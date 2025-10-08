@extends('layouts.app')

@section('content')
    <div class="section-header">
        <h1>ISP Packing</h1>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="table_packing" class="display" style="font-size: 13px; width:100%;">
                    <thead>
                        <tr>
                            <th>No Transaksi</th>
                            <th>Delivery Date</th>
                            <th class="text-center">Transaksi Pulling</th>
                            <th class="text-center">Progress Packing</th>
                            <!-- <th class="text-center">Status</th> -->
                            <th class="text-center">Terakhir Update</th>
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
    let table = $('#table_packing').DataTable({
        processing: false,
        serverSide: true,
        paging: true,
        autoWidth: false,
        ajax: { url: '{{ route("packing.get-data") }}', type: 'GET' },
        columns: [
            { data: 'no_transaksi' },
            { data: 'delivery_date' },
            { 
                data: 'transactions_count', 
                className: 'text-center',
                render: function(data, type, row) {
                    return `<span class="badge badge-info">${data} transaksi</span>`;
                }
            },
            { 
                data: 'progress_percentage', 
                className: 'text-center',
                render: function(data, type, row) {
                    const progressBar = `
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar ${getProgressBarClass(row.packing_status)}" 
                                 role="progressbar" 
                                 style="width: ${data}%" 
                                 aria-valuenow="${data}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                ${data}%
                            </div>
                        </div>
                        <small class="text-muted">${row.total_qty_isp}/${row.total_qty_pulling} qty</small>
                    `;
                    return progressBar;
                }
            },
            // { 
            //     data: 'status_text', 
            //     className: 'text-center',
            //     render: function(data, type, row) {
            //         return `<span class="badge badge-${row.status_class}">${data}</span>`;
            //     }
            // },
            { data: 'updated_at' },
            { 
                data: 'id', 
                className: 'text-center', 
                orderable: false, 
                searchable: false, 
                render: function(data, type, row) {
                    let buttons = '';
                    
                    // Tombol Packing (selalu ada untuk order yang sudah di-pulling)
                    buttons += `
                        <a href="/packing/create?order_id=${row.id}" class="btn btn-sm btn-primary mr-1" title="Mulai/Continue Packing">
                            <i class="fa fa-box"></i>
                        </a>
                    `;
                    
                    // Tombol Detail (opsional)
                    buttons += `
                        <button class="btn btn-sm btn-info btn-detail" data-id="${row.id}" data-no-transaksi="${row.no_transaksi}" title="Detail Order">
                            <i class="fa fa-eye"></i>
                        </button>
                    `;
                    
                    return buttons;
                }
            },
        ]
    });

    // Helper function untuk progress bar class
    function getProgressBarClass(packingStatus) {
        switch(packingStatus) {
            case 'completed': return 'bg-success';
            case 'in_progress': return 'bg-warning';
            case 'draft': return 'bg-info';
            default: return 'bg-secondary';
        }
    }

    // Handle detail button click (opsional - bisa diimplementasi nanti)
    $(document).on('click', '.btn-detail', function(){
        const id = $(this).data('id');
        const noTransaksi = $(this).data('no-transaksi');
        
        // Redirect ke order detail atau buka modal
        Swal.fire({
            title: 'Detail Order',
            text: `Detail untuk ${noTransaksi} akan ditampilkan di sini`,
            icon: 'info'
        });
    });

    $('[data-toggle="sidebar"]').on('click', function(){ 
        setTimeout(function(){ table.columns.adjust().draw(false); }, 350); 
    });
    $(window).on('resize', function(){ 
        table.columns.adjust().draw(false); 
    });
    $('.main-sidebar').on('transitionend webkitTransitionEnd', function(){ 
        table.columns.adjust().draw(false); 
    });
});
</script>
@endpush
