<!-- Modal Detail Item - Compact Version with Inline CSS -->
<div class="modal fade" id="modalDetailItem" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document" style="width: 100vw; max-width: none; height: 100vh; margin: 0;">
        <div class="modal-content" style="height: 100vh; border: 0; border-radius: 0;">
            <!-- Compact Header -->
            <div class="modal-header text-white" style="background-color: #1a548a; border: none; padding: 0.75rem 1.5rem; position: relative;">
                <div class="d-flex align-items-center">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="mr-2" style="height: 48px; width: auto;">
                    <h5 class="modal-title mb-0" style="font-weight: 600; font-size: 1.5rem;">
                        Service Part System
                    </h5>
                </div>
                <div style="position: absolute; left: 50%; top: 25%; transform: translateX(-50%); text-align: center;">
                    <small class="text-light" style="font-size: 1.5rem; font-weight: 600; margin-top: 6px; color: #fff;">
                        <span id="modal_part_no"></span>
                    </small>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" style="opacity: 1; padding: 1.5rem;">
                    <span style="font-size: 1.25rem;">&times;</span>
                </button>
            </div>
            
            <!-- Compact Body -->
            <div class="modal-body" style="padding: 1rem; overflow-y: auto; height: calc(100vh - 70px);">
                <div class="row h-100">
                    <!-- Left Column - Images -->
                    <div class="col-lg-6 d-flex flex-column" style="height: calc(100vh - 90px);">
                        <div class="card shadow-sm flex-fill" style="overflow: hidden;">
                            <div class="card-body p-2 d-flex flex-column" style="height: 100%;">
                                <!-- Keypoint Image -->
                                <div id="keypoint-container" style="height: 70%; min-height: 0; display: flex; flex-direction: column; margin-bottom: 0.5rem;">
                                    <!-- Keypoint image will be loaded here -->
                                </div>
                                <!-- Warna Plastik Image -->
                                <div id="warna_plastik-container" style="height: 30%; min-height: 0; display: flex; flex-direction: column;">
                                    <!-- Warna plastik image will be loaded here -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column - Info & Scan -->
                    <div class="col-lg-6 d-flex flex-column" style="height: calc(100vh - 90px);">
                        <!-- Informasi Item -->
                        <div class="card shadow-sm mb-2" style="flex: 0 0 auto;">
                            <div class="card-body p-2">
                                <table class="table table-borderless table-sm mb-0" style="font-size: 1.2rem;">
                                    <tr>
                                        <td class="text-dark py-1" style="width: 40%;"><strong>Part No:</strong></td>
                                        <td class="py-1" id="detail_part_no">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-dark py-1"><strong>Part Name:</strong></td>
                                        <td class="py-1" id="detail_part_name">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-dark py-1"><strong>Size Plastik:</strong></td>
                                        <td class="py-1" id="detail_size_plastik">-</td>
                                    </tr>
                                    <tr>
                                        <td class="text-dark py-1"><strong>Part Color:</strong></td>
                                        <td class="py-1" id="detail_part_color">-</td>
                                    </tr>
                                    <tr class="border-top">
                                        <td class="text-dark py-1"><strong>Qty Order:</strong></td>
                                        <td class="py-1"><span id="detail_qty_order" style="font-size: 1.2rem;">0</span></td>
                                    </tr>
                                    <tr id="transaction_detail_row" style="display: none;">
                                        <td class="text-dark py-1"><strong>Qty Pulling:</strong></td>
                                        <td class="py-1"><span id="detail_qty_pulling_transaction" style="font-size: 1.2rem;">0</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-dark py-1"><strong>Qty ISP:</strong></td>
                                        <td class="py-1"><span class="badge badge-warning badge-sm" id="detail_qty_isp" style="font-size: 1.2rem;">0</span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Scan Item -->
                        <div class="card shadow-sm flex-fill">
                            <div class="card-header bg-light py-1 px-2">
                                <h6 class="mb-0" style="font-size: 0.85rem;"><i class="fa fa-qrcode mr-1"></i>Scan Item</h6>
                            </div>
                            <div class="card-body p-2">
                                <div class="form-group mb-2">
                                    <label for="scan_input" style="font-size: 0.8rem;"><strong>Scan QR/Part No:</strong></label>
                                    <input type="text" class="form-control" id="scan_input" 
                                           placeholder="Scan atau ketik QR/Part No..." 
                                           autocomplete="off"
                                           style="font-size: 0.9rem; padding: 0.5rem;">
                                </div>
                                <div class="mt-2">
                                    <button type="button" class="btn btn-block btn-submit" id="btn_submit" disabled style="font-size: 0.9rem; padding: 0.6rem; background-color: #1a548a; border-color: #1a548a; color: #fff;">
                                        <i class="fa fa-check mr-1"></i> Submit 
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>