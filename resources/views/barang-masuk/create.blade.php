@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Scan Posting</h1>
        <div class="ml-auto">
            <a href="{{ route('barang-masuk.index') }}" class="btn btn-dark btn-sm">
                <i class="fa fa-arrow-left"></i> <span class="d-none d-md-inline">Kembali</span>
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <form id="form_barang_masuk">
                    @csrf
                    <div class="card-body p-2 p-md-3">
                        <!-- Scan QR Section -->
                        <div class="mb-3">
                            <label for="qr_scan" class="form-label">Scan QR Label</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white">
                                        <i class="fa fa-qrcode text-primary"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" id="qr_scan" placeholder="Scan atau ketik QR Label..." autofocus autocomplete="off">
                                <div class="input-group-append d-none d-md-flex">
                                    <button class="btn btn-primary" type="button" id="btn_scan">
                                        <i class="fa fa-search"></i> Cari
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">
                                <i class="fa fa-info-circle"></i> Tekan Enter setelah scan
                            </small>
                        </div>

                        <hr>

                        <!-- Items List Section -->
                        <h6 class="mb-2">Daftar Barang <span class="badge badge-primary" id="item_count">0</span></h6>
                        
                        <div class="container-fluid p-0">
                            <div class="row" id="items_container">
                                <!-- Items will be added here dynamically -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button type="button" class="btn btn-secondary" onclick="window.location='{{ route('barang-masuk.index') }}'">
                            <i class="fa fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btn_simpan">
                            <i class="fa fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Template untuk card item -->
    <template id="item-card-template">
        <div class="col-12 mb-2 item-card" id="cardItem{id}" data-qr-label="{qr_label}">
            <div class="card border-left-primary shadow-sm">
                <div class="card-body p-2">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="font-weight-bold text-primary mb-1">{part_no}</h6>
                            <small class="text-muted">Stok: <span class="badge badge-info">{stok}</span></small>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm delete_card">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                    <div class="form-group mb-0">
                        <label class="text-dark small mb-1">Jumlah:</label>
                        <div class="d-flex align-items-center" style="gap: 8px;">
                            <input type="number" class="form-control form-control-sm qty-input" style="width: 80px;" value="1" min="1" required>
                            <input type="hidden" class="barang-id" value="{barang_id}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@push('styles')
<style>
/* Base Styles */
.border-left-primary {
    border-left: 4px solid #4e73df !important;
}

/* Mobile First - Base untuk semua mobile */
@media (max-width: 767px) {
    body { font-size: 14px; }
    .section-header { padding: 10px 0; margin-bottom: 10px; }
    .section-header h1 { font-size: 16px; margin-bottom: 0; }
    .card-body { padding: 10px !important; }
    .card-footer { padding: 10px; }
    
    #qr_scan {
        font-size: 16px;
        height: 42px;
    }
    
    .input-group-text {
        padding: 0.5rem;
    }
    
    .form-label {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 5px;
    }
    
    .card-footer .btn {
        font-size: 14px;
        padding: 8px 16px;
    }
    
    /* Item Card */
    .item-card .card {
        margin-bottom: 0;
    }
    
    .item-card .card-body {
        padding: 10px !important;
    }
    
    .item-card h6 {
        font-size: 14px;
        margin-bottom: 3px;
    }
    
    .item-card .qty-input {
        width: 70px !important;
        text-align: center;
        font-weight: bold;
        font-size: 16px;
        height: 38px;
    }
    
    .item-card .btn-danger {
        padding: 6px 10px !important;
        font-size: 14px;
    }
}

/* Scanner Khusus 480px */
@media (max-width: 480px) {
    body { font-size: 13px; }
    .section-header { padding: 8px 0; margin-bottom: 8px; }
    .section-header h1 { font-size: 15px; }
    .card-body { padding: 8px !important; }
    .card-footer { padding: 8px; }
    
    .item-card .card-body {
        padding: 8px !important;
    }
    
    .item-card h6 {
        font-size: 13px;
    }
    
    .item-card .qty-input {
        width: 60px !important;
        font-size: 14px;
        height: 34px;
    }
    
    .item-card .btn-danger {
        padding: 5px 8px !important;
        font-size: 13px;
    }
    
    .item-card small {
        font-size: 11px;
    }
}

/* Desktop */
@media (min-width: 768px) {
    .item-card .card-body {
        padding: 15px !important;
    }
    
    .item-card .qty-input {
        width: 100px !important;
    }
}

/* Animations */
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

.item-card {
    animation: slideIn 0.3s ease;
}

.highlight-success {
    animation: highlightFade 1s ease-in-out;
}

@keyframes highlightFade {
    0% { background-color: #d4edda; }
    100% { background-color: transparent; }
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    let itemCounter = 0;

    // Scan QR on Enter
    $('#qr_scan').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            scanBarang();
        }
    });

    // Clear input on focus
    $('#qr_scan').on('focus', function() {
        if ($(this).val().trim()) {
            $(this).val('');
        }
    });

    // Clear input saat user mulai mengetik
    $('#qr_scan').on('input', function() {
        if ($(this).val().length === 1 && $(this).data('last-length') > 1) {
            $(this).val('');
        }
        $(this).data('last-length', $(this).val().length);
    });

    // Scan QR on Button Click
    $('#btn_scan').on('click', function() {
        scanBarang();
    });

    // Function Scan Barang
    function scanBarang() {
        let qrLabel = $('#qr_scan').val().trim().toUpperCase();
        
        if (!qrLabel) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Silakan scan atau input QR Label',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        // CEK APAKAH BARANG SUDAH ADA DI LIST
        let foundExisting = false;
        
        $('.item-card').each(function() {
            let existingQR = $(this).attr('data-qr-label');
            
            if (existingQR && existingQR.toUpperCase() === qrLabel) {
                foundExisting = true;
                
                // Tambah qty
                let qtyInput = $(this).find('.qty-input');
                let currentQty = parseInt(qtyInput.val()) || 0;
                let newQty = currentQty + 1;
                qtyInput.val(newQty);
                
                // Highlight effect
                $(this).find('.card').addClass('highlight-success');
                setTimeout(() => $(this).find('.card').removeClass('highlight-success'), 1000);
                
                // Reset dan focus
                $('#qr_scan').val('');
                setTimeout(() => $('#qr_scan').focus(), 50);
                
                updateSummary();
                return false;
            }
        });

        if (foundExisting) return;

        // JIKA BELUM ADA, FETCH DARI SERVER
        $.ajax({
            url: '{{ route("barang-masuk.scan") }}',
            type: 'POST',
            data: {
                qr_label: qrLabel,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.status === 'success') {
                    addItemCard(response.data, qrLabel);
                    $('#qr_scan').val('');
                    setTimeout(() => $('#qr_scan').focus(), 50);
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Barang tidak ditemukan',
                    confirmButtonText: 'OK'
                });
                $('#qr_scan').val('');
                setTimeout(() => $('#qr_scan').focus(), 50);
            }
        });
    }

    // Function Add Item Card
    function addItemCard(barang, qrLabel) {
        let qrLabelToStore = qrLabel || barang.qr_label;
        
        // Ambil template dan ganti placeholder
        let template = $('#item-card-template').html();
        template = template.replace(/{id}/g, itemCounter);
        template = template.replace(/{part_no}/g, barang.part_no);
        template = template.replace(/{stok}/g, barang.stok_current);
        template = template.replace(/{barang_id}/g, barang.id);
        template = template.replace(/{qr_label}/g, qrLabelToStore);
        
        // Tambahkan card baru di awal
        $('#items_container').prepend(template);
        
        itemCounter++;
        updateSummary();
    }

    // Delete card handler
    $(document).on('click', '.delete_card', function(e) {
        e.preventDefault();
        
        Swal.fire({
            title: 'Hapus Item?',
            text: "Hapus barang dari list?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).closest('.item-card').fadeOut(300, function() {
                    $(this).remove();
                    updateSummary();
                });
            }
        });
    });

    // Update quantity on change
    $(document).on('input', '.qty-input', function() {
        let val = parseInt($(this).val());
        if (val < 1 || isNaN(val)) {
            $(this).val(1);
        }
        updateSummary();
    });

    // Function Update Summary
    function updateSummary() {
        let totalItems = $('.item-card').length;
        $('#item_count').text(totalItems);
    }

    // Submit Form
    $('#form_barang_masuk').on('submit', function(e) {
        e.preventDefault();
        
        if ($('.item-card').length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Minimal harus ada 1 barang',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Collect items data
        let items = [];
        let hasInvalidQty = false;

        $('.item-card').each(function() {
            let barangId = $(this).find('.barang-id').val();
            let quantity = $(this).find('.qty-input').val();
            
            if (!quantity || quantity < 1) {
                hasInvalidQty = true;
                return false;
            }
            
            if (barangId && quantity) {
                items.push({
                    barang_id: barangId,
                    quantity: parseInt(quantity)
                });
            }
        });

        if (hasInvalidQty) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Quantity harus minimal 1',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (items.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Tidak ada data barang yang valid',
                confirmButtonText: 'OK'
            });
            return;
        }

        // Confirmation
        Swal.fire({
            title: 'Konfirmasi',
            text: `Simpan ${items.length} item barang masuk?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                submitForm(items);
            }
        });
    });

    // Function Submit Form
    function submitForm(items) {
        let formData = {
            items: items,
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        Swal.fire({
            title: 'Menyimpan...',
            text: 'Sedang menyimpan transaksi...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
        });

        $.ajax({
            url: '{{ route("barang-masuk.store") }}',
            type: 'POST',
            data: formData,
            success: function(response) {
                Swal.close();
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        showConfirmButton: true,
                    }).then(() => {
                        window.location.href = '{{ route("barang-masuk.index") }}';
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                let errorMessage = 'Terjadi kesalahan saat menyimpan data';
                
                if (xhr.status === 422 && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    let errorList = '<ul class="text-left mb-0">';
                    $.each(errors, function(key, value) {
                        if (Array.isArray(value)) {
                            value.forEach(e => errorList += '<li>' + e + '</li>');
                        } else {
                            errorList += '<li>' + value + '</li>';
                        }
                    });
                    errorList += '</ul>';
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal!',
                        html: errorList
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: xhr.responseJSON?.message || errorMessage
                    });
                }
            }
        });
    }
});
</script>
@endpush