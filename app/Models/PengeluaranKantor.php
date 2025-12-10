<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PengeluaranKantor extends Model
{
    use HasFactory;

    protected $fillable = [
        'tanggal',
        'pengeluaran',
        'remarks',
        'pembayaran',
    ];
}
