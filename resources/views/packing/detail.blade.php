<!-- Modal Detail Item -->
<div class="modal fade" id="modalDetailItem" tabindex="-1" role="dialog">
    <div class="modal-dialog" style="max-width: 85vw;" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa fa-box mr-2"></i>
                    Detail Item - <span id="modal_part_no"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div id="keypoint-container">
                            <!-- Keypoint image will be loaded here -->
                        </div>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm">
                            <tr>
                                <td><strong>Part No:</strong></td>
                                <td id="detail_part_no">-</td>
                            </tr>
                            <tr>
                                <td><strong>Part Name:</strong></td>
                                <td id="detail_part_name">-</td>
                            </tr>
                            <tr>
                                <td><strong>Size Plastik:</strong></td>
                                <td id="detail_size_plastik">-</td>
                            </tr>
                            <tr>
                                <td><strong>Part Color:</strong></td>
                                <td id="detail_part_color">-</td>
                            </tr>
                            <tr>
                                <td><strong>Qty Order:</strong></td>
                                <td id="detail_qty_order">0</td>
                            </tr>
                            <!-- <tr>
                                <td><strong>Qty Pulling (Total):</strong></td>
                                <td id="detail_qty_pulling">0</td>
                            </tr> -->
                            <tr id="transaction_detail_row" style="display: none;">
                                <td><strong>Qty Pulling:</strong></td>
                                <td id="detail_qty_pulling_transaction">0</td>
                            </tr>
                            <tr>
                                <td><strong>Qty ISP:</strong></td>
                                <td><span class="badge badge-warning" id="detail_qty_isp">0</span></td>
                            </tr>
                        </table>
                        
                        <div class="mt-3">
                            <div class="form-group">
                                <label for="scan_input"><strong>Scan QR/Part No:</strong></label>
                                <input type="text" class="form-control" id="scan_input" 
                                       placeholder="Scan atau ketik QR/Part No..." 
                                       autocomplete="off">
                            </div>
                            <div class="mt-2">
                                <button type="button" class="btn btn-primary btn-submit" id="btn_submit" disabled>
                                    <i class="fa fa-check"></i> Submit
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .custom-modal-size {
        max-width: 75vw; /* atau 1200px, sesuai kebutuhan */
    }
</style>