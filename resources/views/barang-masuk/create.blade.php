@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Scan Posting</h1>
        <div class="ml-auto">
            <a href="{{ route('barang-masuk.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <form id="form_barang_masuk">
                    @csrf
                    <div class="card-body">
                        <!-- Scan QR Section -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label for="qr_scan" class="small mb-1 text-dark">Scan QR Label</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white">
                                                <i class="fa fa-qrcode text-primary"></i>
                                            </span>
                                        </div>
                                        <input type="text" class="form-control" id="qr_scan" placeholder="Scan atau ketik QR Label disini..." autofocus autocomplete="off">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary btn-sm px-3" type="button" id="btn_scan">
                                                <i class="fa fa-search"></i> Cari
                                            </button>
                                        </div>
                                    </div>
                                    <small class="text-muted">
                                        <i class="fa fa-info-circle"></i> Tekan Enter atau klik tombol Cari setelah scan
                                    </small>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Items List Section -->
                        <h6 class="mb-3">Daftar Barang</h6>
                        
                        <div id="items_container">
                            <!-- Empty State -->
                            <div class="alert alert-light text-center border" id="empty_state">
                                <i class="fa fa-inbox fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">Belum ada barang</h6>
                                <p class="text-muted mb-0">Silakan scan QR Label untuk menambahkan barang</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer text-right">
                        <button type="button" class="btn btn-secondary" onclick="window.location='{{ route('barang-masuk.index') }}'">
                            <i class="fa fa-times"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btn_simpan">
                            <i class="fa fa-save"></i> Simpan Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
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

    #qr_scan {
        font-size: 0.95rem;
        font-weight: 500;
        text-transform: uppercase;
        height: 38px;
    }

    .small {
        font-size: 0.8rem;
        font-weight: 600;
    }

    .remove-barang {
        height: 38px;
        padding: 0;
    }

    @media (max-width: 768px) {
        .barang-row {
            padding: 10px;
        }
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

    .qr-label-display {
        font-size: 0.75rem;
        color: #718096;
        margin-top: 2px;
    }

    #btn_scan {
        font-size: 0.875rem;
        height: 38px;
    }

    .input-group-text {
        font-size: 0.9rem;
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

    // Clear input on focus untuk memastikan selalu bersih
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
        let qrLabel = $('#qr_scan').val().trim().toUpperCase(); // Normalize dengan uppercase
        
        if (!qrLabel) {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Silakan scan atau input QR Label terlebih dahulu',
                timer: 2000,
                showConfirmButton: false
            });
            return;
        }

        console.log('Scanning QR:', qrLabel); // Debug log

        // CEK APAKAH BARANG SUDAH ADA DI LIST
        let foundExisting = false;
        $('.barang-row').each(function() {
            let existingQR = $(this).attr('data-qr-label');
            console.log('Comparing with existing QR:', existingQR); // Debug log
            
            if (existingQR && existingQR.toUpperCase() === qrLabel) {
                foundExisting = true;
                
                // Tambah qty
                let qtyInput = $(this).find('.qty-input');
                let currentQty = parseInt(qtyInput.val()) || 0;
                let newQty = currentQty + 1;
                qtyInput.val(newQty);
                
                console.log('Found existing! Old qty:', currentQty, 'New qty:', newQty); // Debug log
                
                // Reset dan focus
                $('#qr_scan').val('');
                setTimeout(() => {
                    $('#qr_scan').focus();
                }, 50);
                
                
                
                // Update summary
                updateSummary();
                
                // Show success message
                // Swal.fire({
                //     icon: 'success',
                //     title: 'Qty Ditambah!',
                //     text: `Qty ditambah menjadi ${newQty}`,
                //     timer: 1500,
                //     showConfirmButton: false
                // });
                
                return false; // Break the each loop
            }
        });

        // Jika sudah ketemu, jangan lanjut ke AJAX
        if (foundExisting) {
            return;
        }

        console.log('Not found in list, fetching from server...'); // Debug log

        // JIKA BELUM ADA, FETCH DARI SERVER
        $.ajax({
            url: '{{ route("barang-masuk.scan") }}',
            type: 'POST',
            data: {
                qr_label: qrLabel,
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Server response:', response); // Debug log
                
                if (response.status === 'success') {
                    addItemRow(response.data, qrLabel);
                    
                    // Reset dan focus
                    $('#qr_scan').val('');
                    setTimeout(() => {
                        $('#qr_scan').focus();
                    }, 50);
                }
            },
            error: function(xhr) {
                console.error('AJAX error:', xhr); // Debug log
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Barang tidak ditemukan',
                    confirmButtonText: 'OK'
                });
                
                // Reset dan focus setelah error
                $('#qr_scan').val('');
                setTimeout(() => {
                    $('#qr_scan').focus();
                }, 50);
            }
        });
    }

    // Function Add Item Row
    function addItemRow(barang, qrLabel) {
        itemCounter++;
        
        // Hide empty state
        $('#empty_state').hide();
        
        // Gunakan QR label yang sudah dinormalisasi
        let qrLabelToStore = qrLabel || barang.qr_label;
        
        console.log('Adding new row with QR:', qrLabelToStore); // Debug log
        
        const newRow = `
            <div class="row barang-row align-items-center" id="row_${itemCounter}" data-barang-id="${barang.id}" data-qr-label="${qrLabelToStore}">
                <div class="col-md-4 col-12 mb-md-0 mb-2">
                    <label class="small mb-1 text-dark">Part No:</label>
                    <div class="part-no-display">
                        <div>${barang.part_no}</div>
                    </div>
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
                    <button type="button" class="btn btn-danger btn-sm remove-barang w-100" data-row="${itemCounter}" data-qr="${qrLabelToStore}">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </div>
            </div>
        `;
        
        $('#items_container').append(newRow);
        
        console.log('Row added. Total rows:', $('.barang-row').length); // Debug log
        
        updateSummary();
    }

    // Remove Item Row
    $(document).on('click', '.remove-barang', function() {
        let row = $(this).data('row');
        
        Swal.fire({
            title: 'Hapus Item?',
            text: "Apakah Anda yakin ingin menghapus barang ini dari list?",
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
                    
                    // Show empty state if no items
                    if ($('.barang-row').length === 0) {
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
        let totalItems = $('.barang-row').length;
        let totalQty = 0;
        
        $('.qty-input').each(function() {
            totalQty += parseInt($(this).val()) || 0;
        });
        
        console.log('Summary - Items:', totalItems, 'Total Qty:', totalQty); // Debug log
    }

    // Submit Form
    $('#form_barang_masuk').on('submit', function(e) {
        e.preventDefault();
        
        // Validasi minimal 1 item
        if ($('.barang-row').length === 0) {
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

        $('.barang-row').each(function() {
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
            html: `
                <div class="text-left">
                    <p class="mb-3">Apakah anda yakin ingin menyimpan transaksi ini?</p>
                    <p class="mb-0 mt-3">Lanjutkan?</p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal',
            width: '500px'
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

        // Show loading
        Swal.fire({
            title: 'Menyimpan...',
            text: 'Sedang menyimpan transaksi barang masuk...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
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
                            value.forEach(function(error) {
                                errorList += '<li>' + error + '</li>';
                            });
                        } else {
                            errorList += '<li>' + value + '</li>';
                        }
                    });
                    
                    errorList += '</ul>';
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Validasi Gagal!',
                        html: errorList,
                        width: '600px'
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