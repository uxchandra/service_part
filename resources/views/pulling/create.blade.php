@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Pulling - {{ $order->no_transaksi }}</h1>
        <div class="ml-auto">
            <a href="/orders" class="btn btn-secondary btn-sm">
                <i class="fa fa-arrow-left"></i> <span class="d-none d-md-inline">Kembali</span>
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Desktop: 2 kolom | Mobile: 1 kolom full -->
        <div class="col-lg-6 col-12">
            <!-- Item Order Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="mb-0">Item Order</h4>
                </div>
                <div class="card-body p-2">
                    <!-- Desktop Table -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-sm table-bordered mb-0">
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

                    <!-- Mobile Cards -->
                    <div class="d-block d-md-none" id="mobile-order-items">
                        @foreach($order->orderItems as $it)
                        @php
                            $alreadyScanned = $previousScanned[$it->part_no] ?? 0;
                            $remaining = $it->quantity - $alreadyScanned;
                        @endphp
                        <div class="order-item-card">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="flex-grow-1">
                                    <div class="font-weight-bold text-dark" style="font-size: 14px;">{{ $it->part_no }}</div>
                                </div>
                                <span class="badge badge-primary ml-2">Qty: {{ $it->quantity }}</span>
                            </div>
                            @if($order->status === 'partial')
                            <div class="d-flex gap-2" style="font-size: 13px;">
                                <div class="flex-fill">
                                    <small class="text-muted d-block">Pulling</small>
                                    <span class="badge badge-info">{{ $alreadyScanned }}</span>
                                </div>
                                <div class="flex-fill">
                                    <small class="text-muted d-block">Sisa</small>
                                    <span class="badge {{ $remaining > 0 ? 'badge-warning' : 'badge-success' }}">{{ $remaining }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Scan Form Card -->
            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="mb-0">Scan Items</h4>
                </div>
                <div class="card-body">
                    <form id="form_scan">
                        @csrf
                        <input type="hidden" name="order_id" id="order_id" value="{{ $order->id }}">
                        <div class="form-group mb-3">
                            <label>QR / Part No</label>
                            <input type="text" id="scan_code" class="form-control" placeholder="Scan atau ketik kode..." autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="fa fa-qrcode"></i> Scan
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Daftar Scan -->
        <div class="col-lg-6 col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Daftar Scan</h4>
                    <button type="button" class="btn btn-success btn-sm" id="btn_submit" disabled>
                        <i class="fa fa-check"></i> Submit
                    </button>
                </div>
                <div class="card-body p-2">
                    <!-- Desktop Table -->
                    <div class="table-responsive d-none d-md-block">
                        <table class="table table-sm table-bordered mb-0" id="table_scanned">
                            <thead>
                                <tr>
                                    <th>Part No</th>
                                    <th>Part Name</th>
                                    <th>Stok</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="scanned_items_tbody"></tbody>
                        </table>
                    </div>

                    <!-- Mobile Scanned Cards -->
                    <div class="d-block d-md-none">
                        <div class="container-fluid p-0">
                            <div class="row" id="mobile-scanned-container">
                                <!-- Items will be added here -->
                            </div>
                        </div>
                        <div id="empty-scanned" class="text-center text-muted py-4">
                            <i class="fa fa-inbox fa-2x mb-2"></i>
                            <p>Belum ada item yang discan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Template untuk Mobile Scanned Item Card -->
    <template id="mobile-scanned-template">
        <div class="col-12 mb-2 scanned-item-card-wrapper" data-item-id="{item_id}">
            <div class="card border-left-success shadow-sm">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="flex-grow-1">
                            <div class="font-weight-bold text-dark" style="font-size: 14px;">{part_no}</div>
                            <small class="text-muted d-block">{part_name}</small>
                            <small class="text-muted">Stok: <span class="badge badge-secondary">{stok}</span></small>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm btn_delete_item" data-item-id="{item_id}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                    <div class="form-group mb-0">
                        <label class="text-dark small mb-1">Jumlah:</label>
                        <input type="number" class="form-control form-control-sm qty_input" 
                               value="{quantity}" min="1"
                               data-item-id="{item_id}"
                               style="max-width: 100px; text-align: center; font-weight: bold;">
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@push('styles')
<style>
.border-left-success {
    border-left: 4px solid #28a745 !important;
}

@media (max-width: 767px) {
    body { font-size: 14px; }
    .section-header { padding: 10px 0; margin-bottom: 10px; }
    .section-header h1 { font-size: 16px; margin-bottom: 0; }
    .card { margin-bottom: 10px !important; }
    .card-header h4 { font-size: 15px; }
    .card-body { padding: 10px !important; }
    
    /* Order Item Cards */
    .order-item-card {
        background: white;
        border: 1px solid #e3e6f0;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 8px;
    }
    
    /* Scanned Item Cards - menggunakan template */
    .scanned-item-card-wrapper .card {
        margin-bottom: 0;
    }
    
    .scanned-item-card-wrapper .card-body {
        padding: 10px !important;
    }
    
    .form-control {
        font-size: 16px; /* Prevent zoom on iOS */
    }
    
    .btn-block {
        font-size: 16px;
        padding: 10px;
    }
}

@media (max-width: 480px) {
    .scanned-item-card-wrapper .card-body {
        padding: 8px !important;
    }
    
    .scanned-item-card-wrapper h6,
    .scanned-item-card-wrapper .font-weight-bold {
        font-size: 13px !important;
    }
    
    .scanned-item-card-wrapper small {
        font-size: 11px;
    }
}

/* Highlight animation */
.highlight-success {
    animation: highlightFade 1s ease-in-out;
}

@keyframes highlightFade {
    0% { background-color: #d4edda; }
    100% { background-color: transparent; }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.scanned-item-card-wrapper {
    animation: slideIn 0.3s ease;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function(){
    const isMobile = $(window).width() < 768;

    // Scan form
    $('#form_scan').on('submit', function(e){
        e.preventDefault();
        const orderId = $('#order_id').val();
        const code = $('#scan_code').val().trim();
        if (!code) return;

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
                    if (isMobile) {
                        updateMobileScannedItem(resp.item);
                    } else {
                        updateDesktopScannedItem(resp.item);
                    }
                    
                    $('#scan_code').val('').focus();
                    checkSubmitButton();
                    
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

    // Update Desktop Table
    function updateDesktopScannedItem(item) {
        if (item.is_update) {
            const row = $(`#scanned_items_tbody tr[data-item-id="${item.id}"]`);
            row.find('.qty_input').val(item.quantity);
            row.addClass('table-success');
            setTimeout(() => row.removeClass('table-success'), 500);
        } else {
            $('#scanned_items_tbody').append(`
                <tr data-item-id="${item.id}">
                    <td>${item.part_no}</td>
                    <td>${item.part_name}</td>
                    <td>${item.stok}</td>
                    <td class="text-center">
                        <input type="number" class="form-control form-control-sm qty_input" 
                               value="${item.quantity}" min="1" 
                               data-item-id="${item.id}" style="width: 80px;">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn_delete_item" 
                                data-item-id="${item.id}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        }
    }

    // Update Mobile Cards menggunakan template
    function updateMobileScannedItem(item) {
        $('#empty-scanned').hide();
        
        if (item.is_update) {
            const card = $(`#mobile-scanned-container .scanned-item-card-wrapper[data-item-id="${item.id}"]`);
            card.find('.qty_input').val(item.quantity);
            card.find('.card').addClass('highlight-success');
            setTimeout(() => card.find('.card').removeClass('highlight-success'), 1000);
        } else {
            // Ambil template dan ganti placeholder
            let template = $('#mobile-scanned-template').html();
            template = template.replace(/{item_id}/g, item.id);
            template = template.replace(/{part_no}/g, item.part_no);
            template = template.replace(/{part_name}/g, item.part_name);
            template = template.replace(/{stok}/g, item.stok);
            template = template.replace(/{quantity}/g, item.quantity);
            
            // Tambahkan card baru di awal
            $('#mobile-scanned-container').prepend(template);
        }
    }

    // Delete item
    $(document).on('click', '.btn_delete_item', function(){
        const itemId = $(this).data('item-id');
        const container = $(this).closest(isMobile ? '.scanned-item-card-wrapper' : 'tr');
        
        Swal.fire({
            title: 'Hapus Item?',
            text: 'Item akan dihapus dari daftar scan',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/pulling/delete-item/${itemId}`,
                    type: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function(resp){
                        if (resp.success) {
                            container.fadeOut(300, function() {
                                $(this).remove();
                                checkSubmitButton();
                                
                                if (isMobile && $('#mobile-scanned-container .scanned-item-card-wrapper').length === 0) {
                                    $('#empty-scanned').show();
                                }
                            });
                            
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

    // Update quantity on change (untuk validasi)
    $(document).on('input', '.qty_input', function() {
        let val = parseInt($(this).val());
        if (val < 1 || isNaN(val)) {
            $(this).val(1);
        }
    });

    // Submit pulling
    $('#btn_submit').on('click', function(){
        const orderId = $('#order_id').val();
        const items = [];
        
        if (isMobile) {
            $('#mobile-scanned-container .scanned-item-card-wrapper').each(function(){
                const itemId = $(this).data('item-id');
                const qty = $(this).find('.qty_input').val();
                if (itemId && qty > 0) {
                    items.push({ id: itemId, quantity: parseInt(qty) });
                }
            });
        } else {
            $('#scanned_items_tbody tr').each(function(){
                const itemId = $(this).data('item-id');
                const qty = $(this).find('.qty_input').val();
                if (itemId && qty > 0) {
                    items.push({ id: itemId, quantity: parseInt(qty) });
                }
            });
        }

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
            confirmButtonText: 'Ya, Submit',
            cancelButtonText: 'Batal'
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

    // Enable/disable submit button
    function checkSubmitButton() {
        const hasItems = isMobile 
            ? $('#mobile-scanned-container .scanned-item-card-wrapper').length > 0
            : $('#scanned_items_tbody tr').length > 0;
        $('#btn_submit').prop('disabled', !hasItems);
    }

    checkSubmitButton();
});
</script>
@endpush