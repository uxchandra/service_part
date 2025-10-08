@extends('layouts.app')

@include('barang.create')
@include('barang.edit')
@include('barang.import')

@section('content')
    <div class="section-header">
        <h1>Data Barang</h1>
        <div class="ml-auto">
            <a href="javascript:void(0)" class="btn btn-success" id="button_import">
                <i class="fa fa-upload"></i> Import Data
            </a>
            <a href="javascript:void(0)" class="btn btn-primary" id="button_tambah_barang">
                <i class="fa fa-plus"></i> Tambah Barang
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_id" class="display" style="font-size: 13px; width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align: center">QR Label</th>
                                    <th>Part No</th>
                                    <th>Customer</th>
                                    <th>Part Name</th>
                                    <th>Size Plastic</th>
                                    <th>Part Color</th>
                                    <th style="text-align: center">Keypoint</th>
                                    <th style="text-align: center">Stok</th>
                                    <th>Opsi</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
<style>
    .keypoint-thumbnail {
        width: 60px;
        height: 60px;
        object-fit: cover;
        cursor: pointer;
        border-radius: 4px;
        border: 2px solid #e3e6f0;
        transition: all 0.3s ease;
    }

    .keypoint-thumbnail:hover {
        border-color: #4e73df;
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(78, 115, 223, 0.3);
    }

    .no-image-box {
        width: 60px;
        height: 60px;
        background: #f8f9fc;
        border: 2px dashed #d1d3e2;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #858796;
        font-size: 10px;
        text-align: center;
    }

    /* Sweet Alert Custom Styles */
    .swal-wide {
        max-width: 80vw !important;
        width: auto !important;
    }

    .swal2-image {
        max-width: 100% !important;
        height: auto !important;
        border-radius: 8px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    let table = $('#table_id').DataTable({
        processing: false,
        serverSide: true,
        paging: true,
        autoWidth: false,
        lengthMenu: [
            [10, 25, 50, 100, 500, -1],
            [10, 25, 50, 100, 500, "Show All"]
        ],
        pageLength: 25,
        ajax: {
            url: '/barang/get-data',
            type: 'GET'
        },
        columns: [
            { data: 'barcode_html', orderable: false, searchable: false },
            { data: 'part_no' },
            { data: 'customer' },
            { data: 'part_name' },
            { data: 'size_plastic' },
            { data: 'part_color' },
            { 
                data: 'keypoint_url',
                orderable: false,
                searchable: false,
                className: 'text-center',
                render: function(data, type, row) {
                    if (data) {
                         return `<img src="${data}" 
                                      alt="Keypoint" 
                                      class="preview-keypoint" 
                                      style="width:60px;height:60px;object-fit:cover;border-radius:4px;border:2px solid #e3e6f0;"
                                      data-src="${data}"
                                      onerror="this.onerror=null; this.src=''; this.parentElement.innerHTML='<div class=\'no-image-box\'>Image Not Found</div>';">`;
                    } else {
                        return `<div class="no-image-box">No Image</div>`;
                    }
                }
            },
            { 
                data: 'stok', 
                orderable: true, 
                searchable: true,
                className: 'text-center'
            },
            { 
                data: 'id',
                render: function(data) {
                    return `<div class="d-flex">
                        <a href="javascript:void(0)" class="btn btn-icon btn-warning mx-1 btn_edit" data-id="${data}" style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;">
                            <i class="far fa-edit"></i>
                        </a>
                        <a href="javascript:void(0)" class="btn btn-icon btn-danger mx-1 btn_hapus" data-id="${data}" style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-trash"></i>
                        </a> 
                    </div>`;
                },
                orderable: false,
                searchable: false
            }
        ],
    });

    // Preview Keypoint Image on Click
    $(document).on('click', '.preview-keypoint', function() {
        let imageSrc = $(this).data('src');
        
        Swal.fire({
            title: 'Preview Keypoint',
            imageUrl: imageSrc,
            imageWidth: 'auto',
            imageHeight: '400px',
            imageAlt: 'Keypoint Image',
            showConfirmButton: false,
            showCloseButton: true,
            customClass: {
                popup: 'swal-wide'
            },
            didOpen: () => {
                // Add custom CSS for wider popup
                const popup = Swal.getPopup();
                if (popup) {
                    popup.style.maxWidth = '80vw';
                    popup.style.width = 'auto';
                }
            }
        });
    });

    // Edit Barang
    $(document).on('click', '.btn_edit', function() {
        let id = $(this).data('id');
        
        $.ajax({
            url: `/barang/${id}`,
            type: 'GET',
            success: function(response) {
                if (response.status === 'success') {
                    $('#edit_id').val(response.data.id);
                    $('#edit_qr_label').val(response.data.qr_label);
                    $('#edit_part_no').val(response.data.part_no);
                    $('#edit_customer').val(response.data.customer);
                    $('#edit_part_name').val(response.data.part_name);
                    $('#edit_size_plastic').val(response.data.size_plastic);
                    $('#edit_part_color').val(response.data.part_color);
                    $('#edit_stok').val(response.data.stok || 0);
                    
                    // Preview keypoint jika ada
                    if (response.data.keypoint_url) {
                        $('#edit_keypoint_preview').attr('src', response.data.keypoint_url).show();
                        $('#edit_keypoint_path').text(response.data.keypoint);
                    } else {
                        $('#edit_keypoint_preview').hide();
                        $('#edit_keypoint_path').text('-');
                    }
                    
                    $('#modal_edit_barang').modal('show');
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Data tidak ditemukan'
                });
            }
        });
    });

    // Hapus Barang
    $(document).on('click', '.btn_hapus', function() {
        let id = $(this).data('id');
        
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/barang/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Terhapus!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            table.ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Gagal menghapus data'
                        });
                    }
                });
            }
        });
    });

    // Import Data
    $('#button_import').on('click', function() {
        $('#modal_import_barang').modal('show');
    });

    $('#form_import_barang').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        let fileInput = $('#import_file')[0];
        
        if (!fileInput.files.length) {
            Swal.fire('Error!', 'Silakan pilih file terlebih dahulu', 'error');
            return;
        }

        // Show loading
        Swal.fire({
            title: 'Mengupload...',
            text: 'Sedang mengupload dan memproses file, mohon tunggu...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '/barang/import',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Swal.close();
                
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    $('#modal_import_barang').modal('hide');
                    $('#form_import_barang')[0].reset();
                    table.ajax.reload(null, false);
                }
            },
            error: function(xhr) {
                Swal.close();
                
                let response = xhr.responseJSON;
                let errorHtml = '<ul class="text-left mb-0">';
                
                if (response && response.errors) {
                    response.errors.forEach(function(error) {
                        errorHtml += '<li>' + error + '</li>';
                    });
                } else {
                    errorHtml += '<li>' + (response.message || 'Terjadi kesalahan saat import') + '</li>';
                }
                
                errorHtml += '</ul>';

                Swal.fire({
                    icon: 'error',
                    title: 'Import Gagal!',
                    html: errorHtml,
                    width: '600px'
                });
            }
        });
    });

    // Tambah Barang
    $('#button_tambah_barang').on('click', function() {
        $('#form_tambah_barang')[0].reset();
        clearErrors();
        $('#modal_tambah_barang').modal('show');
    });

    // Helper functions
    function displayErrors(errors, prefix) {
        $.each(errors, function(key, value) {
            let errorElement = $(`#error_${prefix}${key}`);
            errorElement.removeClass('d-none').text(value[0]);
            errorElement.siblings('input, select, textarea').addClass('is-invalid');
        });
    }

    function clearErrors() {
        $('.invalid-feedback').addClass('d-none').text('');
        $('.form-control').removeClass('is-invalid');
    }

    // Reset form when modal closed
    $('#modal_tambah_barang, #modal_edit_barang').on('hidden.bs.modal', function() {
        $(this).find('form')[0].reset();
        clearErrors();
    });
});
</script>
@endpush