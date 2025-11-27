<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailPenghasilanStaff extends Model
{
    use HasFactory;

    protected $table = 'detail_penghasilan_staffs';
    protected $fillable = [
        'kasbon',
        'lembur',
        'bonus',
        'keterangan',
        'remarks',
        'tanggal',
        'staff_id', // kalau ada relasi foreign key, masukin juga
    ];
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

}
