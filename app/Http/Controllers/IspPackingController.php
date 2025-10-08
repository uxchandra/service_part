<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\BarangKeluar;
use App\Models\IspPacking;
use App\Models\IspPackingItem;
use App\Models\Barang;
use App\Models\Order;

class IspPackingController extends Controller
{
    public function index()
    {
        return view('packing.index');
    }

    public function getData(Request $request)
    {
        if ($request->ajax()) {
            // Get orders that have completed pulling (status partial or pulling)
            $query = Order::with(['orderItems', 'barangKeluar.items'])
                ->whereIn('status', ['partial', 'pulling'])
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
                // Calculate total qty pulling
                $totalQtyPulling = $order->barangKeluar->sum(function($bk) {
                    return $bk->items->sum('quantity');
                });

                // Calculate total qty isp - cari isp packing melalui barang keluar
                $totalQtyIsp = 0;
                $ispPacking = IspPacking::whereHas('barangKeluar', function($q) use ($order) {
                    $q->where('order_id', $order->id);
                })->with('items')->first();
                
                if ($ispPacking) {
                    $totalQtyIsp = $ispPacking->items->sum('qty_isp');
                }

                // Calculate progress percentage
                $progressPercentage = $totalQtyPulling > 0 ? round(($totalQtyIsp / $totalQtyPulling) * 100, 1) : 0;

                // Determine packing status
                $packingStatus = 'not_started';
                $statusText = 'Belum Mulai';
                $statusClass = 'secondary';

                if ($ispPacking) {
                    if ($ispPacking->tanggal_isp) {
                        $packingStatus = 'completed';
                        $statusText = 'Selesai';
                        $statusClass = 'success';
                    } else if ($totalQtyIsp > 0) {
                        $packingStatus = 'in_progress';
                        $statusText = 'Sedang Proses';
                        $statusClass = 'warning';
                    } else {
                        $packingStatus = 'draft';
                        $statusText = 'Draft';
                        $statusClass = 'info';
                    }
                }

                return [
                    'id' => $order->id,
                    'no_transaksi' => $order->no_transaksi,
                    'delivery_date' => optional($order->delivery_date)->format('d/m/Y'),
                    'total_qty_pulling' => $totalQtyPulling,
                    'total_qty_isp' => $totalQtyIsp,
                    'progress_percentage' => $progressPercentage,
                    'packing_status' => $packingStatus,
                    'status_text' => $statusText,
                    'status_class' => $statusClass,
                    'transactions_count' => $order->barangKeluar->count(),
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

    public function create(Request $request)
    {
        $orderId = $request->query('order_id');
        
        if (!$orderId) {
            return redirect()->route('orders.index')->with('error', 'Order ID tidak ditemukan');
        }

        // Ambil order dengan semua barang keluar transactions
        $order = Order::with(['orderItems', 'barangKeluar.items.barang'])
            ->findOrFail($orderId);

        if ($order->barangKeluar->isEmpty()) {
            return redirect()->route('orders.index')->with('error', 'Tidak ada data pulling untuk order ini');
        }

        // Group items by barang keluar transaction (tidak ditotalkan)
        // Urutkan dari yang terbaru (latest first)
        $transactionsData = collect();
        foreach ($order->barangKeluar->sortByDesc('created_at') as $barangKeluar) {
            $transactionItems = collect();
            foreach ($barangKeluar->items as $bkItem) {
                $transactionItems->push([
                    'barang_id' => $bkItem->barang_id,
                    'barang' => $bkItem->barang,
                    'qty_pulling' => $bkItem->quantity,
                    'qty_order' => $order->orderItems->where('part_no', $bkItem->barang->part_no)->first()->quantity ?? 0,
                ]);
            }
            
            $transactionsData->push([
                'barang_keluar' => $barangKeluar,
                'items' => $transactionItems,
                'transaction_number' => $barangKeluar->id, // atau bisa pakai tanggal/format lain
                'tanggal' => $barangKeluar->tanggal_keluar
            ]);
        }

        // Cek apakah sudah ada isp packing untuk order ini
        $existingIspPacking = IspPacking::whereHas('barangKeluar', function($query) use ($orderId) {
            $query->where('order_id', $orderId);
        })->first();
            
            if ($existingIspPacking) {
                // Jika sudah ada, ambil data yang sudah ada
                $ispPacking = $existingIspPacking;
                $ispPackingItems = $ispPacking->items()->with('barang')->get();
            } else {
            // Jika belum ada, buat yang baru dengan barang keluar terbaru
            $latestBarangKeluar = $order->barangKeluar->first();
                $ispPacking = IspPacking::create([
                'barang_keluar_id' => $latestBarangKeluar->id,
                    'tanggal_isp' => null, // null untuk draft
                    'user_id' => auth()->id()
                ]);

            // Buat isp packing items untuk setiap barang di semua transaksi
                $ispPackingItems = collect();
            $processedBarangIds = collect();
            
            foreach ($transactionsData as $transaction) {
                foreach ($transaction['items'] as $item) {
                    // Cek apakah barang ini sudah diproses
                    if (!$processedBarangIds->contains($item['barang_id'])) {
                    $ispItem = IspPackingItem::create([
                        'isp_packing_id' => $ispPacking->id,
                        'barang_id' => $item['barang_id'],
                        'qty_isp' => 0
                    ]);
                    $ispItem->load('barang');
                    $ispPackingItems->push($ispItem);
                        $processedBarangIds->push($item['barang_id']);
                    }
                }
            }
        }

        return view('packing.create', compact('order', 'transactionsData', 'ispPacking', 'ispPackingItems'));
    }

    public function getItemDetail(Request $request)
    {
        $request->validate([
            'isp_packing_item_id' => 'required|exists:isp_packing_items,id',
            'transaction_index' => 'nullable|integer',
        ]);

        try {
            $ispPackingItem = IspPackingItem::with(['barang', 'ispPacking.barangKeluar.order'])
                ->findOrFail($request->isp_packing_item_id);

            $barang = $ispPackingItem->barang;
            $order = $ispPackingItem->ispPacking->barangKeluar->order;
            
            // Cari order item yang sesuai
            $orderItem = $order->orderItems->where('part_no', $barang->part_no)->first();
            
            // Hitung total qty pulling dari semua barang keluar untuk part ini
            $totalQtyPulling = $order->barangKeluar()
                ->whereHas('items', function($query) use ($barang) {
                    $query->where('barang_id', $barang->id);
                })
                ->with(['items' => function($query) use ($barang) {
                    $query->where('barang_id', $barang->id);
                }])
                ->get()
                ->sum(function($bk) use ($barang) {
                    return $bk->items->where('barang_id', $barang->id)->sum('quantity');
                });

            // Ambil detail per transaksi jika transaction_index diberikan
            $transactionDetails = collect();
            if ($request->has('transaction_index')) {
                // Gunakan urutan yang sama dengan create method (terbaru dulu)
                $transactionsData = collect();
                foreach ($order->barangKeluar->sortByDesc('created_at') as $barangKeluar) {
                    $transactionItems = collect();
                    foreach ($barangKeluar->items as $bkItem) {
                        $transactionItems->push([
                            'barang_id' => $bkItem->barang_id,
                            'barang' => $bkItem->barang,
                            'qty_pulling' => $bkItem->quantity,
                            'qty_order' => $order->orderItems->where('part_no', $bkItem->barang->part_no)->first()->quantity ?? 0,
                        ]);
                    }
                    
                    $transactionsData->push([
                        'barang_keluar' => $barangKeluar,
                        'items' => $transactionItems,
                        'transaction_number' => $barangKeluar->id,
                        'tanggal' => $barangKeluar->tanggal_keluar
                    ]);
                }
                
                $transactionIndex = $request->transaction_index;
                if (isset($transactionsData[$transactionIndex])) {
                    $transaction = $transactionsData[$transactionIndex];
                    $transactionItem = $transaction['items']->where('barang_id', $barang->id)->first();
                    if ($transactionItem) {
                        $transactionDetails = [
                            'transaction_number' => $transaction['transaction_number'],
                            'tanggal' => $transaction['tanggal'],
                            'qty_pulling_transaction' => $transactionItem['qty_pulling'],
                        ];
                    }
                }
            }

            // Tentukan batas scan berdasarkan transaksi atau total
            $scanLimit = $totalQtyPulling;
            $canScan = $ispPackingItem->qty_isp < $scanLimit;
            
            if ($request->has('transaction_index') && $transactionDetails && isset($transactionDetails['qty_pulling_transaction'])) {
                // Jika ada transaction_index, batasi berdasarkan qty transaksi tersebut
                $scanLimit = $transactionDetails['qty_pulling_transaction'];
                $canScan = $ispPackingItem->qty_isp < $scanLimit;
            }

            $data = [
                'keypoint' => $barang->keypoint ?? null,
                'part_no' => $barang->part_no ?? '-',
                'part_name' => $barang->part_name ?? '-',
                'size_plastik' => $barang->size_plastik ?? '-',
                'part_color' => $barang->part_color ?? '-',
                'qty_order' => $orderItem ? $orderItem->quantity : 0,
                'qty_pulling' => $totalQtyPulling,
                'qty_isp' => $ispPackingItem->qty_isp,
                'can_scan' => $canScan,
                'scan_limit' => $scanLimit,
                'transaction_details' => $transactionDetails,
            ];

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memuat detail item: ' . $e->getMessage()
            ], 500);
        }
    }

    public function scan(Request $request)
    {
        $request->validate([
            'isp_packing_item_id' => 'required|exists:isp_packing_items,id',
            'scan_value' => 'required|string',
            'transaction_index' => 'nullable|integer',
        ]);

        try {
            DB::beginTransaction();

            $ispPackingItem = IspPackingItem::with(['barang', 'ispPacking.barangKeluar.items'])
                ->findOrFail($request->isp_packing_item_id);

            // Validasi scan_value dengan barang
            $scanValue = trim($request->scan_value);
            $barang = $ispPackingItem->barang;
            
            if ($barang->qr_label !== $scanValue && $barang->part_no !== $scanValue) {
                throw new \Exception('QR/Part No tidak sesuai dengan item ini');
            }

            // Hitung total qty pulling dari semua barang keluar untuk part ini
            $order = $ispPackingItem->ispPacking->barangKeluar->order;
            $totalQtyPulling = $order->barangKeluar()
                ->whereHas('items', function($query) use ($ispPackingItem) {
                    $query->where('barang_id', $ispPackingItem->barang_id);
                })
                ->with(['items' => function($query) use ($ispPackingItem) {
                    $query->where('barang_id', $ispPackingItem->barang_id);
                }])
                ->get()
                ->sum(function($bk) use ($ispPackingItem) {
                    return $bk->items->where('barang_id', $ispPackingItem->barang_id)->sum('quantity');
                });

            if ($totalQtyPulling == 0) {
                throw new \Exception('Item tidak ditemukan di barang keluar');
            }

            // Tentukan batas scan berdasarkan transaksi atau total
            $scanLimit = $totalQtyPulling;
            
            if ($request->has('transaction_index')) {
                // Gunakan urutan yang sama dengan create method (terbaru dulu)
                $transactionsData = collect();
                foreach ($order->barangKeluar->sortByDesc('created_at') as $barangKeluar) {
                    $transactionItems = collect();
                    foreach ($barangKeluar->items as $bkItem) {
                        $transactionItems->push([
                            'barang_id' => $bkItem->barang_id,
                            'barang' => $bkItem->barang,
                            'qty_pulling' => $bkItem->quantity,
                            'qty_order' => $order->orderItems->where('part_no', $bkItem->barang->part_no)->first()->quantity ?? 0,
                        ]);
                    }
                    
                    $transactionsData->push([
                        'barang_keluar' => $barangKeluar,
                        'items' => $transactionItems,
                        'transaction_number' => $barangKeluar->id,
                        'tanggal' => $barangKeluar->tanggal_keluar
                    ]);
                }
                
                $transactionIndex = $request->transaction_index;
                if (isset($transactionsData[$transactionIndex])) {
                    $transaction = $transactionsData[$transactionIndex];
                    $transactionItem = $transaction['items']->where('barang_id', $barang->id)->first();
                    if ($transactionItem) {
                        $scanLimit = $transactionItem['qty_pulling'];
                    }
                }
            }

            // Cek apakah masih bisa scan berdasarkan batas yang ditentukan
            if ($ispPackingItem->qty_isp >= $scanLimit) {
                throw new \Exception('Qty ISP sudah mencapai batas pulling untuk transaksi ini');
            }

            // Tambahkan qty isp
            $ispPackingItem->qty_isp += 1;
            $ispPackingItem->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Scan berhasil',
                'qty_isp' => $ispPackingItem->qty_isp,
                'can_scan' => $ispPackingItem->qty_isp < $scanLimit,
                'scan_limit' => $scanLimit,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function submit(Request $request)
    {
        $request->validate([
            'isp_packing_id' => 'required|exists:isp_packing,id',
        ]);

        try {
            DB::beginTransaction();

            $ispPacking = IspPacking::findOrFail($request->isp_packing_id);
            
            // Update tanggal_isp untuk menandakan sudah submit
            $ispPacking->tanggal_isp = now();
            $ispPacking->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'ISP Packing berhasil disubmit',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}