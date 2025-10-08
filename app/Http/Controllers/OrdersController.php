<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\BarangKeluar;

class OrdersController extends Controller
{
    /**
     * Tampilkan halaman index orders
     */
    public function index()
    {
        return view('orders.index');
    }

    /**
     * Endpoint JSON untuk DataTables berdasarkan model Order/OrderItem
     */

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            // Flatten: satu baris per OrderItem
            $query = Order::query()
                ->join('order_items', 'orders.id', '=', 'order_items.order_id')
                ->select([
                    'orders.id as order_id',
                    'orders.no_transaksi',
                    'orders.delivery_date',
                    'orders.status',
                    'order_items.id as item_id',
                    'order_items.part_no',
                    'order_items.quantity',
                ]);

            // Pencarian
            if ($request->has('search') && $request->search['value'] != '') {
                $search = $request->search['value'];
                $query->where(function($q) use ($search) {
                    $q->where('orders.no_transaksi', 'like', "%{$search}%")
                    ->orWhere('orders.status', 'like', "%{$search}%")
                    ->orWhere('order_items.part_no', 'like', "%{$search}%")
                    ->orWhereDate('orders.delivery_date', $search);
                });
            }

            $totalRecords = Order::join('order_items', 'orders.id', '=', 'order_items.order_id')->count();
            $totalFiltered = (clone $query)->count();

            // Ordering
            if ($request->has('order')) {
                $columns = ['orders.no_transaksi', 'order_items.part_no', 'order_items.quantity', 'orders.delivery_date', 'orders.status'];
                $orderColumn = $columns[$request->order[0]['column']] ?? 'orders.id';
                $orderDir = $request->order[0]['dir'] ?? 'desc';
                $query->orderBy($orderColumn, $orderDir);
            } else {
                $query->orderBy('orders.id', 'desc')->orderBy('order_items.id', 'asc');
            }

            // Pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            if ($length != -1) {
                $query->offset($start)->limit($length);
            }

            $rows = $query->get();

            // Tandai baris pertama tiap grup order_id dalam halaman ini
            $data = [];
            $seenOrderIds = [];
            foreach ($rows as $row) {
                $isFirst = !in_array($row->order_id, $seenOrderIds, true);
                if ($isFirst) {
                    $seenOrderIds[] = $row->order_id;
                }
                
                // Format status display dengan badge
                $statusBadge = '';
                switch ($row->status) {
                    case 'planning':
                        $statusBadge = '<span class="badge badge-primary">Planning</span>';
                        break;
                    case 'partial':
                        $statusBadge = '<span class="badge badge-warning">Partial</span>';
                        break;
                    case 'pulling':
                        $statusBadge = '<span class="badge badge-success">Pulling</span>';
                        break;
                    default:
                        $statusBadge = '<span class="badge badge-light">' . ucfirst($row->status) . '</span>';
                }
                
                $data[] = [
                    'order_id' => $row->order_id,
                    'no_transaksi_display' => $isFirst ? $row->no_transaksi : '',
                    'part_no' => $row->part_no,
                    'qty' => (int) $row->quantity,
                    'delivery_date' => optional($row->delivery_date)->format('d/m/Y'),
                    'delivery_date_display' => $isFirst ? optional($row->delivery_date)->format('d/m/Y') : '',
                    'status' => $row->status, // Field status mentah untuk logic di JavaScript
                    'status_display' => $isFirst ? $statusBadge : '', // Badge HTML untuk tampilan
                    'is_group_start' => $isFirst,
                    'actions' => '',
                ];
            }

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalFiltered,
                'data' => $data
            ]);
        }
    }

    /**
     * Import Orders dari Excel
     */
    public function import(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'import_file' => 'required|mimes:xlsx,xls|max:5120',
        ], [
            'import_file.required' => 'File wajib diupload',
            'import_file.mimes' => 'Format harus .xlsx atau .xls',
            'import_file.max' => 'Ukuran file maksimal 5MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()->all(),
            ], 422);
        }

        try {
            $file = $request->file('import_file');
            \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\OrdersImport(), $file);

            return response()->json([
                'success' => true,
                'message' => 'Import orders berhasil',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import gagal: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download template Excel
     */
    public function downloadTemplate()
    {
        try {
            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\OrdersTemplateExport(),
                'template_import_orders.xlsx'
            );
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal download template: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $order = Order::findOrFail($id);

            // Cek apakah order sudah memiliki barang keluar (sudah di-pulling)
            $barangKeluar = BarangKeluar::where('order_id', $order->id)
                ->whereNotNull('tanggal_keluar') // Yang sudah submit (bukan draft)
                ->first();

            if ($barangKeluar) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order tidak dapat dihapus karena sudah di-pulling'
                ], 422);
            }

            // Hapus draft barang keluar jika ada (yang belum submit)
            $draftBarangKeluar = BarangKeluar::where('order_id', $order->id)
                ->whereNull('tanggal_keluar')
                ->first();

            if ($draftBarangKeluar) {
                // Hapus items dulu
                $draftBarangKeluar->items()->delete();
                // Hapus barang keluar
                $draftBarangKeluar->delete();
            }

            // Hapus order items
            $order->orderItems()->delete();

            // Hapus order
            $order->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Order berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus order: ' . $e->getMessage()
            ], 500);
        }
    }

}


