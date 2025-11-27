<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenghasilanTeknisi extends Model
{
    use HasFactory;

    protected $fillable = [
        'kasbon',
        'lembur',
        'bonus',
        'keterangan',
        'remarks',
        'tanggal',
        'teknisi_id', // kalau ada relasi foreign key, masukin juga
    ];
    public function teknisi()
    {
        return $this->belongsTo(Teknisi::class);
    }

}
