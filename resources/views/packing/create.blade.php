@extends('layouts.app')

@include('packing.detail')

@section('content')
    <div class="section-header">
        <h1>ISP Packing - {{ $order->no_transaksi }}</h1>
        <div class="ml-auto">
            <a href="/orders" class="btn btn-dark">Kembali</a>
        </div>
    </div>

    @foreach($transactionsData as $index => $transaction)
    <div class="card mb-4">
        <div class="card-header" style="color: #000;">
                <h5 class="mb-0">
                    Pulling #{{ $transactionsData->count() - $index }} 
                    <small class="text-muted">
                        ( {{ $transaction['tanggal'] ? $transaction['tanggal']->format('d/m/Y H:i') : 'Draft' }})
                    </small>
                </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead>
                        <tr>
                            <th>Part No</th>
                            <th>Part Name</th>
                            <th class="text-center">Qty Order</th>
                            <th class="text-center">Qty Pulling</th>
                            <th class="text-center">Scan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($transaction['items'] as $transactionItem)
                        @php
                            $ispPackingItem = $ispPackingItems->where('barang_id', $transactionItem['barang_id'])->first();
                            $qtyOrder = $transactionItem['qty_order'];
                            $qtyPulling = $transactionItem['qty_pulling'];
                            $qtyIsp = $ispPackingItem ? $ispPackingItem->qty_isp : 0;
                        @endphp
                        <tr>
                            <td>{{ $transactionItem['barang']->part_no }}</td>
                            <td>{{ $transactionItem['barang']->part_name }}</td>
                            <td class="text-center">{{ $qtyOrder }}</td>
                            <td class="text-center">{{ $qtyPulling }}</td>
                            <td class="text-center">
                                @if($ispPackingItem)
                                    @if($qtyIsp >= $qtyPulling)
                                        <span class="badge badge-success">Close</span>
                                    @else
                                        <button type="button" class="btn btn-sm btn-primary btn-detail" 
                                                data-isp-packing-item-id="{{ $ispPackingItem->id }}"
                                                data-transaction-index="{{ $index }}">
                                            <i class="fa fa-qrcode"></i> Scan ({{ $qtyIsp }}/{{ $qtyPulling }})
                                        </button>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endforeach
@endsection


@push('scripts')
<script>
$(document).ready(function(){
    let currentIspPackingItemId = null;
    let canScan = false;

    // Handle detail button click
    $(document).on('click', '.btn-detail', function(){
        currentIspPackingItemId = $(this).data('isp-packing-item-id');
        const transactionIndex = $(this).data('transaction-index');
        
        // Show loading
        $('#keypoint-container').html('<div class="text-center py-4"><div class="spinner-border" role="status"></div></div>');
        $('#modal_part_no').text('Loading...');
        
        // Show modal
        $('#modalDetailItem').modal('show');
        
        // Load item detail
        $.ajax({
            url: '/packing/get-item-detail',
            type: 'GET',
            data: { 
                isp_packing_item_id: currentIspPackingItemId,
                transaction_index: transactionIndex
            },
            success: function(resp){
                if (resp.success) {
                    const data = resp.data;
                    
                    // Update modal content
                    $('#modal_part_no').text(data.part_no);
                    $('#detail_part_no').text(data.part_no);
                    $('#detail_part_name').text(data.part_name);
                    $('#detail_size_plastik').text(data.size_plastik);
                    $('#detail_part_color').text(data.part_color);
                    $('#detail_qty_order').text(data.qty_order);
                    $('#detail_qty_pulling').text(data.qty_pulling);
                    $('#detail_qty_isp').text(data.qty_isp);
                    
                    // Show transaction detail if available
                    if (data.transaction_details && Object.keys(data.transaction_details).length > 0) {
                        $('#transaction_detail_row').show();
                        $('#detail_qty_pulling_transaction').text(data.transaction_details.qty_pulling_transaction);
                    } else {
                        $('#transaction_detail_row').hide();
                    }
                    
                    // Update keypoint image
                    if (data.keypoint) {
                        $('#keypoint-container').html(`
                            <img src="/images/${data.keypoint}" 
                                 class="img-fluid rounded" 
                                 style="max-height: 500px; width: 100%; object-fit: contain;"
                                 alt="Keypoint">
                        `);
                    } else {
                        $('#keypoint-container').html(`
                            <div class="text-center py-4 text-muted">
                                <i class="fa fa-image fa-3x mb-3"></i>
                                <p>No keypoint image</p>
                            </div>
                        `);
                    }
                    
                    // Update scan input and submit button
                    canScan = data.can_scan;
                    $('#scan_input').prop('disabled', !canScan);
                    $('#btn_submit').prop('disabled', data.qty_isp === 0);
                    
                    // Focus on scan input if can scan
                    if (canScan) {
                        $('#scan_input').focus();
                    }
                    
                } else {
                    $('#keypoint-container').html('<div class="text-center py-4 text-danger">Gagal memuat data</div>');
                }
            },
            error: function(){
                $('#keypoint-container').html('<div class="text-center py-4 text-danger">Terjadi kesalahan</div>');
            }
        });
    });

    // Handle scan input
    $('#scan_input').on('keypress', function(e){
        if (e.which === 13) { // Enter key
            e.preventDefault();
            performScan();
        }
    });

    function updateTableRow(ispPackingItemId, qtyIsp) {
        // Find the row with the matching data-isp-packing-item-id
        const button = $(`button[data-isp-packing-item-id="${ispPackingItemId}"]`);
        const row = button.closest('tr');
        const transactionIndex = button.data('transaction-index');
        
        // Get qty pulling from the row
        const qtyPulling = parseInt(row.find('td:eq(3)').text());
        
        // Update scan column
        if (qtyIsp >= qtyPulling) {
            row.find('td:eq(4)').html('<span class="badge badge-success">Close</span>');
        } else {
            row.find('td:eq(4)').html(`
                <button type="button" class="btn btn-sm btn-primary btn-detail" 
                        data-isp-packing-item-id="${ispPackingItemId}"
                        data-transaction-index="${transactionIndex}">
                    <i class="fa fa-qrcode"></i> Scan (${qtyIsp}/${qtyPulling})
                </button>
            `);
        }
    }

    function performScan() {
        if (!currentIspPackingItemId || !canScan) return;
        
        const scanValue = $('#scan_input').val().trim();
        if (!scanValue) return;
        
        // Ambil transaction_index dari button yang diklik
        const transactionIndex = $(`button[data-isp-packing-item-id="${currentIspPackingItemId}"]`).data('transaction-index');
        
        $.ajax({
            url: '/packing/scan',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                isp_packing_item_id: currentIspPackingItemId,
                scan_value: scanValue,
                transaction_index: transactionIndex
            },
            success: function(resp){
                if (resp.success) {
                    // Update qty isp display
                    $('#detail_qty_isp').text(resp.qty_isp);
                    
                    // Update input and button states
                    canScan = resp.can_scan;
                    $('#scan_input').prop('disabled', !canScan);
                    $('#btn_submit').prop('disabled', resp.qty_isp === 0);
                    
                    // Clear input and focus again if can scan
                    $('#scan_input').val('');
                    if (canScan) {
                        $('#scan_input').focus();
                    }
                    
                    // Show success message
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: resp.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                    
                    // Update table row without reload
                    updateTableRow(currentIspPackingItemId, resp.qty_isp);
                    
                } else {
                    Swal.fire('Gagal', resp.message, 'error');
                    // Clear input on error
                    $('#scan_input').val('');
                    if (canScan) {
                        $('#scan_input').focus();
                    }
                }
            },
            error: function(xhr){
                Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                // Clear input on error
                $('#scan_input').val('');
                if (canScan) {
                    $('#scan_input').focus();
                }
            }
        });
    }

    // Handle submit button
    $('#btn_submit').on('click', function(){
        if (!currentIspPackingItemId) return;
        
        Swal.fire({
            title: 'Submit ISP Packing?',
            text: 'Pastikan semua item sudah di-scan',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Submit'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/packing/submit',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        isp_packing_id: {{ $ispPacking->id }}
                    },
                    success: function(resp){
                        if (resp.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: resp.message,
                                icon: 'success'
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Gagal', resp.message, 'error');
                        }
                    },
                    error: function(xhr){
                        Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
                    }
                });
            }
        });
    });
});
</script>
@endpush
