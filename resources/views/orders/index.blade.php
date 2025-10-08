@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Orders</h1>
        <div class="ml-auto">
            <a href="javascript:void(0)" class="btn btn-success mr-2" id="btn_import">
                <i class="fa fa-file-excel"></i> Import Orders
            </a>
            <a href="{{ route('orders.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Tambah Order
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_orders" class="display" style="font-size: 13px; width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align:center">No Order</th>
                                    <th>Item (Part No)</th>
                                    <th class="text-center">Qty</th>
                                    <th>Delivery Date</th>
                                    <th>Status</th>
                                    <th style="text-align:center">Aksi</th>
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

<!-- Import Modal -->
<div class="modal fade" id="modal_import" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Orders</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_import" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>File Excel</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="import_file" name="import_file" accept=".xlsx, .xls">
                            <label class="custom-file-label" for="import_file">Pilih file...</label>
                        </div>
                        <small class="text-muted">Format: .xlsx atau .xls</small>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('orders.download-template') }}" class="btn btn-sm btn-secondary">
                            <i class="fa fa-download"></i> Download Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Custom file input
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Import button click
    $('#btn_import').on('click', function() {
        $('#form_import')[0].reset();
        $('.custom-file-label').html('Pilih file...');
        $('#modal_import').modal('show');
    });

    // Handle import form submission
    $('#form_import').on('submit', function(e) {
        e.preventDefault();
        
        let formData = new FormData(this);
        
        if (!$('#import_file')[0].files.length) {
            Swal.fire('Error', 'Pilih file terlebih dahulu', 'error');
            return;
        }

        Swal.fire({
            title: 'Mengimport...',
            text: 'Mohon tunggu, sedang memproses data...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route("orders.import") }}',
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
                        text: response.message
                    });

                    $('#modal_import').modal('hide');
                    table.ajax.reload();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                Swal.close();
                
                let response = xhr.responseJSON;
                let errorHtml = '<div style="text-align: left;">';
                
                if (response && response.errors) {
                    errorHtml += '<ul class="mb-0">';
                    response.errors.forEach(function(error) {
                        errorHtml += '<li>' + error + '</li>';
                    });
                    errorHtml += '</ul>';
                } else {
                    errorHtml += response.message || 'Terjadi kesalahan saat import data';
                }
                
                errorHtml += '</div>';

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    html: errorHtml
                });
            }
        });
    });

    let table = $('#table_orders').DataTable({
        processing: false,
        serverSide: true,
        paging: true,
        autoWidth: false,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "Show All"]
        ],
        pageLength: 25,
        ajax: {
            url: '{{ route("orders.get-data") }}',
            type: 'GET'
        },
        columns: [
            { data: 'no_transaksi_display', className: 'text-center' },
            { data: 'part_no' },
            { data: 'qty', className: 'text-center' },
            { data: 'delivery_date_display' },
            { data: 'status_display' },
            { 
                data: 'actions', 
                className: 'text-center',
                orderable: false,
                searchable: false,
                render: function(_, __, row) {
                    if (!row.is_group_start) return '';
                    
                    // Tombol Scan hanya muncul jika status planning atau partial
                    let scanButton = '';
                    if (row.status === 'planning' || row.status === 'partial') {
                        scanButton = `
                            <a href="/pulling/create?order_id=${row.order_id}" 
                               class="btn btn-icon btn-info btn-sm mr-2" 
                               title="Pulling" 
                               data-toggle="tooltip" 
                               style="width: 26px; height: 26px; display:flex; align-items:center; justify-content:center;">
                                <i class="fa fa-qrcode"></i>
                            </a>
                        `;
                    }
                    
                    // Tombol Check muncul jika status partial atau pulling
                    let checkButton = '';
                    if (row.status === 'partial' || row.status === 'pulling') {
                        checkButton = `
                            <a href="/packing/create?order_id=${row.order_id}" 
                               class="btn btn-icon btn-success btn-sm mr-2" 
                               title="Check ISP Packing" 
                               data-toggle="tooltip" 
                               style="width: 26px; height: 26px; display:flex; align-items:center; justify-content:center;">
                                <i class="fa fa-box-open"></i>
                            </a>
                        `;
                    }
                    
                    return `
                        <div class="d-flex justify-content-end">
                             ${/* <a href="/orders/${row.order_id}" 
                                    class="btn btn-icon btn-warning btn-sm mr-2" 
                                    title="Edit" 
                                    data-toggle="tooltip" 
                                    style="width: 26px; height: 26px; display:flex; align-items:center; justify-content:center;">
                                    <i class="far fa-edit"></i>
                                </a> */''}
                            ${scanButton}
                            ${checkButton}
                            <button type="button" 
                                    class="btn btn-icon btn-danger btn-sm btn_delete" 
                                    data-id="${row.order_id}" 
                                    title="Hapus" 
                                    data-toggle="tooltip" 
                                    style="width: 26px; height: 26px; display:flex; align-items:center; justify-content:center;">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>`;
                }
            },
        ],
        createdRow: function(row, data) {
            if (data.is_group_start) {
                $(row).addClass('order-group-start');
            }
        },
    });

    // Responsif saat sidebar ditoggle/resize/selesai transisi
    $('[data-toggle="sidebar"]').on('click', function() {
        setTimeout(function(){
            table.columns.adjust().draw(false);
        }, 350);
    });

    $(window).on('resize', function(){
        table.columns.adjust().draw(false);
    });

    $('.main-sidebar').on('transitionend webkitTransitionEnd', function(){
        table.columns.adjust().draw(false);
    });

    // Tooltip init
    $('[data-toggle="tooltip"]').tooltip();

    // Hapus order
    $(document).on('click', '.btn_delete', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Hapus Order?',
            text: 'Data tidak dapat dikembalikan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/orders/${id}`,
                    type: 'DELETE',
                    data: { _token: $('meta[name="csrf-token"]').attr('content') },
                    success: function(res){
                        if (res.status === 'success') {
                            Swal.fire('Terhapus', res.message, 'success');
                            table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Gagal', res.message || 'Gagal menghapus', 'error');
                        }
                    },
                    error: function(){
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus', 'error');
                    }
                });
            }
        });
    });
});
</script>
<style>
    /* Garis pemisah antar transaksi */
    #table_orders tbody tr.order-group-start td {
        border-top: 3px solid #4e73df !important;
    }
    #table_orders tbody tr:first-child.order-group-start td {
        border-top-width: 0 !important;
    }
    #table_orders tbody td {
        vertical-align: middle;
    }
</style>
@endpush