<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Sales extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nama',
        'no_hp',
        'alamat',
        'remarks',
        'gaji_pokok',
        'uang_transport',
        'status',
        'terakhir_aktif',
    ];

    /**
     * Relations
     */
    public function transaksiProduks()
    {
        return $this->hasMany(TransaksiProduk::class, 'sales_id');
    }

    public function penghasilanDetails()
    {
        return $this->hasMany(DetailPenghasilanSales::class);
    }

    public function filterDetailSum($field, $filters)
    {
        $bulan = $filters['bulan']['value'] ?? null;
        $from = $filters['rentang_tanggal']['from'] ?? null;
        $until = $filters['rentang_tanggal']['until'] ?? null;

        return $this->penghasilanDetails
            ->filter(function ($detail) use ($bulan, $from, $until) {
                $tanggal = $detail->tanggal ? \Illuminate\Support\Carbon::parse($detail->tanggal) : null;

                return (!$bulan || ($tanggal && $tanggal->month == $bulan))
                    && (!$from || ($tanggal && $tanggal->greaterThanOrEqualTo($from)))
                    && (!$until || ($tanggal && $tanggal->lessThanOrEqualTo($until)));
            })
            ->sum($field);
    }

}
