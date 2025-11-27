<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenghasilanSales extends Model
{
    use HasFactory;
    protected $fillable = [
        'kasbon',
        'lembur',
        'bonus_retail',
        'bonus_projek',
        'keterangan',
        'remarks',
        'tanggal',
        'sales_id', // kalau ada relasi foreign key, masukin juga
    ];

    public function sales()
    {
        return $this->belongsTo(Sales::class);
    }
}
