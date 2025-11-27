<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SewaAC extends Model
{
    use HasFactory;

    protected $fillable = ['tanggal', 'pemasukan', 'pengeluaran', 'keterangan_pemasukan','keterangan_pengeluaran'];

}
