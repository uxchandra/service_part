<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangKeluarItem extends Model
{
    protected $table = 'barang_keluar_items';

    protected $fillable = [
        'barang_keluar_id',
        'barang_id',
        'quantity',
    ];

    public function barangKeluar()
    {
        return $this->belongsTo(BarangKeluar::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}


