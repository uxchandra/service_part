<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'barang';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'qr_label',
        'part_no',
        'customer',
        'part_name',
        'size_plastic',
        'part_color',
        'stok',
        'keypoint',
        'warna_plastik',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'stok' => 'integer',
    ];

    public function barangMasukItems()
    {
        return $this->hasMany(BarangMasukItem::class);
    }
}