<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IspPackingItem extends Model
{
    protected $table = 'isp_packing_items';

    protected $fillable = [
        'isp_packing_id',
        'barang_id',
        'qty_isp',
    ];

    public function ispPacking()
    {
        return $this->belongsTo(IspPacking::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }
}
