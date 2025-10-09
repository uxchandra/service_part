<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Order;
use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\BarangKeluarItem;

class PullingController extends Controller
{
    public function index()
    {
        return view('pulling.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            // Get orders that have pulling data
            $query = Order::with(['orderItems', 'barangKeluar.items'])
                ->whereIn('status', ['planning', 'partial', 'pulling', 'completed'])
                ->whereHas('barangKeluar', function($q) {
                    $q->whereNotNull('tanggal_keluar');
                });

            $totalRecords = $query->count();

            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            if ($length != -1) {
                $query->offset($start)->limit($length);
            }

            $data = $query->orderBy('updated_at', 'desc')->get()->map(function ($order) {
                // Calculate total qty order
                $totalQtyOrder = $order->orderItems->sum('quantity');
                
                // Calculate total qty pulling
                $totalQtyPulling = $order->barangKeluar->sum(function($bk) {
                    return $bk->items->sum('quantity');
                });

                // Calculate progress percentage
                $progressPercentage = $totalQtyOrder > 0 ? round(($totalQtyPulling / $totalQtyOrder) * 100, 1) : 0;

                // Determine pulling status
                $pullingStatus = 'not_started';
                if ($totalQtyPulling > 0) {
                    if ($progressPercentage >= 100) {
                        $pullingStatus = 'completed';
                    } else {
                        $pullingStatus = 'partial';
                    }
                }

                return [
                    'id' => $order->id,
                    'no_transaksi' => $order->no_transaksi,
                    'delivery_date' => optional($order->delivery_date)->format('d/m/Y'),
                    'transactions_count' => $order->barangKeluar->count(),
                    'progress_percentage' => $progressPercentage,
                    'pulling_status' => $pullingStatus,
                    'total_qty_pulling' => $totalQtyPulling,
                    'total_qty_order' => $totalQtyOrder,
                    'updated_at' => $order->updated_at->format('d/m/Y H:i'),
                ];
            });

            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => $totalRecords,
                'recordsFiltered' => $totalRecords,
                'data' => $data,
            ]);
        }
    }

    public function detail($id)
    {
        try {
            // $id sekarang adalah order_id, bukan barang_keluar_id
            $orderId = $id;
            
            // Get all completed transactions for this order (sorted by latest first)
            $transactions = BarangKeluar::with(['items.barang', 'user'])
                ->where('order_id', $orderId)
                ->whereNotNull('tanggal_keluar')
                ->orderBy('tanggal_keluar', 'desc')
                ->get();
            
            $transactionsData = $transactions->map(function($transaction, $index) {
                $items = $transaction->items->map(function($item) {
                    return [
                        'part_no' => $item->barang->part_no ?? '-',
                        'part_name' => $item->barang->part_name ?? '-',
                        'quantity' => $item->quantity,
                    ];
                });
                
                return [
                    'transaction_number' => $index + 1,
                    'time' => $transaction->tanggal_keluar->format('d F y, H:i'),
                    'user_name' => $transaction->user->name ?? '-',
                    'items_count' => $transaction->items->count(),
                    'total_quantity' => $transaction->items->sum('quantity'),
                    'items' => $items,
                ];
            });

            return response()->json([
                'success' => true,
                'transactions' => $transactionsData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail: ' . $e->getMessage()
            ], 500);
        }
    }

    public function create(Request $request)
    {
        $orderId = $request->query('order_id');
        $order = Order::with('orderItems')->findOrFail($orderId);

        // Cek apakah order sudah completed (status = 'pulling')
        if ($order->status === 'pulling') {
            return redirect()->route('orders.index')->with('error', 'Order sudah selesai di-pulling');
        }

        // Hapus draft lama jika ada (reset)
        $existingDraft = BarangKeluar::where('order_id', $order->id)
            ->whereNull('tanggal_keluar')
            ->first();
        
        if ($existingDraft) {
            // Hapus items dulu
            $existingDraft->items()->delete();
            // Hapus barang keluar
            $existingDraft->delete();
        }

        // Buat barang_keluar baru yang kosong
        $barangKeluar = BarangKeluar::create([
            'order_id' => $order->id,
            'tanggal_keluar' => null, // null untuk draft
            'user_id' => auth()->id()
        ]);

        $scannedItems = collect(); // Kosong karena baru dibuat

        // Ambil data qty yang sudah di-scan sebelumnya (untuk partial orders)
        $previousScanned = [];
        if ($order->status === 'partial') {
            $previousTransactions = BarangKeluar::with(['items.barang'])
                ->where('order_id', $order->id)
                ->whereNotNull('tanggal_keluar')
                ->get();
            
            foreach ($previousTransactions as $transaction) {
                foreach ($transaction->items as $item) {
                    $partNo = $item->barang->part_no;
                    if (!isset($previousScanned[$partNo])) {
                        $previousScanned[$partNo] = 0;
                    }
                    $previousScanned[$partNo] += $item->quantity;
                }
            }
        }

        return view('pulling.create', compact('order', 'barangKeluar', 'scannedItems', 'previousScanned'));
    }

    public function scan(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'code' => 'required|string',
        ]);

        try {
            $order = Order::with('orderItems')->findOrFail($request->order_id);

            // Cari barang berdasarkan qr_label atau part_no
            $code = trim($request->code);
            $barang = Barang::where('qr_label', $code)
                ->orWhere('part_no', $code)
                ->first();

            if (!$barang) {
                return response()->json(['success' => false, 'message' => 'Barang tidak ditemukan'], 404);
            }

            // Validasi part_no ada di order
            $existsInOrder = $order->orderItems->contains(function ($item) use ($barang) {
                return $item->part_no === $barang->part_no;
            });
            if (!$existsInOrder) {
                return response()->json(['success' => false, 'message' => 'Part tidak ada pada order'], 422);
            }

            DB::beginTransaction();

            $barangKeluar = BarangKeluar::where('order_id', $order->id)
                ->whereNull('tanggal_keluar')
                ->firstOrFail();

            // Cek apakah barang sudah pernah di-scan
            $existingItem = BarangKeluarItem::where('barang_keluar_id', $barangKeluar->id)
                ->where('barang_id', $barang->id)
                ->first();

            if ($existingItem) {
                // Jika sudah ada, tambahkan qty +1
                $existingItem->quantity += 1;
                $existingItem->save();

                DB::commit();

                // Return data item yang diupdate
                $scannedItem = [
                    'id' => $existingItem->id,
                    'qr_label' => $barang->qr_label ?? '-',
                    'part_no' => $barang->part_no ?? '-',
                    'part_name' => $barang->part_name ?? '-',
                    'stok' => $barang->stok ?? 0,
                    'quantity' => $existingItem->quantity,
                    'is_update' => true,
                ];

                return response()->json([
                    'success' => true,
                    'message' => 'Qty berhasil ditambahkan',
                    'item' => $scannedItem,
                ]);
            }

            // Jika belum ada, tambahkan item baru dengan qty 1
            $item = BarangKeluarItem::create([
                'barang_keluar_id' => $barangKeluar->id,
                'barang_id' => $barang->id,
                'quantity' => 1,
            ]);

            DB::commit();

            // Return data item yang baru di-scan
            $scannedItem = [
                'id' => $item->id,
                'qr_label' => $barang->qr_label ?? '-',
                'part_no' => $barang->part_no ?? '-',
                'part_name' => $barang->part_name ?? '-',
                'stok' => $barang->stok ?? 0,
                'quantity' => 1,
                'is_update' => false,
            ];

            return response()->json([
                'success' => true,
                'message' => 'Scan berhasil',
                'item' => $scannedItem,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal scan: ' . $e->getMessage()], 500);
        }
    }

    public function submit(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'items' => 'required|array',
            'items.*.id' => 'required|exists:barang_keluar_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $order = Order::with('orderItems')->findOrFail($request->order_id);

            DB::beginTransaction();

            $isFullyFulfilled = true;
            $fulfilledParts = [];

            // Validasi qty vs order items
            foreach ($request->items as $itemData) {
                $item = BarangKeluarItem::with('barang')->findOrFail($itemData['id']);
                
                // Cari order item yang sesuai
                $orderItem = $order->orderItems->where('part_no', $item->barang->part_no)->first();
                if (!$orderItem) {
                    throw new \Exception("Part {$item->barang->part_no} tidak ada di order");
                }

                // PERUBAHAN: Qty harus kurang dari atau sama dengan qty order
                if ($itemData['quantity'] > $orderItem->quantity) {
                    throw new \Exception("Qty untuk {$item->barang->part_no} melebihi order (Order: {$orderItem->quantity}, Input: {$itemData['quantity']})");
                }

                // Cek apakah qty kurang dari order (partial)
                if ($itemData['quantity'] < $orderItem->quantity) {
                    $isFullyFulfilled = false;
                }

                // Cek stok tersedia
                if ($item->barang->stok < $itemData['quantity']) {
                    throw new \Exception("Stok {$item->barang->part_no} tidak mencukupi (Stok: {$item->barang->stok}, Butuh: {$itemData['quantity']})");
                }

                $fulfilledParts[] = $item->barang->part_no;
            }

            // Cek apakah semua part sudah di-scan
            $allOrderParts = $order->orderItems->pluck('part_no')->toArray();
            $missingParts = array_diff($allOrderParts, $fulfilledParts);
            
            if (!empty($missingParts)) {
                $isFullyFulfilled = false;
            }

            // Buat BarangKeluar record baru untuk transaksi ini
            $barangKeluar = BarangKeluar::create([
                'order_id' => $order->id,
                'tanggal_keluar' => now(),
                'user_id' => auth()->id()
            ]);

            // Update qty dan kurangi stok, lalu pindahkan ke record baru
            foreach ($request->items as $itemData) {
                $item = BarangKeluarItem::with('barang')->findOrFail($itemData['id']);
                
                // Update qty
                $item->quantity = $itemData['quantity'];
                $item->barang_keluar_id = $barangKeluar->id; // Pindahkan ke record baru
                $item->save();

                // Kurangi stok
                $barang = $item->barang;
                $barang->stok -= $itemData['quantity'];
                $barang->save();
            }

            // Hapus draft lama setelah semua items dipindahkan
            $existingDraft = BarangKeluar::where('order_id', $order->id)
                ->whereNull('tanggal_keluar')
                ->where('id', '!=', $barangKeluar->id) // Jangan hapus yang baru dibuat
                ->first();
            
            if ($existingDraft) {
                // Hapus items dulu
                $existingDraft->items()->delete();
                // Hapus barang keluar
                $existingDraft->delete();
            }

            // PERUBAHAN: Update status order berdasarkan fulfillment
            if ($isFullyFulfilled) {
                $order->status = 'pulling'; // Semua item lengkap
                $message = 'Pulling berhasil disubmit (Lengkap)';
            } else {
                $order->status = 'partial'; // Ada item yang kurang/tidak lengkap
                $message = 'Pulling berhasil disubmit (Partial)';
            }
            $order->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $order->status,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function deleteItem(Request $request, $id)
    {
        try {
            $item = BarangKeluarItem::findOrFail($id);
            $item->delete();

            return response()->json(['success' => true, 'message' => 'Item berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus item'], 500);
        }
    }
}