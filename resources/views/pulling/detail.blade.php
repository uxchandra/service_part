<!-- Modal Detail Items -->
<div class="modal fade" id="modalDetailItems" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa fa-list mr-2"></i>
                    Detail Transaksi - <span id="modal_no_transaksi"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="transactions-container">
                    <!-- Transactions will be loaded here -->
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
