<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    protected $fillable = [
        'principle_subdealer_id',
        'tanggal',
        'nomor_barang_masuk',
    ];
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
