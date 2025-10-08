<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Models\User;
use App\Models\BarangMasukItem;

class BarangMasuk extends Model
{
    use HasFactory;

    protected $table = 'barang_masuk';

    protected $fillable = [
        'tanggal_masuk',
        'user_id',
    ];

    protected $casts = [
        'tanggal_masuk' => 'datetime', // Ubah dari 'date' ke 'datetime'
    ];

    /**
     * Relasi ke User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke BarangMasukItem
     */
    public function items()
    {
        return $this->hasMany(BarangMasukItem::class);
    }

    /**
     * Accessor untuk format tanggal Indonesia (dengan waktu)
     */
    public function getTanggalMasukFormattedAttribute()
    {
        // return $this->tanggal_masuk->format('d/m/Y H:i:s');
        // Atau format lain:
        // return $this->tanggal_masuk->format('d-m-Y H:i'); // 07-01-2025 14:30
        return $this->tanggal_masuk->format('d M Y, H:i'); // 07 Jan 2025, 14:30
    }


}