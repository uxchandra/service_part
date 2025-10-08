<!-- Modal Tambah Barang -->
<div class="modal fade" id="modal_tambah_barang" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa fa-plus"></i> Tambah Data Barang
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_tambah_barang">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="qr_label">QR Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="qr_label" name="qr_label" placeholder="Masukkan QR Label" required>
                                <div class="invalid-feedback d-none" id="error_qr_label"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="part_no">Part No <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="part_no" name="part_no" placeholder="Masukkan Part No" required>
                                <div class="invalid-feedback d-none" id="error_part_no"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer">Customer</label>
                                <input type="text" class="form-control" id="customer" name="customer" placeholder="Masukkan Customer">
                                <div class="invalid-feedback d-none" id="error_customer"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="part_name">Part Name</label>
                                <input type="text" class="form-control" id="part_name" name="part_name" placeholder="Masukkan Part Name">
                                <div class="invalid-feedback d-none" id="error_part_name"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="size_plastic">Size Plastic</label>
                                <input type="text" class="form-control" id="size_plastic" name="size_plastic" placeholder="Masukkan Size Plastic">
                                <div class="invalid-feedback d-none" id="error_size_plastic"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="part_color">Part Color</label>
                                <input type="text" class="form-control" id="part_color" name="part_color" placeholder="Masukkan Part Color">
                                <div class="invalid-feedback d-none" id="error_part_color"></div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="stok">Stok</label>
                                <input type="number" class="form-control" id="stok" name="stok" placeholder="Masukkan Stok" min="0" value="0">
                                <div class="invalid-feedback d-none" id="error_stok"></div>
                                <small class="form-text text-muted">Kosongkan jika tidak ada stok</small>
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
                    <button type="submit" class="btn btn-primary" id="btn_simpan">
                        <i class="fa fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>