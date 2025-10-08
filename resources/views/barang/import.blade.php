<!-- Modal Import -->
<div class="modal fade" id="modal_import_barang" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Import Data Barang</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="form_import_barang" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i> 
                        <strong>Petunjuk:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Download template Excel terlebih dahulu</li>
                            <li>Isi data sesuai format template</li>
                            <li>Upload file Excel yang telah diisi</li>
                            <li>Format file: .xlsx, .xls, atau .csv</li>
                            <li>Maksimal ukuran file: 5MB</li>
                        </ul>
                    </div>

                    <div class="form-group">
                        <label>Download Template</label>
                        <div>
                            <a href="{{ route('barang.download-template') }}" class="btn btn-success btn-sm">
                                <i class="fa fa-download"></i> Download Template Excel
                            </a>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="import_file">Upload File Excel <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="import_file" name="file" accept=".xlsx,.xls,.csv" required>
                        <small class="form-text text-muted">Format: .xlsx, .xls, .csv (Max: 5MB)</small>
                    </div>

                    <div id="import_progress" class="progress d-none" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%">0%</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btn_import">
                        <i class="fa fa-upload"></i> Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>