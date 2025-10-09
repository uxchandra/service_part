<?php

namespace App\Http\Controllers;

use App\Models\BarangMasuk;
use App\Models\BarangMasukItem;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BarangMasukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('barang-masuk.index');
    }

    /**
     * Get data for DataTables - Grouped by Date
     */
    /**
     * Get data for DataTables - Grouped by Date
     */
    public function getData(Request $request)
    {
        if ($request->ajax()) {
            $query = BarangMasuk::query()
                ->leftJoin('barang_masuk_items', 'barang_masuk.id', '=', 'barang_masuk_items.barang_masuk_id');

            // Search
            if ($request->has('search') && $request->search['value'] != '') {
                $search = $request->search['value'];
                $query->whereRaw('DATE(barang_masuk.tanggal_masuk) LIKE ?', ["%{$search}%"]);
            }

            // Group by date and calculate totals
            $query->select(
                DB::raw('DATE(barang_masuk.tanggal_masuk) as date_group'),
                DB::raw('COUNT(DISTINCT barang_masuk.id) as transaction_count'),
                DB::raw('COALESCE(SUM(barang_masuk_items.quantity), 0) as total_quantity')
            )
            ->groupBy('date_group');

            // Total records - PERBAIKAN DI SINI
            $totalRecords = DB::table('barang_masuk')
                ->select(DB::raw('DATE(tanggal_masuk) as date_group'))
                ->groupBy('date_group')
                ->get()
                ->count();
            
            // Total filtered
            $totalFiltered = $query->get()->count();

            // Ordering
            $query->orderBy('date_group', 'desc');

            // Pagination
            $start = $request->start ?? 0;
            $length = $request->length ?? 10;
            
            if ($length != -1) {
                $query->offset($start)->limit($length);
            }

            $data = $query->get()->map(function($row) {
                return [
                    'date' => Carbon::parse($row->date_group)->format('Y-m-d'),
                    'date_formatted' => Carbon::parse($row->date_group)->translatedFormat('d F Y'),
                    'transaction_count' => $row->transaction_count,
                    'total_quantity' => $row->total_quantity,
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
     * Get transactions by date for detail modal
     */
    public function getTransactionsByDate($date)
    {
        try {
            $transactions = BarangMasuk::with(['items.barang', 'user'])
                ->whereDate('tanggal_masuk', $date)
                ->orderBy('created_at', 'desc')
                ->get();

            $response = [
                'date' => Carbon::parse($date)->translatedFormat('d F Y'),
                'transactions' => $transactions->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'time' => $transaction->created_at->format('H:i'),
                        'user_name' => $transaction->user->name ?? '-',
                        'items_count' => $transaction->items->count(),
                        'total_quantity' => $transaction->items->sum('quantity'),
                        'items' => $transaction->items->map(function ($item) {
                            return [
                                'qr_label' => $item->barang->qr_label ?? '-',
                                'part_no' => $item->barang->part_no ?? '-',
                                'part_name' => $item->barang->part_name ?? '-',
                                'customer' => $item->barang->customer ?? '-',
                                'quantity' => $item->quantity,
                                'keypoint' => $item->barang->keypoint,
                                'keypoint_url' => $item->barang->keypoint ? asset('images/' . $item->barang->keypoint) : null,
                                'warna_plastik' => $item->barang->warna_plastik,
                                'warna_plastik_url' => $item->barang->warna_plastik ? asset('images/' . $item->barang->warna_plastik) : null,
                            ];
                        })
                    ];
                })
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal memuat data transaksi'
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('barang-masuk.create');
    }

    /**
     * Scan QR Label untuk get data barang
     */
    public function scanBarang(Request $request)
    {
        try {
            $qrLabel = $request->qr_label;
            
            $barang = Barang::where('qr_label', $qrLabel)->first();
            
            if (!$barang) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Barang dengan QR Label "' . $qrLabel . '" tidak ditemukan'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $barang->id,
                    'qr_label' => $barang->qr_label,
                    'part_no' => $barang->part_no,
                    'part_name' => $barang->part_name,
                    'customer' => $barang->customer,
                    'stok_current' => $barang->stok ?? 0,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.barang_id' => 'required|exists:barang,id',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'items.required' => 'Minimal harus ada 1 barang',
            'items.*.barang_id.required' => 'Barang wajib dipilih',
            'items.*.barang_id.exists' => 'Barang tidak ditemukan',
            'items.*.quantity.required' => 'Quantity wajib diisi',
            'items.*.quantity.min' => 'Quantity minimal 1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Create Barang Masuk - Auto set waktu sekarang
            $barangMasuk = BarangMasuk::create([
                'tanggal_masuk' => now(),
                'user_id' => auth()->id(),
            ]);

            // Create Items & Update Stok
            foreach ($request->items as $item) {
                // Insert item
                BarangMasukItem::create([
                    'barang_masuk_id' => $barangMasuk->id,
                    'barang_id' => $item['barang_id'],
                    'quantity' => $item['quantity'],
                ]);

                // Update stok barang
                $barang = Barang::find($item['barang_id']);
                $barang->stok = ($barang->stok ?? 0) + $item['quantity'];
                $barang->save();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Barang masuk berhasil disimpan',
                'data' => $barangMasuk
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource (single transaction detail)
     */
    public function show(string $id)
    {
        try {
            $barangMasuk = BarangMasuk::with(['user', 'items.barang'])->findOrFail($id);
            
            $items = $barangMasuk->items->map(function($item) {
                return [
                    'barang_id' => $item->barang_id,
                    'qr_label' => $item->barang->qr_label,
                    'part_no' => $item->barang->part_no,
                    'part_name' => $item->barang->part_name,
                    'customer' => $item->barang->customer,
                    'quantity' => $item->quantity,
                ];
            });

            return response()->json([
                'status' => 'success',
                'data' => [
                    'id' => $barangMasuk->id,
                    'tanggal_masuk' => Carbon::parse($barangMasuk->tanggal_masuk)->format('d/m/Y H:i:s'),
                    'user_name' => $barangMasuk->user->name ?? '-',
                    'items' => $items,
                    'total_items' => $items->count(),
                    'total_quantity' => $items->sum('quantity'),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();
        try {
            $barangMasuk = BarangMasuk::with('items')->findOrFail($id);
            
            // Kembalikan stok barang
            foreach ($barangMasuk->items as $item) {
                $barang = Barang::find($item->barang_id);
                $barang->stok = ($barang->stok ?? 0) - $item->quantity;
                $barang->save();
            }

            // Hapus transaksi (items akan terhapus otomatis karena cascade)
            $barangMasuk->delete();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Barang masuk berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}