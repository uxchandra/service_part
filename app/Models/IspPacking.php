<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IspPacking extends Model
{
    protected $table = 'isp_packing';

    protected $fillable = [
        'barang_keluar_id',
        'tanggal_isp',
        'user_id',
    ];

    protected $casts = [
        'tanggal_isp' => 'datetime',
    ];

    public function barangKeluar()
    {
        return $this->belongsTo(BarangKeluar::class);
    }

    public function items()
    {
        return $this->hasMany(IspPackingItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
