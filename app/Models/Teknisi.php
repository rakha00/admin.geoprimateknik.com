<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Teknisi extends Model
{
    use HasFactory;

    protected $fillable = ['nama', 'no_hp', 'alamat', 'gaji_pokok', 'status', 'terakhir_aktif'];

    public function penghasilanDetails()
    {
        return $this->hasMany(DetailPenghasilanTeknisi::class);
    }
    public function getTotalGajiAttribute()
    {
        return ($this->gaji_pokok ?? 0) + ($this->lembur ?? 0) + ($this->bonus ?? 0);
    }

    public function getGajiYangDiterimaAttribute()
    {
        return $this->total_gaji - ($this->kasbon ?? 0);
    }

    public function sumDetail($field, $from = null, $until = null)
    {
        return $this->penghasilanDetails
            ->filter(function ($detail) use ($from, $until) {
                $tanggal = $detail->tanggal ? Carbon::parse($detail->tanggal) : null;

                return (!$from || ($tanggal && $tanggal->gte(Carbon::parse($from))))
                    && (!$until || ($tanggal && $tanggal->lte(Carbon::parse($until))));
            })
            ->sum($field);
    }

    public function hitungGajiDiterima($from = null, $until = null)
    {
        $lembur = $this->sumDetail('lembur', $from, $until);
        $bonus = $this->sumDetail('bonus', $from, $until);
        $kasbon = $this->sumDetail('kasbon', $from, $until);

        return $this->gaji_pokok + $lembur + $bonus - $kasbon;
    }

}
