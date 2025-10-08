<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('barang.index');
    }

    /**
     * Get data for DataTables
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = Barang::query();

            // Search
            if ($request->has('search') && $request->search['value'] != '') {
                $search = $request->search['value'];
                $query->where(function($q) use ($search) {
                    $q->where('qr_label', 'like', "%{$search}%")
                    ->orWhere('part_no', 'like', "%{$search}%")
                    ->orWhere('customer', 'like', "%{$search}%")
                    ->orWhere('part_name', 'like', "%{$search}%")
                    ->orWhere('size_plastic', 'like', "%{$search}%")
                    ->orWhere('part_color', 'like', "%{$search}%")
                    ->orWhere('stok', 'like', "%{$search}%");
                });
            }

            // Total records
            $totalRecords = Barang::count();
            $totalFiltered = $query->count();

            // Ordering
            if ($request->has('order')) {
                $columns = ['id', 'qr_label', 'part_no', 'customer', 'part_name', 'size_plastic', 'part_color', 'stok'];
                $orderColumn = $columns[$request->order[0]['column']] ?? 'id';
                $orderDir = $request->order[0]['dir'] ?? 'desc';
                $query->orderBy($orderColumn, $orderDir);
            } else {
                $query->orderBy('id', 'desc');
            }

            // Pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            
            if ($length != -1) {
                $query->offset($start)->limit($length);
            }

            $data = $query->get()->map(function($row) {
                return [
                    'id' => $row->id,
                    'barcode_html' => '<div style="text-align: center"><strong>' . $row->qr_label . '</strong></div>',
                    'qr_label' => $row->qr_label,
                    'part_no' => $row->part_no,
                    'customer' => $row->customer ?? '-',
                    'part_name' => $row->part_name ?? '-',
                    'size_plastic' => $row->size_plastic ?? '-',
                    'part_color' => $row->part_color ?? '-',
                    'stok' => $row->stok ?? '-',
                    'keypoint' => $row->keypoint, // Tambahkan ini
                    'keypoint_url' => $row->keypoint ? asset('images/' . $row->keypoint) : null, // Full URL gambar
                ];
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'qr_label' => 'required|string|max:255|unique:barang,qr_label',
            'part_no' => 'required|string|max:255',
            'customer' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'size_plastic' => 'nullable|string|max:255',
            'part_color' => 'nullable|string|max:255',
            'stok' => 'nullable|integer|min:0',
            'keypoint' => 'nullable|string|max:255', // Tambahkan validasi keypoint
        ], [
            'qr_label.required' => 'QR Label wajib diisi',
            'qr_label.unique' => 'QR Label sudah digunakan',
            'part_no.required' => 'Part No wajib diisi',
            'stok.integer' => 'Stok harus berupa angka',
            'stok.min' => 'Stok tidak boleh negatif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Validasi file keypoint exists jika ada
            $data = $request->all();
            
            if (!empty($data['keypoint'])) {
                $keypointPath = public_path('images/' . $data['keypoint']);
                if (!file_exists($keypointPath)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'File keypoint tidak ditemukan: ' . $data['keypoint']
                    ], 422);
                }
            }

            $barang = Barang::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Data barang berhasil ditambahkan',
                'data' => $barang
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $barang = Barang::findOrFail($id);
            
            // Tambahkan URL keypoint
            $barang->keypoint_url = $barang->keypoint ? asset('images/' . $barang->keypoint) : null;
            
            return response()->json([
                'status' => 'success',
                'data' => $barang
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'qr_label' => 'required|string|max:255|unique:barang,qr_label,' . $id,
            'part_no' => 'required|string|max:255',
            'customer' => 'nullable|string|max:255',
            'part_name' => 'nullable|string|max:255',
            'size_plastic' => 'nullable|string|max:255',
            'part_color' => 'nullable|string|max:255',
            'stok' => 'nullable|integer|min:0',
            'keypoint' => 'nullable|string|max:255', // Tambahkan validasi keypoint
        ], [
            'qr_label.required' => 'QR Label wajib diisi',
            'qr_label.unique' => 'QR Label sudah digunakan',
            'part_no.required' => 'Part No wajib diisi',
            'stok.integer' => 'Stok harus berupa angka',
            'stok.min' => 'Stok tidak boleh negatif',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $barang = Barang::findOrFail($id);
            
            $data = $request->all();
            
            // Validasi file keypoint exists jika ada dan berubah
            if (!empty($data['keypoint']) && $data['keypoint'] !== $barang->keypoint) {
                $keypointPath = public_path('images/' . $data['keypoint']);
                if (!file_exists($keypointPath)) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'File keypoint tidak ditemukan: ' . $data['keypoint']
                    ], 422);
                }
            }
            
            $barang->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Data barang berhasil diupdate',
                'data' => $barang
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $barang = Barang::findOrFail($id);
            $barang->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Data barang berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export data to Excel
     */
    public function export()
    {
        try {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\BarangExport(), 
                'data_barang_' . date('YmdHis') . '.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Export gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download template Excel untuk import
     */
    public function downloadTemplate()
    {
        try {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\BarangTemplateExport(), 
                'template_import_barang.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Download template gagal: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import data from Excel
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls,csv|max:5120',
        ], [
            'file.required' => 'File wajib diupload',
            'file.mimes' => 'File harus berformat Excel (.xlsx, .xls) atau CSV',
            'file.max' => 'Ukuran file maksimal 5MB'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()->all()
            ], 422);
        }

        try {
            $file = $request->file('file');
            
            $import = new \App\Imports\BarangImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $file);

            $failures = $import->failures();
            $errors = $import->errors();

            if (count($failures) > 0 || count($errors) > 0) {
                $errorMessages = [];
                
                foreach ($failures as $failure) {
                    $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
                }
                
                foreach ($errors as $error) {
                    $errorMessages[] = $error->getMessage();
                }

                return response()->json([
                    'success' => false,
                    'message' => 'Import selesai dengan beberapa error',
                    'errors' => $errorMessages
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data berhasil diimport'
            ]);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMessages = [];
            
            foreach ($failures as $failure) {
                $errorMessages[] = "Baris {$failure->row()}: " . implode(', ', $failure->errors());
            }

            return response()->json([
                'success' => false,
                'message' => 'Validasi data gagal',
                'errors' => $errorMessages
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import gagal: ' . $e->getMessage(),
                'errors' => [$e->getMessage()]
            ], 500);
        }
    }

    /**
     * Print selected items
     */
    public function printSelected(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            $barang = Barang::whereIn('id', $ids)->get();
            
            return response()->json([
                'status' => 'success',
                'data' => $barang
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Print gagal: ' . $e->getMessage()
            ], 500);
        }
    }
}