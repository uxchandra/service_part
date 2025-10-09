@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>Orders</h1>
        <div class="ml-auto">
            @if(auth()->user()->role->name === 'admin scanner')
                <a href="{{ route('mobile.dashboard') }}" class="btn btn-dark btn-sm">
                    <i class="fa fa-arrow-left"></i> Kembali
                </a>
            @else
                <a href="javascript:void(0)" class="btn btn-success btn-sm mr-2" id="btn_import">
                    <i class="fa fa-file-excel"></i> <span class="d-none d-md-inline">Import</span>
                </a>
                <a href="{{ route('orders.create') }}" class="btn btn-primary btn-sm">
                    <i class="fa fa-plus"></i> <span class="d-none d-md-inline">Tambah</span>
                </a>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-2">
                    <!-- Mobile Card View -->
                    <div class="d-block d-md-none" id="mobile-view">
                        <div class="mb-2">
                            <input type="text" id="mobile-search" class="form-control form-control-sm" placeholder="Cari...">
                        </div>
                        <div id="mobile-cards"></div>
                        <div class="text-center mt-2">
                            <button id="load-more" class="btn btn-secondary btn-sm" style="display:none;">
                                <i class="fa fa-sync"></i> Muat Lebih
                            </button>
                        </div>
                    </div>

                    <!-- Desktop Table -->
                    <div class="table-responsive d-none d-md-block">
                        <table id="table_orders" class="display" style="font-size: 13px; width:100%;">
                            <thead>
                                <tr>
                                    <th style="text-align:center">No Transaksi</th>
                                    <th class="text-center">Jumlah Item</th>
                                    <th>Delivery Date</th>
                                    <th>Status</th>
                                    <th class="text-center">Progress</th>
                                    <th style="text-align:center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@include('orders.show')

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
                        <a href="{{ route('orders.download-template') }}" class="btn btn-sm btn-success">
                            <i class="fa fa-download"></i> Download Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Import</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let mobileData = [], filteredData = [], displayCount = 10;
    const isMobile = $(window).width() < 768;

    // Custom file input
    $('.custom-file-input').on('change', function() {
        let fileName = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').addClass("selected").html(fileName);
    });

    // Import button
    $('#btn_import').on('click', function() {
        $('#form_import')[0].reset();
        $('.custom-file-label').html('Pilih file...');
        $('#modal_import').modal('show');
    });

    // Import form
    $('#form_import').on('submit', function(e) {
        e.preventDefault();
        let formData = new FormData(this);
        
        if (!$('#import_file')[0].files.length) {
            Swal.fire('Error', 'Pilih file terlebih dahulu', 'error');
            return;
        }

        Swal.fire({
            title: 'Mengimport...',
            text: 'Mohon tunggu...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => Swal.showLoading()
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
                    Swal.fire('Berhasil!', response.message, 'success');
                    $('#modal_import').modal('hide');
                    isMobile ? loadMobileData() : table.ajax.reload();
                } else {
                    Swal.fire('Gagal!', response.message, 'error');
                }
            },
            error: function(xhr) {
                Swal.close();
                let response = xhr.responseJSON;
                let errorHtml = '<div style="text-align: left;">';
                if (response && response.errors) {
                    errorHtml += '<ul class="mb-0">';
                    response.errors.forEach(e => errorHtml += '<li>' + e + '</li>');
                    errorHtml += '</ul>';
                } else {
                    errorHtml += response.message || 'Terjadi kesalahan';
                }
                errorHtml += '</div>';
                Swal.fire('Gagal!', errorHtml, 'error');
            }
        });
    });

    // Initialize
    if (isMobile) {
        loadMobileData();
    } else {
        initTable();
    }

    // Desktop Table
    function initTable() {
        window.table = $('#table_orders').DataTable({
            processing: false,
            serverSide: true,
            paging: true,
            autoWidth: false,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Show All"]],
            pageLength: 25,
            ajax: { url: '{{ route("orders.get-data") }}', type: 'GET' },
            columns: [
                { data: 'no_transaksi_display', className: 'text-center' },
                { data: 'jumlah_item', className: 'text-center' },
                { data: 'delivery_date_display' },
                { data: 'status_display' },
                { 
                    data: 'progress_display', 
                    className: 'text-center',
                    orderable: false,
                    searchable: false
                },
                { 
                    data: 'actions', 
                    className: 'text-center',
                    orderable: false,
                    searchable: false,
                    render: function(_, __, row) {
                        const isAdminScanner = '{{ auth()->user()->role->name }}' === 'admin scanner';
                        
                        if (isAdminScanner) {
                            // Admin Scanner: hanya button scan/pulling
                            if (row.status === 'planning' || row.status === 'partial') {
                                return `<div class="d-flex justify-content-end"><a href="/pulling/create?order_id=${row.order_id}" class="btn btn-icon btn-info btn-sm" title="Pulling" data-toggle="tooltip" style="width: 26px; height: 26px; display:flex; align-items:center; justify-content:center;"><i class="fa fa-qrcode"></i></a></div>`;
                            }
                            return '';
                        }
                        
                        // Role lain: tampilkan semua button
                        let detailBtn = `<button type="button" class="btn btn-icon btn-primary btn-sm mr-2 btn_detail" data-id="${row.order_id}" title="Detail" data-toggle="tooltip" style="width: 26px; height: 26px; display:flex; align-items:center; justify-content:center;"><i class="fa fa-eye"></i></button>`;
                        
                        let scanBtn = '';
                        if (row.status === 'planning' || row.status === 'partial') {
                            scanBtn = `<a href="/pulling/create?order_id=${row.order_id}" class="btn btn-icon btn-info btn-sm mr-2" title="Pulling" data-toggle="tooltip" style="width: 26px; height: 26px; display:flex; align-items:center; justify-content:center;"><i class="fa fa-qrcode"></i></a>`;
                        }
                        
                        let checkBtn = '';
                        if (row.status === 'partial' || row.status === 'pulling') {
                            checkBtn = `<a href="/packing/create?order_id=${row.order_id}" class="btn btn-icon btn-success btn-sm mr-2" title="Check ISP Packing" data-toggle="tooltip" style="width: 26px; height: 26px; display:flex; align-items:center; justify-content:center;"><i class="fa fa-box-open"></i></a>`;
                        }
                        
                        return `<div class="d-flex justify-content-end">${detailBtn}${scanBtn}${checkBtn}<button type="button" class="btn btn-icon btn-danger btn-sm btn_delete" data-id="${row.order_id}" title="Hapus" data-toggle="tooltip" style="width: 26px; height: 26px; display:flex; align-items:center; justify-content:center;"><i class="fas fa-trash"></i></button></div>`;
                    }
                },
            ]
        });

        $('[data-toggle="sidebar"]').on('click', function() {
            setTimeout(() => table.columns.adjust().draw(false), 350);
        });
        $(window).on('resize', () => table.columns.adjust().draw(false));
        $('.main-sidebar').on('transitionend webkitTransitionEnd', () => table.columns.adjust().draw(false));
    }

    // Mobile Functions
    function loadMobileData() {
        $('#mobile-cards').html('<div class="loading-skeleton"></div>'.repeat(3));
        $.ajax({
            url: '{{ route("orders.get-data") }}',
            data: { length: -1 },
            success: function(response) {
                mobileData = response.data;
                filteredData = mobileData;
                renderCards();
            },
            error: () => $('#mobile-cards').html('<div class="text-center text-danger p-3">Gagal memuat</div>')
        });
    }

    function renderCards() {
        const container = $('#mobile-cards');
        container.empty();

        if (filteredData.length === 0) {
            container.html('<div class="text-center text-muted p-3">Tidak ada data</div>');
            $('#load-more').hide();
            return;
        }

        const display = filteredData.slice(0, displayCount);
        let currentOrder = null;
        
        display.forEach(item => {
            let statusBadge = '';
            if (item.status === 'planning') statusBadge = '<span class="badge badge-secondary">Planning</span>';
            else if (item.status === 'partial') statusBadge = '<span class="badge badge-warning">Partial</span>';
            else if (item.status === 'pulling') statusBadge = '<span class="badge badge-info">Pulling</span>';
            else if (item.status === 'delay') statusBadge = '<span class="badge badge-danger">Delay</span>';
            else if (item.status === 'completed') statusBadge = '<span class="badge badge-success">Completed</span>';

            const isAdminScanner = '{{ auth()->user()->role->name }}' === 'admin scanner';
            let actions = '';
            
            if (isAdminScanner) {
                // Admin Scanner: hanya button scan/pulling
                if (item.status === 'planning' || item.status === 'partial') {
                    actions = `<a href="/pulling/create?order_id=${item.order_id}" class="btn btn-info btn-sm btn-block"><i class="fa fa-qrcode"></i> Pulling</a>`;
                }
            } else {
                // Role lain: tampilkan semua button
                actions += `<button class="btn btn-primary btn-sm mr-1 btn_detail" data-id="${item.order_id}"><i class="fa fa-eye"></i></button>`;
                if (item.status === 'planning' || item.status === 'partial') {
                    actions += `<a href="/pulling/create?order_id=${item.order_id}" class="btn btn-info btn-sm mr-1"><i class="fa fa-qrcode"></i></a>`;
                }
                if (item.status === 'partial' || item.status === 'pulling') {
                    actions += `<a href="/packing/create?order_id=${item.order_id}" class="btn btn-success btn-sm mr-1"><i class="fa fa-box-open"></i></a>`;
                }
                actions += `<button class="btn btn-danger btn-sm btn_delete" data-id="${item.order_id}"><i class="fa fa-trash"></i></button>`;
            }

            container.append(`
                <div class="mobile-card mb-2">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <div class="font-weight-bold" style="font-size:15px;color:#2c3e50;">${item.no_transaksi_display}</div>
                            <small class="text-muted"><i class="far fa-calendar mr-1"></i>${item.delivery_date_display}</small>
                            <br><small class="text-muted"><i class="fa fa-box mr-1"></i>${item.jumlah_item} item</small>
                        </div>
                        ${statusBadge}
                    </div>
                    ${actions ? '<div class="mobile-actions">' + actions + '</div>' : ''}
                </div>
            `);
        });

        $('#load-more').toggle(filteredData.length > displayCount);
    }

    $('#load-more').on('click', function() {
        displayCount += 10;
        renderCards();
    });

    $('#mobile-search').on('keyup', function() {
        const term = $(this).val().toLowerCase();
        filteredData = term === '' ? mobileData : mobileData.filter(i => 
            i.no_transaksi_display.toLowerCase().includes(term) || 
            i.status.toLowerCase().includes(term)
        );
        displayCount = 10;
        renderCards();
    });

    // Detail
    $(document).on('click', '.btn_detail', function() {
        const id = $(this).data('id');
        showOrderDetail(id);
    });

    // Delete
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
                            isMobile ? loadMobileData() : table.ajax.reload(null, false);
                        } else {
                            Swal.fire('Gagal', res.message || 'Gagal menghapus', 'error');
                        }
                    },
                    error: () => Swal.fire('Gagal', 'Terjadi kesalahan', 'error')
                });
            }
        });
    });

    $('[data-toggle="tooltip"]').tooltip();
});
</script>
<style>
@media (max-width: 767px) {
    body { font-size: 14px; }
    .section-header { padding: 10px 0; margin-bottom: 15px; }
    .section-header h1 { font-size: 18px; margin-bottom: 0; }
    .card-body { padding: 8px !important; }
    
    .mobile-card {
        background: white;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        padding: 12px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    }
    
    .mobile-actions {
        display: flex;
        gap: 4px;
        padding-top: 8px;
        border-top: 1px solid #f0f0f0;
    }
    
    .mobile-actions .btn {
        flex: 1;
        padding: 8px;
        font-size: 14px;
    }
    
    .btn-block {
        width: 100%;
        display: block;
    }
    
    .loading-skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
        height: 100px;
        border-radius: 8px;
        margin-bottom: 10px;
    }
    
    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .modal-dialog { margin: 5px; max-width: calc(100% - 10px); }
    .modal-body { padding: 10px; }
}

#table_orders tbody td {
    vertical-align: middle;
}
</style>
@endpush