@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Pulling - {{ $order->no_transaksi }}</h1>
        <div class="ml-auto">
            <a href="/orders" class="btn btn-secondary">Kembali</a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h4>Item Order</h4></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th>Part No</th>
                                    <th class="text-center">Qty Order</th>
                                    @if($order->status === 'partial')
                                    <th class="text-center">Pulling</th>
                                    <th class="text-center">Sisa</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->orderItems as $it)
                                <tr>
                                    <td>{{ $it->part_no }}</td>
                                    <td class="text-center">{{ $it->quantity }}</td>
                                    @if($order->status === 'partial')
                                    <td class="text-center">
                                        <span class="badge badge-info">{{ $previousScanned[$it->part_no] ?? 0 }}</span>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $alreadyScanned = $previousScanned[$it->part_no] ?? 0;
                                            $remaining = $it->quantity - $alreadyScanned;
                                        @endphp
                                        <span class="badge {{ $remaining > 0 ? 'badge-warning' : 'badge-success' }}">
                                            {{ $remaining }}
                                        </span>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4>Scan Items</h4></div>
                <div class="card-body">
                    <form id="form_scan">
                        @csrf
                        <input type="hidden" name="order_id" id="order_id" value="{{ $order->id }}">
                        <div class="form-group">
                            <label>QR / Part No</label>
                            <input type="text" id="scan_code" class="form-control" placeholder="Scan atau ketik kode..." autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fa fa-qrcode"></i> Scan</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Daftar Scan</h4>
                    <button type="button" class="btn btn-success" id="btn_submit" disabled>
                        <i class="fa fa-check"></i> Submit
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered" id="table_scanned">
                            <thead>
                                <tr>
                                    <th>Part No</th>
                                    <th>Part Name</th>
                                    <th>Stok</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="scanned_items_tbody">
                                {{-- Kosong karena setiap kali masuk halaman ini akan reset --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function(){
    // Scan form
    $('#form_scan').on('submit', function(e){
        e.preventDefault();
        const orderId = $('#order_id').val();
        const code = $('#scan_code').val().trim();
        if (!code) {
            return;
        }

        $.ajax({
            url: '{{ route("pulling.scan") }}',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                order_id: orderId,
                code: code,
            },
            success: function(resp){
                if (resp.success) {
                    // PERBAIKAN: Cek apakah item sudah ada di tabel (update) atau baru (insert)
                    if (resp.item.is_update) {
                        // Update qty di row yang sudah ada
                        const row = $(`tr[data-item-id="${resp.item.id}"]`);
                        row.find('.qty_input').val(resp.item.quantity);
                        
                        // Highlight row sebentar
                        row.addClass('table-success');
                        setTimeout(() => row.removeClass('table-success'), 500);
                    } else {
                        // Tambahkan row baru ke tabel scanned
                        const tbody = $('#scanned_items_tbody');
                        tbody.append(`
                            <tr data-item-id="${resp.item.id}">
                                <td>${resp.item.part_no}</td>
                                <td>${resp.item.part_name}</td>
                                <td>${resp.item.stok}</td>
                                <td class="text-center">
                                    <input type="number" class="form-control form-control-sm qty_input" 
                                           value="${resp.item.quantity}" min="1" 
                                           data-item-id="${resp.item.id}" style="width: 80px;">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-danger btn_delete_item" 
                                            data-item-id="${resp.item.id}">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `);
                    }
                    
                    $('#scan_code').val('').focus();
                    checkSubmitButton();
                    
                    // Notifikasi sukses
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: resp.message,
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire('Gagal', resp.message || 'Scan gagal', 'error');
                }
            },
            error: function(xhr){
                Swal.fire('Error', xhr.responseJSON?.message || 'Terjadi kesalahan', 'error');
            }
        });
    });

    // Delete item
    $(document).on('click', '.btn_delete_item', function(){
        const itemId = $(this).data('item-id');
        const row = $(this).closest('tr');
        
        Swal.fire({
            title: 'Hapus Item?',
            text: 'Item akan dihapus dari daftar scan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/pulling/delete-item/${itemId}`,
                    type: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function(resp){
                        if (resp.success) {
                            row.remove();
                            checkSubmitButton();
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Item berhasil dihapus',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        } else {
                            Swal.fire('Gagal', resp.message, 'error');
                        }
                    },
                    error: function(){
                        Swal.fire('Error', 'Terjadi kesalahan', 'error');
                    }
                });
            }
        });
    });

    // Submit pulling
    $('#btn_submit').on('click', function(){
        const orderId = $('#order_id').val();
        const items = [];
        
        $('#scanned_items_tbody tr').each(function(){
            const itemId = $(this).data('item-id');
            const qty = $(this).find('.qty_input').val();
            if (itemId && qty > 0) {
                items.push({ id: itemId, quantity: parseInt(qty) });
            }
        });

        if (items.length === 0) {
            Swal.fire('Error', 'Tidak ada item untuk disubmit', 'error');
            return;
        }

        Swal.fire({
            title: 'Submit Pulling?',
            text: 'Pastikan qty sudah sesuai dengan order',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Submit'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("pulling.submit") }}',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        order_id: orderId,
                        items: items,
                    },
                    success: function(resp){
                        if (resp.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: resp.message,
                                icon: 'success'
                            }).then(() => {
                                window.location.href = '/orders';
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

    // Enable/disable submit button based on items
    function checkSubmitButton() {
        const hasItems = $('#scanned_items_tbody tr').length > 0;
        $('#btn_submit').prop('disabled', !hasItems);
    }

    // Initial check
    checkSubmitButton();
});
</script>
@endpush