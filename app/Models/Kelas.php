<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'kode_matkul',
        'kelas',
        'nidn',
    ];

    public function ruangKelas()
    {
        return $this->belongsTo(RuangKelas::class, 'ruang_kelas_id');
    }

    // Relasi dengan model User (Dosen)
    public function dosen()
    {
        return $this->belongsTo(User::class, 'nidn', 'nidn');
    }

    // Define the relationship with MataKuliah
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'kode_matkul', 'kode_matkul');
    }

    // public function jadwal()
    // {
    //     return $this->hasMany(JadwalKuliah::class, 'kelas', 'kelas');
           
    // }

    public function jadwalKuliahs()
{
    // Konsisten dengan relasi di JadwalKuliah
    return $this->hasMany(JadwalKuliah::class, 'kelas', 'kelas');
}


}