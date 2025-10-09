<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Barang;
use App\Models\BarangKeluar;
use App\Models\IspPacking;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // KPI counts by effective status (computed in PHP for first render)
        $orders = Order::with(['orderItems', 'barangKeluar.ispPacking.items'])->latest()->get();
        $counters = [
            'planning' => 0,
            'partial' => 0,
            'pulling' => 0,
            'delay' => 0,
            'completed' => 0,
        ];
        foreach ($orders as $order) {
            $totalOrder = (int) $order->orderItems->sum('quantity');
            $totalPacking = 0;
            foreach ($order->barangKeluar as $bk) {
                if ($bk->ispPacking) {
                    $totalPacking += (int) $bk->ispPacking->items->sum('qty_isp');
                }
            }
            $status = $order->status;
            if ($totalOrder > 0 && $totalPacking >= $totalOrder) {
                $status = 'completed';
            } else if ($order->delivery_date && now()->startOfDay()->gt(Carbon::parse($order->delivery_date)->startOfDay())) {
                $status = 'delay';
            }
            if (!isset($counters[$status])) $counters[$status] = 0;
            $counters[$status]++;
        }

        // Monitoring stok: barang dengan stok rendah yang ada di order aktif (planning/partial/pulling)
        $activeOrderPartNos = Order::whereIn('status', ['planning', 'partial', 'pulling'])
            ->with('orderItems')
            ->get()
            ->flatMap->orderItems
            ->pluck('part_no')
            ->unique()
            ->values();

        $stokList = Barang::whereIn('part_no', $activeOrderPartNos)
            ->orderBy('stok', 'asc')
            ->limit(20)
            ->get(['part_no', 'part_name', 'stok']);

        return view('dashboard', compact('counters', 'stokList'));
    }
    public function mobile()
    {
        return view('mobile-dashboard');
    }
}
