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
                            <th class="text-center">Qty ISP</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
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
                            <td class="text-center">{{ $qtyIsp }}</td>
                            @php
                                // Status berdasarkan qty ISP vs qty Order (bukan vs pulling)
                                $isCompleted = $qtyIsp >= $qtyOrder;
                                $status = $isCompleted ? 'Close' : 'Open';
                                $statusClass = $isCompleted ? 'success' : 'warning';
                            @endphp
                            <td class="text-center">
                                <span class="badge badge-{{ $statusClass }}">{{ $status }}</span>
                            </td>
                            <td class="text-center">
                                @if($ispPackingItem)
                                    @if($qtyIsp >= $qtyPulling)
                                        <span class="text-muted">-</span>
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
                            <h6 class="text-muted mb-1" style="font-size: 0.7rem; flex-shrink: 0;"><i class="fa fa-eye mr-1"></i>Keypoint</h6>
                            <div style="flex: 1; display: flex; align-items: center; justify-content: center; min-height: 0; overflow: hidden;">
                                <img src="/images/${data.keypoint}" 
                                    class="img-fluid rounded shadow-sm" 
                                    style="max-height: 100%; max-width: 100%; object-fit: contain;"
                                    alt="Keypoint">
                            </div>
                        `);
                    } else {
                        $('#keypoint-container').html(`
                            <h6 class="text-muted mb-1" style="font-size: 0.7rem; flex-shrink: 0;"><i class="fa fa-eye mr-1"></i>Keypoint</h6>
                            <div style="flex: 1; display: flex; align-items: center; justify-content: center; min-height: 0;" class="text-center text-muted border rounded">
                                <div>
                                    <i class="fa fa-image mb-1" style="font-size: 1.5rem;"></i>
                                    <p class="mb-0" style="font-size: 0.7rem;">No keypoint image</p>
                                </div>
                            </div>
                        `);
                    }

                    // Update warna plastik image
                    if (data.warna_plastik) {
                        $('#warna_plastik-container').html(`
                            <h6 class="text-muted mb-1" style="font-size: 0.7rem; flex-shrink: 0;"><i class="fa fa-palette mr-1"></i>Warna Plastik</h6>
                            <div style="flex: 1; display: flex; align-items: center; justify-content: center; min-height: 0; overflow: hidden;">
                                <img src="/images/${data.warna_plastik}" 
                                    class="img-fluid rounded shadow-sm" 
                                    style="max-height: 100%; max-width: 100%; object-fit: contain;"
                                    alt="Warna Plastik">
                            </div>
                        `);
                    } else {
                        $('#warna_plastik-container').html(`
                            <h6 class="text-muted mb-1" style="font-size: 0.7rem; flex-shrink: 0;"><i class="fa fa-palette mr-1"></i>Warna Plastik</h6>
                            <div style="flex: 1; display: flex; align-items: center; justify-content: center; min-height: 0;" class="text-center text-muted border rounded">
                                <div>
                                    <i class="fa fa-image mb-1" style="font-size: 1.5rem;"></i>
                                    <p class="mb-0" style="font-size: 0.7rem;">No warna plastik image</p>
                                </div>
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
        
        // Get qty order & pulling from the row
        const qtyOrder = parseInt(row.find('td:eq(2)').text());
        const qtyPulling = parseInt(row.find('td:eq(3)').text());

        // Update Qty ISP column (index 4)
        row.find('td:eq(4)').text(qtyIsp);

        // Update Status column (index 5) berdasarkan qty ISP vs qty Order
        const isCompleted = qtyIsp >= qtyOrder;
        const status = isCompleted ? 'Close' : 'Open';
        const statusClass = isCompleted ? 'success' : 'warning';
        row.find('td:eq(5)').html(`<span class="badge badge-${statusClass}">${status}</span>`);

        // Update Aksi column (index 6)
        if (qtyIsp >= qtyPulling) {
            row.find('td:eq(6)').html('<span class="text-muted">-</span>');
        } else {
            row.find('td:eq(6)').html(`
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
                            // Tampilkan informasi completion status
                            let alertTitle = 'Berhasil!';
                            let alertIcon = 'success';
                            let alertText = resp.message;
                            
                            if (resp.completion_status) {
                                alertTitle = 'Order Completed!';
                                alertIcon = 'success';
                                alertText = resp.completion_message;
                            } else {
                                alertTitle = 'ISP Packing Submitted';
                                alertIcon = 'success';
                                alertText = resp.message;
                            }
                            
                            Swal.fire({
                                title: alertTitle,
                                text: alertText,
                                icon: alertIcon,
                                showConfirmButton: false,
                                timer: 1500
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
