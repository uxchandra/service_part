@extends('layouts.app')

@include('barang-masuk.detail')
@section('content')
    <div class="section-header">
        <h1>Data Posting</h1>
        <div class="ml-auto">
            <a href="{{ route('barang-masuk.create') }}" class="btn btn-primary">
                <i class="fa fa-plus"></i> Tambah Posting
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table_barang_masuk" class="display" style="font-size: 13px; width:100%;">
                            <thead>
                                <tr>
                                    <th style="width: 5%">No</th>
                                    <th>Tanggal</th>
                                    <th style="text-align: center">Total Transaksi</th>
                                    <th style="width: 10%; text-align: center">Aksi</th>
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
    .transaction-item {
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .transaction-header {
        background: white;
        padding: 12px 15px;
        border-radius: 6px;
        margin-bottom: 15px;
        border-left: 4px solid #4e73df;
    }

    .transaction-body {
        padding: 0 15px;
    }

    .items-table {
        background: white;
        margin-bottom: 0;
    }

    .items-table thead {
        background: #f8f9fc;
    }

    .items-table th {
        font-weight: 600;
        font-size: 0.85rem;
        color: #5a5c69;
        border-bottom: 2px solid #e3e6f0;
    }

    .items-table td {
        font-size: 0.875rem;
        vertical-align: middle;
    }

    #transactions-container:empty::after {
        content: "Tidak ada data transaksi";
        display: block;
        text-align: center;
        padding: 40px;
        color: #858796;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    let table = $('#table_barang_masuk').DataTable({
        processing: false,
        serverSide: true,
        paging: true,
        autoWidth: false,
        lengthMenu: [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "Show All"]
        ],
        pageLength: 10,
        ajax: {
            url: '{{ route("barang-masuk.get-data") }}',
            type: 'GET'
        },
        columns: [
            { 
                data: null,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                },
                orderable: false,
                searchable: false
            },
            { data: 'date_formatted' },
            { 
                data: 'transaction_count', 
                className: 'text-center',
                render: function(data) {
                    return `<span style="font-size: 0.9rem;">${data}</span>`;
                }
            },
            { 
                data: 'date',
                className: 'text-center',
                render: function(data) {
                    return `
                        <button type="button" class="btn btn-info btn-sm btn_detail" data-date="${data}">
                            <i class="fa fa-eye"></i> Detail
                        </button>
                    `;
                },
                orderable: false,
                searchable: false
            }
        ]
    });

    // Adjust columns when sidebar is toggled (allow sidebar animation to finish)
    $('[data-toggle="sidebar"]').on('click', function() {
        setTimeout(function(){
            table.columns.adjust().draw(false);
        }, 350);
    });

    // Adjust columns on window resize
    $(window).on('resize', function(){
        table.columns.adjust().draw(false);
    });

    // Adjust after sidebar transition ends
    $('.main-sidebar').on('transitionend webkitTransitionEnd', function(){
        table.columns.adjust().draw(false);
    });

    // Show Detail Modal
    $(document).on('click', '.btn_detail', function() {
        let date = $(this).data('date');
        
        // Show loading state in modal
        $('#transactions-container').html('<div class="text-center py-4"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>');
        $('#modal-date-title').text('Loading...');
        
        // Show the modal
        $('#dateDetailModal').modal('show');
        
        // Fetch transactions for the date
        $.ajax({
            url: `/barang-masuk/date/${date}/transactions`,
            type: 'GET',
            success: function(data) {
                // Update modal title
                $('#modal-date-title').text(data.date);
                
                // Generate transactions HTML
                let transactionsHtml = '';

                if (data.transactions.length === 0) {
                    transactionsHtml = '<div class="text-center py-4 text-muted"><i class="fa fa-inbox fa-3x mb-3"></i><p>Tidak ada transaksi</p></div>';
                } else {
                    data.transactions.forEach((transaction, index) => {
                        // Hitung nomor transaksi mulai dari yang terbesar (transaksi terbaru = nomor terbesar)
                        const transactionNumber = data.transactions.length - index;
                        
                        transactionsHtml += `
                            <div class="transaction-item" style="font-size: 12px;">
                                <div class="transaction-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0" style="color: #000;">
                                        <i class="fas fa-clock mr-2"></i>
                                        Transaksi ${transactionNumber} - ${transaction.time}
                                    </h6>
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-secondary mr-2">${transaction.items_count} item</span>
                                        <span class="badge badge-info">${transaction.total_quantity} qty</span>
                                    </div>
                                </div>
                                <div class="transaction-body">
                                    <div class="mb-3 mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-user mr-1"></i>
                                            Input by: <strong>${transaction.user_name}</strong>
                                        </small>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered items-table">
                                            <thead>
                                                <tr>
                                                    <th style="font-size: 14px;">Part No</th>
                                                    <th style="font-size: 14px;">Part Name</th>
                                                    <th style="font-size: 14px;">Customer</th>
                                                    <th style="width: 10%; font-size: 12px; text-align: center">Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                        `;

                        transaction.items.forEach((item, itemIndex) => {
                            transactionsHtml += `
                                <tr>
                                    <td style="font-size: 14px;">${item.part_no}</td>
                                    <td style="font-size: 14px;">${item.part_name}</td>
                                    <td style="font-size: 14px;">${item.customer}</td>
                                    <td class="text-center" style="font-size: 14px;"><strong>${item.quantity}</strong></td>
                                </tr>
                            `;
                        });

                        transactionsHtml += `
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                }
                
                $('#transactions-container').html(transactionsHtml);
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                $('#transactions-container').html('<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-circle mr-2"></i>Gagal memuat data transaksi</div>');
            }
        });
    });
});
</script>
@endpush