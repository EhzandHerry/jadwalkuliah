<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RuangKelas extends Model
{
    use HasFactory;

    protected $table = 'ruang_kelas';

    protected $fillable = [
        'kode_ruangan', 'nama_ruangan', 'lantai', 'nama_gedung'
    ];

    // Relasi dengan Kelas
    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'ruang_kelas_id');
    }

}
