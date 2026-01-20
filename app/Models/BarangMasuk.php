<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    protected $fillable = [
        'principle_subdealer_id',
        'tanggal',
        'nomor_barang_masuk',
        'status',
    ];

    protected static function booted()
    {
        static::saved(function ($barangMasuk) {
            if ($barangMasuk->wasChanged('status')) {
                foreach ($barangMasuk->details as $detail) {
                    if ($detail->unit_ac_id) {
                        $unit = \App\Models\UnitAc::find($detail->unit_ac_id);
                        if ($unit) {
                            $unit->recalculateStock();
                        }
                    } elseif ($detail->sparepart_id) {
                        $detail->updateSparepartStock();
                    }

                    $detail->syncUtang();
                }
            }
        });
    }

    public function barangMasukDetails()
    {
        return $this->hasMany(BarangMasukDetail::class);
    }

    public function principleSubdealer()
    {
        return $this->belongsTo(PrincipleSubdealer::class);
    }

    public function details()
    {
        return $this->hasMany(BarangMasukDetail::class);
    }
}
