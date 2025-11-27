<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Utang extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'barang_masuk_id',
        'due_date',
        'keterangan',
        'status_pembayaran',
        'fotos',
        'sudah_dibayar',
        'total_harga_modal',
    ];

    protected $casts = [
        'fotos' => 'array',
        'due_date' => 'date',
    ];

    protected static function booted()
    {
        static::creating(function ($detail) {
            if ($detail->barang_masuk_id) {
                $barangMasuk = BarangMasuk::with('barangMasukDetails')->find($detail->barang_masuk_id);
                
                if ($barangMasuk) {
                    $totalHargaModal = $barangMasuk->barangMasukDetails->sum(function ($detail) {
                        $harga = $detail->harga_modal ?? 0;
                        $jumlah = $detail->jumlah_barang_masuk ?? 0;

                        return $harga * $jumlah;
                    });

                    $detail->total_harga_modal = $totalHargaModal;

                }else{
                    $detail->total_harga_modal = 0;

                }
            }
        });
    }

    public function barangMasuk()
    {
        return $this->belongsTo(BarangMasuk::class);
    }
}
