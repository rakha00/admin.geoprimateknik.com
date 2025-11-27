<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Helper extends Model
{
    use HasFactory;

    protected $fillable = ['nama', 'no_hp', 'alamat', 'gaji_pokok'];

public function penghasilanDetails()
    {
        return $this->hasMany(DetailPenghasilanHelper::class);
    }

    public function sumDetail($field, $bulan = null, $from = null, $until = null)
    {
        return $this->penghasilanDetails
            ->filter(function ($detail) use ($bulan, $from, $until) {
                $tanggal = $detail->tanggal ? Carbon::parse($detail->tanggal) : null;

                return (!$bulan || ($tanggal && $tanggal->month == $bulan))
                    && (!$from || ($tanggal && $tanggal->gte($from)))
                    && (!$until || ($tanggal && $tanggal->lte($until)));
            })
            ->sum($field);
    }

    public function hitungGajiDiterima($bulan = null, $from = null, $until = null)
    {
        $lembur = $this->sumDetail('lembur', $bulan, $from, $until);
        $bonus  = $this->sumDetail('bonus', $bulan, $from, $until);
        $kasbon = $this->sumDetail('kasbon', $bulan, $from, $until);

        return $this->gaji_pokok + $lembur + $bonus - $kasbon;
    }

}
