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
        'due_date',
        'keterangan',
        'status_pembayaran',
        'fotos',
        'sudah_dibayar',
        'total_harga_modal',
    ];

    protected $casts = [
        'fotos'    => 'array',
        'due_date' => 'date',
    ];

protected static function booted()
{
    static::creating(function ($piutang) {
        if ($piutang->non_pajak_id) {
            $nonpajak = NonPajak::with('details')->find($piutang->non_pajak_id);
            $piutang->total_harga_modal = $nonpajak->details->sum(fn($d) => $d->total_harga_jual ?? ($d->harga_jual * $d->jumlah_keluar));
            $piutang->due_date = $nonpajak->tanggal ?? null;
        }
        elseif ($piutang->pajak_id) {
            $pajak = Pajak::with('details')->find($piutang->pajak_id);
            $piutang->total_harga_modal = $pajak->details->sum(fn($d) => $d->total_harga_jual ?? ($d->harga_jual * $d->jumlah_keluar));
            $piutang->due_date = $pajak->tanggal ?? null;
        }
    });
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
}
