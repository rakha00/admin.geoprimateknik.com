<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    use HasFactory;
    protected $fillable = ['nama', 'no_hp', 'alamat', 'gaji_pokok', 'status', 'terakhir_aktif'];


    public function penghasilanDetails()
    {
        return $this->hasMany(DetailPenghasilanStaff::class);
    }

}
