<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenghasilanGudang extends Model
{
    use HasFactory;

    protected $fillable = [
        'gudang_id',
        'kasbon',
        'lembur',
        'bonus',
        'keterangan',
        'remarks',
        'tanggal',
    ];
    public function gudang()
    {
        return $this->belongsTo(Gudang::class, 'gudang_id');
    }
}
