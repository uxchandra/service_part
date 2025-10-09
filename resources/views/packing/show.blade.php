<!-- Modal Detail Packing -->
<div class="modal fade" id="modalDetailPacking" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa fa-box mr-2"></i>
                    Detail Packing - <span id="modal_no_transaksi"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="packing-container">
                    <!-- Packing data will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fa fa-times"></i> Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    .packing-item {
        background: #f8f9fc;
        border: 1px solid #e3e6f0;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }

    .packing-header {
        background: white;
        padding: 12px 15px;
        border-radius: 6px;
        margin-bottom: 15px;
        border-left: 4px solid #4e73df;
    }

    .packing-body {
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

    .progress-container {
        margin: 10px 0;
    }

    .progress {
        height: 8px;
        border-radius: 4px;
    }

    .progress-bar {
        border-radius: 4px;
    }

    #packing-container:empty::after {
        content: "Tidak ada data packing";
        display: block;
        text-align: center;
        padding: 40px;
        color: #858796;
    }
</style>
