<!-- Detail Order Modal -->
<div class="modal fade" id="modal_detail" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" style="color: #000;">Detail Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="detail-content">
                    <div class="text-center">
                        <div class="spinner-border" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-dark" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function showOrderDetail(orderId) {
    $('#modal_detail').modal('show');
    $('#detail-content').html('<div class="text-center"><div class="spinner-border" role="status"><span class="sr-only">Loading...</span></div></div>');
    
    $.ajax({
        url: `/orders/${orderId}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const order = response.data;
                renderOrderDetail(order);
            } else {
                $('#detail-content').html('<div class="alert alert-danger">Gagal memuat data order</div>');
            }
        },
        error: function() {
            $('#detail-content').html('<div class="alert alert-danger">Terjadi kesalahan saat memuat data</div>');
        }
    });
}

function renderOrderDetail(order) {
    let html = `
        <div class="row">
            <div class="col-md-6">
                <table class="table table-sm">
                    <tr>
                        <td><strong>No Transaksi:</strong></td>
                        <td>${order.no_transaksi}</td>
                    </tr>
                    <tr>
                        <td><strong>Delivery Date:</strong></td>
                        <td>${order.delivery_date ? new Date(order.delivery_date).toLocaleDateString('id-ID') : '-'}</td>
                    </tr>
                    <tr>
                        <td><strong>Status:</strong></td>
                        <td>${(function(){
                            const s = (order.effective_status || order.status || '').toLowerCase();
                            let cls = 'badge-secondary', label = s.charAt(0).toUpperCase() + s.slice(1);
                            if (s === 'planning') cls = 'badge-primary';
                            else if (s === 'partial') cls = 'badge-warning';
                            else if (s === 'pulling') cls = 'badge-info';
                            else if (s === 'delay') cls = 'badge-danger';
                            else if (s === 'completed') cls = 'badge-success';
                            return `<span class="badge ${cls}">${label}</span>`;
                        })()}</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <hr>
        
        <div class="table-responsive">
            <table class="table table-sm table-bordered" style="color: #000;">
                <thead>
                    <tr>
                        <th>Part No</th>
                        <th class="text-center">Qty Order</th>
                        <th class="text-center">Qty Pulling</th>
                        <th class="text-center">Qty Packing</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    // Group items by part_no
    const itemMap = {};
    order.order_items.forEach(item => {
        if (!itemMap[item.part_no]) {
            itemMap[item.part_no] = {
                part_no: item.part_no,
                qty_order: 0,
                qty_pulling: 0,
                qty_packing: 0
            };
        }
        itemMap[item.part_no].qty_order += item.quantity;
    });
    
    // Add pulling data
    order.barang_keluar.forEach(bk => {
        bk.items.forEach(item => {
            const partNo = item.barang ? item.barang.part_no : null;
            if (partNo && itemMap[partNo]) {
                itemMap[partNo].qty_pulling += item.quantity;
            }
        });
        
        // Add packing data
        if (bk.isp_packing) {
            bk.isp_packing.items.forEach(item => {
                const partNo = item.barang ? item.barang.part_no : null;
                if (partNo && itemMap[partNo]) {
                    itemMap[partNo].qty_packing += item.qty_isp;
                }
            });
        }
    });
    
    // Render items
    Object.values(itemMap).forEach(item => {
        html += `
            <tr>
                <td style="color: #000;">${item.part_no}</td>
                <td class="text-center" style="color: #000;">${item.qty_order}</td>
                <td class="text-center" style="color: #000;">${item.qty_pulling}</td>
                <td class="text-center" style="color: #000;">${item.qty_packing}</td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    $('#detail-content').html(html);
}

function getPullingProgress(order) {
    const totalOrder = order.order_items.reduce((sum, item) => sum + item.quantity, 0);
    const totalPulling = order.barang_keluar.reduce((sum, bk) => 
        sum + bk.items.reduce((itemSum, item) => itemSum + item.quantity, 0), 0);
    return `${totalPulling}/${totalOrder}`;
}

function getPackingProgress(order) {
    const totalOrder = order.order_items.reduce((sum, item) => sum + item.quantity, 0);
    const totalPacking = order.barang_keluar.reduce((sum, bk) => {
        if (bk.isp_packing) {
            return sum + bk.isp_packing.items.reduce((itemSum, item) => itemSum + item.qty_isp, 0);
        }
        return sum;
    }, 0);
    return `${totalPacking}/${totalOrder}`;
}
</script>
