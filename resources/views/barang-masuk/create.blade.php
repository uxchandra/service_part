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
                        
                        <div id="items_container">
                            <!-- Empty State -->
                            <div class="text-center py-4" id="empty_state">
                                <i class="fa fa-inbox fa-3x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Belum ada barang<br><small>Scan QR Label untuk menambah</small></p>
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
@endsection

@push('styles')
<style>
@media (max-width: 767px) {
    body { font-size: 14px; }
    .section-header { padding: 10px 0; margin-bottom: 10px; }
    .section-header h1 { font-size: 16px; margin-bottom: 0; }
    .card-body { padding: 10px !important; }
    .card-footer { padding: 10px; }
    
    /* Scan Input */
    #qr_scan {
        font-size: 16px;
        height: 42px;
    }
    
    .input-group-text {
        padding: 0.5rem;
    }
    
    /* Item Card Mobile */
    .barang-card {
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: 6px;
        padding: 10px;
        margin-bottom: 8px;
        animation: slideIn 0.3s ease;
    }
    
    .barang-card-header {
        display: flex;
        justify-content: space-between;
        align-items: start;
        margin-bottom: 8px;
    }
    
    .barang-part-no {
        font-size: 14px;
        font-weight: bold;
        color: #2c3e50;
    }
    
    .barang-stok {
        font-size: 12px;
        color: #6c757d;
    }
    
    .barang-actions {
        display: flex;
        gap: 8px;
        padding-top: 8px;
        border-top: 1px solid #e3e6f0;
    }
    
    .barang-actions input {
        flex: 1;
        text-align: center;
        font-weight: bold;
        font-size: 16px;
        height: 38px;
    }
    
    .barang-actions .btn {
        width: 38px;
        height: 38px;
        padding: 0;
        flex-shrink: 0;
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
}

/* Desktop styles */
.barang-row {
    padding: 12px 15px;
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 6px;
    margin-bottom: 12px;
    transition: all 0.3s ease;
    animation: slideIn 0.3s ease;
}

.barang-row:hover {
    border-color: #4e73df;
    box-shadow: 0 2px 8px rgba(78, 115, 223, 0.1);
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

.qty-input {
    font-size: 0.9rem;
    font-weight: 600;
    text-align: center;
    height: 38px;
}

.stok-display {
    font-size: 0.9rem;
    font-weight: 600;
    text-align: center;
    padding: 8px;
    background: #e7f3ff;
    border: 1px solid #b3d9ff;
    border-radius: 4px;
    color: #004085;
}

.part-no-display {
    font-size: 0.9rem;
    font-weight: 600;
    color: #2d3748;
    padding: 8px;
    background: white;
    border: 1px solid #e3e6f0;
    border-radius: 4px;
    min-height: 38px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.small {
    font-size: 0.8rem;
    font-weight: 600;
}

.remove-barang {
    height: 38px;
}

/* Highlight animation */
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
    const isMobile = $(window).width() < 768;

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
        const selector = isMobile ? '.barang-card' : '.barang-row';
        
        $(selector).each(function() {
            let existingQR = $(this).attr('data-qr-label');
            
            if (existingQR && existingQR.toUpperCase() === qrLabel) {
                foundExisting = true;
                
                // Tambah qty
                let qtyInput = $(this).find('.qty-input');
                let currentQty = parseInt(qtyInput.val()) || 0;
                let newQty = currentQty + 1;
                qtyInput.val(newQty);
                
                // Highlight effect
                $(this).addClass('highlight-success');
                setTimeout(() => $(this).removeClass('highlight-success'), 1000);
                
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
                    addItemRow(response.data, qrLabel);
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

    // Function Add Item Row
    function addItemRow(barang, qrLabel) {
        itemCounter++;
        $('#empty_state').hide();
        
        let qrLabelToStore = qrLabel || barang.qr_label;
        
        if (isMobile) {
            // Mobile Card Layout
            const newCard = `
                <div class="barang-card" id="row_${itemCounter}" data-barang-id="${barang.id}" data-qr-label="${qrLabelToStore}">
                    <div class="barang-card-header">
                        <div>
                            <div class="barang-part-no">${barang.part_no}</div>
                            <div class="barang-stok">Stok: <span class="badge badge-info">${barang.stok_current}</span></div>
                        </div>
                    </div>
                    <div class="barang-actions">
                        <input type="number" class="form-control qty-input" data-row="${itemCounter}" value="1" min="1" required>
                        <button type="button" class="btn btn-danger remove-barang" data-row="${itemCounter}">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                    <input type="hidden" class="barang-id" value="${barang.id}">
                </div>
            `;
            $('#items_container').append(newCard);
        } else {
            // Desktop Row Layout
            const newRow = `
                <div class="row barang-row align-items-center" id="row_${itemCounter}" data-barang-id="${barang.id}" data-qr-label="${qrLabelToStore}">
                    <div class="col-md-4 col-12 mb-md-0 mb-2">
                        <label class="small mb-1 text-dark">Part No:</label>
                        <div class="part-no-display">${barang.part_no}</div>
                        <input type="hidden" class="barang-id" value="${barang.id}">
                    </div>
                    
                    <div class="col-md-3 col-6 mb-md-0 mb-2">
                        <label class="small mb-1 text-dark">Stok Saat Ini:</label>
                        <div class="stok-display">${barang.stok_current}</div>
                    </div>
                    
                    <div class="col-md-3 col-6 mb-md-0 mb-2">
                        <label class="small mb-1 text-dark">Qty Masuk:</label>
                        <input type="number" class="form-control qty-input" data-row="${itemCounter}" value="1" min="1" required>
                    </div>
                    
                    <div class="col-md-2 col-12 d-flex align-items-end">
                        <label class="small mb-1 text-dark d-md-block d-none" style="visibility: hidden;">Aksi</label>
                        <button type="button" class="btn btn-danger btn-sm remove-barang w-100" data-row="${itemCounter}">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            `;
            $('#items_container').append(newRow);
        }
        
        updateSummary();
    }

    // Remove Item Row
    $(document).on('click', '.remove-barang', function() {
        let row = $(this).data('row');
        
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
                $(`#row_${row}`).fadeOut(300, function() {
                    $(this).remove();
                    updateSummary();
                    
                    const selector = isMobile ? '.barang-card' : '.barang-row';
                    if ($(selector).length === 0) {
                        $('#empty_state').show();
                    }
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
        const selector = isMobile ? '.barang-card' : '.barang-row';
        let totalItems = $(selector).length;
        $('#item_count').text(totalItems);
    }

    // Submit Form
    $('#form_barang_masuk').on('submit', function(e) {
        e.preventDefault();
        
        const selector = isMobile ? '.barang-card' : '.barang-row';
        
        if ($(selector).length === 0) {
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

        $(selector).each(function() {
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