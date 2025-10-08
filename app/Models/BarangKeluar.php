<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    protected $table = 'barang_keluar';

    protected $fillable = [
        'order_id',
        'tanggal_keluar',
        'user_id',
    ];

    protected $casts = [
        'tanggal_keluar' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function items()
    {
        return $this->hasMany(BarangKeluarItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}


