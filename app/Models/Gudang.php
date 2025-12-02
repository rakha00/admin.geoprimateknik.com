<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;


class Gudang extends Model
{
    use HasFactory;
    protected $fillable = ['nama', 'no_hp', 'alamat', 'gaji_pokok', 'status', 'terakhir_aktif'];

    public function penghasilanDetails()
    {
        return $this->hasMany(DetailPenghasilanGudang::class);
    }

    public function sumDetail($field, $bulan = null, $tahun = null)
    {
        return $this->penghasilanDetails
            ->filter(function ($detail) use ($bulan, $tahun) {
                $tanggal = $detail->tanggal ? Carbon::parse($detail->tanggal) : null;

                return (!$bulan || ($tanggal && $tanggal->month == $bulan))
                    && (!$tahun || ($tanggal && $tanggal->year == $tahun));
            })
            ->sum($field);
    }

    public function hitungGajiDiterima($bulan = null, $tahun = null)
    {
        $lembur = $this->sumDetail('lembur', $bulan, $tahun);
        $bonus = $this->sumDetail('bonus', $bulan, $tahun);
        $kasbon = $this->sumDetail('kasbon', $bulan, $tahun);

        return $this->gaji_pokok + $lembur + $bonus - $kasbon;
    }
}
