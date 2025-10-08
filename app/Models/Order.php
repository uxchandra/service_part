<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['no_transaksi', 'delivery_date', 'status'];
    
    protected $casts = [
        'delivery_date' => 'date',
    ];

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function barangKeluar()
    {
        return $this->hasMany(BarangKeluar::class);
    }

    public function ispPacking()
    {
        return $this->hasOneThrough(
            IspPacking::class,
            BarangKeluar::class,
            'order_id', // Foreign key on barang_keluar table
            'barang_keluar_id', // Foreign key on isp_packing table
            'id', // Local key on orders table
            'id' // Local key on barang_keluar table
        );
    }
}