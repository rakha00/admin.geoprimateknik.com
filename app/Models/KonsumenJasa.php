<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KonsumenJasa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama',
        'alamat',
        'no_hp',
        'remarks',
    ];

        public function transaksiJasas()
    {
        return $this->hasMany(TransaksiJasa::class);
    }
}