<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'part_no', 'quantity'];

    // Relasi ke Order
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Relasi ke Barang menggunakan part_no
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'part_no', 'part_no');
    }
}