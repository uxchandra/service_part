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
            // Query untuk mendapatkan 1 baris per transaksi dengan jumlah item
            $query = Order::query()
                ->leftJoin('order_items', 'orders.id', '=', 'order_items.order_id')
                ->select([
                    'orders.id as order_id',
                    'orders.no_transaksi',
                    'orders.delivery_date',
                    'orders.status',
                    DB::raw('COUNT(order_items.id) as order_items_count')
                ])
                ->groupBy('orders.id', 'orders.no_transaksi', 'orders.delivery_date', 'orders.status');

            // Pencarian
            if ($request->has('search') && $request->search['value'] != '') {
                $search = $request->search['value'];
                $query->where(function($q) use ($search) {
                    $q->where('orders.no_transaksi', 'like', "%{$search}%")
                    ->orWhere('orders.status', 'like', "%{$search}%")
                    ->orWhereDate('orders.delivery_date', $search);
                });
            }

            $totalRecords = Order::count();
            $totalFiltered = (clone $query)->count();

            // Ordering
            if ($request->has('order')) {
                $columns = ['orders.no_transaksi', 'orders.delivery_date', 'orders.status'];
                $orderColumn = $columns[$request->order[0]['column']] ?? 'orders.id';
                $orderDir = $request->order[0]['dir'] ?? 'desc';
                $query->orderBy($orderColumn, $orderDir);
            } else {
                $query->orderBy('orders.id', 'desc');
            }

            // Pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            if ($length != -1) {
                $query->offset($start)->limit($length);
            }

            $rows = $query->get();

            $data = [];
            foreach ($rows as $row) {
                // Determine effective status (consider delay/completed)
                $effectiveStatus = $this->getEffectiveStatusForOrderId($row->order_id, $row->status);

                // Format status display dengan badge
                $statusBadge = '';
                switch ($effectiveStatus) {
                    case 'planning':
                        $statusBadge = '<span class="badge badge-primary">Planning</span>';
                        break;
                    case 'partial':
                        $statusBadge = '<span class="badge badge-warning">Partial</span>';
                        break;
                    case 'pulling':
                        $statusBadge = '<span class="badge badge-info">Pulling</span>';
                        break;
                    case 'delay':
                        $statusBadge = '<span class="badge badge-danger">Delay</span>';
                        break;
                    case 'completed':
                        $statusBadge = '<span class="badge badge-success">Completed</span>';
                        break;
                    default:
                        $statusBadge = '<span class="badge badge-light">' . ucfirst($effectiveStatus) . '</span>';
                }
                
                // Calculate progress
                $progressDisplay = $this->calculateProgressDisplay($row->order_id, $row->status);
                
                $data[] = [
                    'order_id' => $row->order_id,
                    'no_transaksi_display' => $row->no_transaksi,
                    'jumlah_item' => $row->order_items_count,
                    'delivery_date_display' => optional($row->delivery_date)->format('d/m/Y'),
                    'status' => $effectiveStatus,
                    'status_display' => $statusBadge,
                    'progress_display' => $progressDisplay,
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
        $validator = Validator::make($request->all(), [
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

    private function calculateProgressDisplay($orderId, $status)
    {
        try {
            // Get order with all related data
            $order = Order::with(['orderItems', 'barangKeluar.items', 'barangKeluar.ispPacking.items'])
                ->find($orderId);
            
            if (!$order) {
                return '<span class="text-muted">-</span>';
            }
            
            // Calculate total qty order
            $totalQtyOrder = $order->orderItems->sum('quantity');
            
            // Calculate total qty pulling
            $totalQtyPulling = $order->barangKeluar->sum(function($bk) {
                return $bk->items->sum('quantity');
            });
            
            // Calculate total qty ISP
            $totalQtyIsp = 0;
            foreach ($order->barangKeluar as $bk) {
                if ($bk->ispPacking) {
                    $totalQtyIsp += $bk->ispPacking->items->sum('qty_isp');
                }
            }
            
            // Format progress as text
            $progressText = "Pulling: {$totalQtyPulling}/{$totalQtyOrder}<br>";
            $progressText .= "Packing: {$totalQtyIsp}/{$totalQtyOrder}";
            
            return $progressText;
            
        } catch (\Exception $e) {
            return '<span class="text-muted">-</span>';
        }
    }

    private function getEffectiveStatusForOrderId(int $orderId, string $currentStatus): string
    {
        try {
            $order = Order::with(['orderItems', 'barangKeluar.ispPacking.items'])
                ->find($orderId);
            if (!$order) {
                return $currentStatus;
            }

            $totalQtyOrder = (int) $order->orderItems->sum('quantity');
            $totalQtyPacking = 0;
            foreach ($order->barangKeluar as $bk) {
                if ($bk->ispPacking) {
                    $totalQtyPacking += (int) $bk->ispPacking->items->sum('qty_isp');
                }
            }

            // Completed always wins
            if ($currentStatus === 'completed' || ($totalQtyOrder > 0 && $totalQtyPacking >= $totalQtyOrder)) {
                return 'completed';
            }

            // Delay if overdue and not completed
            if ($order->delivery_date) {
                $today = now()->startOfDay();
                $due = \Carbon\Carbon::parse($order->delivery_date)->startOfDay();
                if ($today->gt($due)) {
                    return 'delay';
                }
            }

            return $currentStatus;
        } catch (\Exception $e) {
            return $currentStatus;
        }
    }

    public function show($id)
    {
        try {
            $order = Order::with(['orderItems.barang', 'barangKeluar.items.barang', 'barangKeluar.ispPacking.items.barang'])
                ->findOrFail($id);
            // Compute effective status for detail modal consistency
            $effectiveStatus = $this->getEffectiveStatusForOrderId($order->id, $order->status);
            $order->setAttribute('effective_status', $effectiveStatus);
            
            return response()->json([
                'success' => true,
                'data' => $order
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data order: ' . $e->getMessage()
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

    /**
     * Public Andon view (no auth)
     */
    public function andon()
    {
        return view('andon');
    }

    /**
     * Public Andon data (no auth)
     */
    public function andonData(Request $request)
    {
        // Fetch latest orders with relations needed to compute per-part totals and stok
        $orders = Order::query()
            ->with([
                'orderItems.barang',
                'barangKeluar.items.barang',
                'barangKeluar.ispPacking.items.barang',
            ])
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        $rows = [];
        foreach ($orders as $order) {
            // Build map per part_no from order items
            $map = [];
            foreach ($order->orderItems as $oi) {
                $partNo = $oi->part_no;
                if (!isset($map[$partNo])) {
                    $map[$partNo] = [
                        'no_transaksi' => $order->no_transaksi,
                        'part_no' => $partNo,
                        'qty_order' => 0,
                        'qty_pulling' => 0,
                        'qty_packing' => 0,
                        'stok' => optional($oi->barang)->stok ?? 0,
                        'delivery_date' => optional($order->delivery_date)->format('d/m/Y'),
                        'status' => $order->status,
                    ];
                }
                $map[$partNo]['qty_order'] += (int) $oi->quantity;
                // if stok missing on this item but available on barang, set it
                if (($map[$partNo]['stok'] === 0 || $map[$partNo]['stok'] === null) && $oi->barang) {
                    $map[$partNo]['stok'] = (int) $oi->barang->stok;
                }
            }

            // Sum pulling per part_no
            foreach ($order->barangKeluar as $bk) {
                foreach ($bk->items as $it) {
                    $partNo = optional($it->barang)->part_no;
                    if ($partNo && isset($map[$partNo])) {
                        $map[$partNo]['qty_pulling'] += (int) $it->quantity;
                    }
                }
                // Sum packing per part_no
                if ($bk->ispPacking) {
                    foreach ($bk->ispPacking->items as $pit) {
                        $partNo = optional($pit->barang)->part_no;
                        if ($partNo && isset($map[$partNo])) {
                            $map[$partNo]['qty_packing'] += (int) $pit->qty_isp;
                        }
                    }
                }
            }

            // Append rows
            // Compute effective status once per order
            $totalQtyOrder = (int) array_sum(array_column($map, 'qty_order'));
            $totalQtyPacking = 0;
            foreach ($order->barangKeluar as $bk) {
                if ($bk->ispPacking) {
                    $totalQtyPacking += (int) $bk->ispPacking->items->sum('qty_isp');
                }
            }
            $effectiveStatus = $order->status;
            if ($effectiveStatus !== 'completed' && $totalQtyOrder > 0 && $totalQtyPacking >= $totalQtyOrder) {
                $effectiveStatus = 'completed';
            } else if ($order->delivery_date) {
                $today = now()->startOfDay();
                $due = \Carbon\Carbon::parse($order->delivery_date)->startOfDay();
                if ($today->gt($due) && $effectiveStatus !== 'completed') {
                    $effectiveStatus = 'delay';
                }
            }

            foreach ($map as $row) {
                $row['status'] = $effectiveStatus;
                $rows[] = $row;
            }
        }

        return response()->json(['data' => $rows]);
    }

}
