@extends('layouts.app')

@include('pulling.detail')

@section('content')
    <div class="section-header">
        <h1>Pulling</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table_pulling" class="display" style="font-size: 13px; width:100%;">
                    <thead>
                        <tr>
                            <th>No Transaksi</th>
                            <th>Tanggal</th>
                            <th class="text-center">Jumlah Transaksi</th>
                            <!-- <th>User</th> -->
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
$(document).ready(function(){
    let table = $('#table_pulling').DataTable({
        processing: false,
        serverSide: true,
        paging: true,
        autoWidth: false,
        ajax: { url: '{{ route("pulling.get-data") }}', type: 'GET' },
        columns: [
            { data: 'no_transaksi' },
            { data: 'tanggal_keluar' },
            { data: 'items_count', className: 'text-center' },
            // { data: 'user_name' },
            { data: 'id', className: 'text-center', orderable: false, searchable: false, render: function(data, type, row){
                return `
                    <button class="btn btn-sm btn-info btn-detail" data-id="${row.id}" data-no-transaksi="${row.no_transaksi}">
                        <i class="fa fa-eye"></i> Detail
                    </button>
                `;
            }},
        ]
    });

    // Handle detail button click
    $(document).on('click', '.btn-detail', function(){
        const id = $(this).data('id');
        const noTransaksi = $(this).data('no-transaksi');
        
        $('#modal_no_transaksi').text(noTransaksi);
        $('#transactions-container').html('<div class="text-center py-4"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>');
        
        // Show modal
        $('#modalDetailItems').modal('show');
        
        // Load transactions
        $.ajax({
            url: `/pulling/detail/${id}`,
            type: 'GET',
            success: function(resp){
                if (resp.success) {
                    let transactionsHtml = '';

                    if (resp.transactions.length === 0) {
                        transactionsHtml = '<div class="text-center py-4 text-muted"><i class="fa fa-inbox fa-3x mb-3"></i><p>Tidak ada transaksi</p></div>';
                    } else {
                        resp.transactions.forEach((transaction, index) => {
                            transactionsHtml += `
                                <div class="transaction-item" style="font-size: 12px;">
                                    <div class="transaction-header d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0" style="color: #000;">
                                            <i class="fas fa-clock mr-2"></i>
                                            ${transaction.time}
                                        </h6>
                                        <div class="d-flex align-items-center">
                                            <span class="badge badge-secondary mr-2">${transaction.items_count} item</span>
                                            <span class="badge badge-info">${transaction.total_quantity} qty</span>
                                        </div>
                                    </div>
                                    <div class="transaction-body">
                                        <div class="mb-3 mt-2">
                                            <small class="text-dark">
                                                <i class="fas fa-user mr-1"></i>
                                                Scanned by: <strong>${transaction.user_name}</strong>
                                            </small>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered items-table">
                                                <thead>
                                                    <tr>
                                                        <th style="font-size: 14px;">Part No</th>
                                                        <th style="font-size: 14px;">Part Name</th>
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
                } else {
                    $('#transactions-container').html('<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-circle mr-2"></i>Gagal memuat data transaksi</div>');
                }
            },
            error: function(){
                $('#transactions-container').html('<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-circle mr-2"></i>Terjadi kesalahan</div>');
            }
        });
    });

    $('[data-toggle="sidebar"]').on('click', function(){ setTimeout(function(){ table.columns.adjust().draw(false); }, 350); });
    $(window).on('resize', function(){ table.columns.adjust().draw(false); });
    $('.main-sidebar').on('transitionend webkitTransitionEnd', function(){ table.columns.adjust().draw(false); });
});
</script>
@endpush