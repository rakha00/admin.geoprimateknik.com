<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiJasa extends Model
{
    protected $fillable = [
        'konsumen_jasa_id',
        'tanggal',
        'pemasukan',
        'remarks_pemasukan',
        'pengeluaran',
        'remarks_pengeluaran',
        'pemasukan_bersih',
    ];

    protected static function booted()
    {
        static::creating(function (TransaksiJasa $model) {
            $model->pemasukan_bersih = ($model->pemasukan ?? 0) - ($model->pengeluaran ?? 0);
        });
        static::updating(function (TransaksiJasa $model) {
            $model->pemasukan_bersih = ($model->pemasukan ?? 0) - ($model->pengeluaran ?? 0);
        });
    }

    // Relasi ke Konsumen Jasa
    public function konsumenJasa()
    {
        return $this->belongsTo(\App\Models\KonsumenJasa::class);
    }

    public function teknisi()
    {
        return $this->belongsTo(\App\Models\Teknisi::class);
    }

    public function helper()
    {
        return $this->belongsTo(\App\Models\Helper::class);
    }

}

