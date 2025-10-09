@extends('layouts.app')

@include('barang-masuk.detail')
@section('content')
    <div class="section-header">
        <h1>Data Posting</h1>
        <div class="ml-auto">
        @if(auth()->user()->role->name === 'admin scanner')
            <a href="{{ route('mobile.dashboard') }}" class="btn btn-dark btn-sm">
                <i class="fa fa-arrow-left"></i> Kembali
            </a>
        @endif

        <a href="{{ route('barang-masuk.create') }}" class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Tambah
        </a>

        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-body p-2">
                    <!-- Mobile Card View (EDA50K) -->
                    <div class="d-block d-md-none" id="mobile-view">
                        <div class="search-box mb-3">
                            <input type="text" id="mobile-search" class="form-control form-control-sm" placeholder="Cari tanggal...">
                        </div>
                        <div id="mobile-cards-container">
                            <!-- Cards will be loaded here -->
                        </div>
                        <div class="text-center mt-3">
                            <button id="load-more" class="btn btn-secondary btn-sm" style="display: none;">
                                <i class="fa fa-sync"></i> Muat Lebih Banyak
                            </button>
                        </div>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="table-responsive d-none d-md-block">
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
    /* Mobile Optimizations for EDA50K (480x800) */
    @media (max-width: 767px) {
        body {
            font-size: 14px;
        }
        
        .section-header {
            padding: 10px 0;
            margin-bottom: 15px;
        }
        
        .section-header h1 {
            font-size: 18px;
            margin-bottom: 0;
        }
        
        .card {
            margin-bottom: 10px;
        }
        
        .card-body {
            padding: 8px !important;
        }

        /* Mobile Cards Style */
        .mobile-card {
            background: white;
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            cursor: pointer;
            transition: all 0.2s;
            min-height: 70px;
        }

        .mobile-card:active {
            background: #f8f9fc;
            transform: scale(0.98);
        }

        .mobile-card-date {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 6px;
        }

        .mobile-card-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .mobile-card-count {
            font-size: 14px;
            color: #6c757d;
        }

        .mobile-card-count i {
            color: #4e73df;
        }

        .mobile-card-btn {
            padding: 6px 12px;
            font-size: 13px;
            border-radius: 5px;
        }

        .search-box input {
            font-size: 14px;
            padding: 8px 12px;
            border-radius: 6px;
        }

        /* Loading State */
        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
            height: 70px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        @keyframes loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    }

    /* Modal Optimizations for Mobile */
    @media (max-width: 767px) {
        .modal-dialog {
            margin: 5px;
            max-width: calc(100% - 10px);
        }

        .modal-content {
            border-radius: 8px;
        }

        .modal-header {
            padding: 12px 15px;
        }

        .modal-title {
            font-size: 16px;
        }

        .modal-body {
            padding: 10px;
            max-height: calc(100vh - 120px);
            overflow-y: auto;
        }

        .transaction-item {
            font-size: 13px !important;
            padding: 10px !important;
            margin-bottom: 12px !important;
        }

        .transaction-header {
            padding: 8px 10px !important;
            margin-bottom: 10px !important;
        }

        .transaction-header h6 {
            font-size: 14px !important;
        }

        .items-table {
            font-size: 12px !important;
        }

        .items-table th {
            font-size: 12px !important;
            padding: 6px 4px !important;
        }

        .items-table td {
            font-size: 12px !important;
            padding: 6px 4px !important;
        }

        .badge {
            font-size: 11px;
            padding: 4px 6px;
        }
    }

    /* Desktop styles */
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
    let mobileData = [];
    let mobileDisplayCount = 10;
    let filteredData = [];

    // Check if mobile view
    const isMobile = $(window).width() < 768;

    if (isMobile) {
        // Load data for mobile cards
        loadMobileData();
    } else {
        // Initialize DataTable for desktop
        initDesktopTable();
    }

    // Initialize Desktop DataTable
    function initDesktopTable() {
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

        // Adjust columns when sidebar is toggled
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
    }

    // Load Mobile Data
    function loadMobileData() {
        $('#mobile-cards-container').html('<div class="loading-skeleton"></div><div class="loading-skeleton"></div><div class="loading-skeleton"></div>');
        
        $.ajax({
            url: '{{ route("barang-masuk.get-data") }}',
            type: 'GET',
            data: {
                length: -1 // Get all data
            },
            success: function(response) {
                mobileData = response.data;
                filteredData = mobileData;
                renderMobileCards();
            },
            error: function() {
                $('#mobile-cards-container').html('<div class="text-center text-danger p-3">Gagal memuat data</div>');
            }
        });
    }

    // Render Mobile Cards
    function renderMobileCards() {
        const container = $('#mobile-cards-container');
        container.empty();

        if (filteredData.length === 0) {
            container.html('<div class="text-center text-muted p-3">Tidak ada data</div>');
            $('#load-more').hide();
            return;
        }

        const displayData = filteredData.slice(0, mobileDisplayCount);
        
        displayData.forEach(function(item) {
            const card = $(`
                <div class="mobile-card" data-date="${item.date}">
                    <div class="mobile-card-date">
                        <i class="far fa-calendar-alt mr-2" style="color: #4e73df;"></i>${item.date_formatted}
                    </div>
                    <div class="mobile-card-info">
                        <span class="mobile-card-count">
                            <i class="fas fa-box mr-1"></i>
                            ${item.transaction_count} transaksi
                        </span>
                        <button class="btn btn-info btn-sm mobile-card-btn">
                            <i class="fa fa-eye"></i> Lihat
                        </button>
                    </div>
                </div>
            `);
            container.append(card);
        });

        // Show/hide load more button
        if (filteredData.length > mobileDisplayCount) {
            $('#load-more').show();
        } else {
            $('#load-more').hide();
        }
    }

    // Load More Button
    $('#load-more').on('click', function() {
        mobileDisplayCount += 10;
        renderMobileCards();
    });

    // Mobile Search
    $('#mobile-search').on('keyup', function() {
        const searchTerm = $(this).val().toLowerCase();
        
        if (searchTerm === '') {
            filteredData = mobileData;
        } else {
            filteredData = mobileData.filter(function(item) {
                return item.date_formatted.toLowerCase().includes(searchTerm);
            });
        }
        
        mobileDisplayCount = 10;
        renderMobileCards();
    });

    // Mobile Card Click Handler
    $(document).on('click', '.mobile-card', function(e) {
        e.preventDefault();
        const date = $(this).data('date');
        showDetailModal(date);
    });

    // Desktop Detail Button
    $(document).on('click', '.btn_detail', function() {
        const date = $(this).data('date');
        showDetailModal(date);
    });

    // Show Detail Modal Function
    function showDetailModal(date) {
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

                        transaction.items.forEach((item) => {
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
    }
});
</script>
@endpush