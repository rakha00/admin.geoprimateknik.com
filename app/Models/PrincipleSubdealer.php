<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrincipleSubdealer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama',         // kolom nama
        'sales',        // kolom sales
        'no_hp',        // kolom no_hp
        'alamat',
        'remarks',      // kolom remarks (jika ada)
    ];

    /**
     * Relations
     */
    public function barangMasuks()
    {
        return $this->hasMany(BarangMasuk::class, 'principle_subdealer_id');
    }
}
