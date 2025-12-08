<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiJasa extends Model
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'tanggal_transaksi',
        'no_invoice',
        'no_surat_jalan',
        'teknisi_id',
        'helper_id',
        'konsumen_jasa_id',
        'total_pendapatan_jasa',
        'total_pengeluaran_jasa',
        'total_keuntungan_jasa',
        'keluhan',
    ];

    protected static function booted()
    {
        static::created(function (TransaksiJasa $model) {
            // Logic to create Piutang will be added here
            if ($model->konsumen_jasa_id && $model->total_pendapatan_jasa > 0) {
                \App\Models\Piutang::create([
                    'transaksi_jasa_id' => $model->id,
                    'keterangan' => 'Transaksi Jasa', // Add default description
                    'total_harga_modal' => $model->total_pendapatan_jasa, // Uses total_harga_modal column for amount
                    'sudah_dibayar' => 0,
                    'status_pembayaran' => 'belum lunas',
                    'due_date' => $model->tanggal_transaksi,
                ]);
            }
        });

        static::updated(function (TransaksiJasa $model) {
            // Logic to update Piutang if needed
        });
    }

    public function teknisi()
    {
        return $this->belongsTo(\App\Models\Teknisi::class);
    }

    public function helper()
    {
        return $this->belongsTo(\App\Models\Helper::class);
    }

    public function konsumenJasa()
    {
        return $this->belongsTo(\App\Models\KonsumenJasa::class);
    }

    public function piutang()
    {
        return $this->hasOne(\App\Models\Piutang::class);
    }
}

