<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenghasilanHelper extends Model
{
    use HasFactory;

    protected $fillable = [
        'kasbon',
        'lembur',
        'bonus',
        'keterangan',
        'remarks',
        'tanggal',
        'helper_id', // kalau ada relasi foreign key, masukin juga
    ];
    public function helper()
    {
        return $this->belongsTo(Helper::class);
    }
}
