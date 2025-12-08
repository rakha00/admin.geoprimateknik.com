<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Piutang extends Model
{
    use HasFactory;

    protected $fillable = [
        'pajak_id',
        'non_pajak_id',
        'sparepart_keluar_id',
        'transaksi_jasa_id',
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
        static::creating(function ($piutang) {
            if ($piutang->non_pajak_id) {
                $nonpajak = NonPajak::with('details')->find($piutang->non_pajak_id);
                $piutang->total_harga_modal = $nonpajak->details->sum(fn($d) => $d->total_harga_jual ?? ($d->harga_jual * $d->jumlah_keluar));
                $piutang->due_date = $nonpajak->tanggal ?? null;
            } elseif ($piutang->pajak_id) {
                $pajak = Pajak::with('details')->find($piutang->pajak_id);
                $piutang->total_harga_modal = $pajak->details->sum(fn($d) => $d->total_harga_jual ?? ($d->harga_jual * $d->jumlah_keluar));
                $piutang->due_date = $pajak->tanggal ?? null;
            } elseif ($piutang->sparepart_keluar_id) {
                $sparepartKeluar = SparepartKeluar::with('details')->find($piutang->sparepart_keluar_id);
                $piutang->total_harga_modal = $sparepartKeluar->details->sum(fn($d) => $d->total_harga_jual ?? ($d->harga_jual * $d->jumlah_keluar));
                $piutang->due_date = $sparepartKeluar->tanggal ?? null;
            } elseif ($piutang->transaksi_jasa_id) {
                $transaksiJasa = TransaksiJasa::find($piutang->transaksi_jasa_id);
                $piutang->total_harga_modal = $piutang->total_harga_modal ?: ($transaksiJasa->total_pendapatan_jasa ?? 0);
                $piutang->due_date = $piutang->due_date ?: ($transaksiJasa->tanggal_transaksi ?? null);
            }
        });
    }

    /**
     * Relasi ke SparepartKeluar
     */
    public function sparepartKeluar()
    {
        return $this->belongsTo(SparepartKeluar::class);
    }


    /**
     * Relasi ke Pajak
     */
    public function pajak()
    {
        return $this->belongsTo(Pajak::class);
    }

    /**
     * Relasi ke NonPajak
     */
    public function nonPajak()
    {
        return $this->belongsTo(NonPajak::class);
    }

    public function transaksiJasa()
    {
        return $this->belongsTo(TransaksiJasa::class);
    }
}
