<!-- Modal Edit Barang -->
<div class="modal fade" id="modal_edit_barang" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title">
                    <i class="fa fa-edit"></i> Edit Data Barang
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_edit_barang">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_id" name="id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_qr_label">QR Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_qr_label" name="qr_label" placeholder="Masukkan QR Label" required>
                                <div class="invalid-feedback d-none" id="error_edit_qr_label"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_part_no">Part No <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_part_no" name="part_no" placeholder="Masukkan Part No" required>
                                <div class="invalid-feedback d-none" id="error_edit_part_no"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_customer">Customer</label>
                                <input type="text" class="form-control" id="edit_customer" name="customer" placeholder="Masukkan Customer">
                                <div class="invalid-feedback d-none" id="error_edit_customer"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_part_name">Part Name</label>
                                <input type="text" class="form-control" id="edit_part_name" name="part_name" placeholder="Masukkan Part Name">
                                <div class="invalid-feedback d-none" id="error_edit_part_name"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_size_plastic">Size Plastic</label>
                                <input type="text" class="form-control" id="edit_size_plastic" name="size_plastic" placeholder="Masukkan Size Plastic">
                                <div class="invalid-feedback d-none" id="error_edit_size_plastic"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_part_color">Part Color</label>
                                <input type="text" class="form-control" id="edit_part_color" name="part_color" placeholder="Masukkan Part Color">
                                <div class="invalid-feedback d-none" id="error_edit_part_color"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_stok">Stok</label>
                                <input type="number" class="form-control" id="edit_stok" name="stok" placeholder="Masukkan Stok" min="0">
                                <div class="invalid-feedback d-none" id="error_edit_stok"></div>
                                <small class="form-text text-muted">Kosongkan jika tidak ada stok</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_keypoint">Keypoint</label>
                                <input type="text" class="form-control" id="edit_keypoint" name="keypoint" placeholder="Contoh: keypoint/AR.ADL.0001.jpeg">
                                <div class="invalid-feedback d-none" id="error_edit_keypoint"></div>
                                <small class="form-text text-muted">Path relatif ke file gambar (contoh: keypoint/AR.ADL.0001.jpeg)</small>
                                <div class="mt-2" id="edit_keypoint_preview" style="display: none;">
                                    <img id="edit_keypoint_img" src="" alt="Keypoint Preview" style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px; border: 2px solid #e3e6f0;">
                                    <div class="mt-1">
                                        <small class="text-muted">Current: <span id="edit_keypoint_path">-</span></small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="edit_warna_plastik">Warna Plastik</label>
                                <input type="text" class="form-control" id="edit_warna_plastik" name="warna_plastik" placeholder="Contoh: keypoint/300x200.JPG">
                                <div class="invalid-feedback d-none" id="error_edit_warna_plastik"></div>
                                <small class="form-text text-muted">Path relatif ke file gambar (contoh: keypoint/300x200.JPG)</small>
                                <div class="mt-2" id="edit_warna_plastik_preview" style="display: none;">
                                    <img id="edit_warna_plastik_img" src="" alt="Warna Plastik Preview" style="width: 100px; height: 100px; object-fit: cover; border-radius: 4px; border: 2px solid #e3e6f0;">
                                    <div class="mt-1">
                                        <small class="text-muted">Current: <span id="edit_warna_plastik_path">-</span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mt-2">
                        <small><i class="fa fa-info-circle"></i> Field dengan tanda <span class="text-danger">*</span> wajib diisi</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-warning text-white" id="btn_update">
                        <i class="fa fa-save"></i> Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>