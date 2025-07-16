<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalKuliah extends Model
{
    use HasFactory;

    protected $table = 'jadwal';

    protected $fillable = [
        'hari',
        'kode_matkul', 
        'kelas',
        'nama_ruangan',
        'nidn',
        'jam',
    ];

    // Relasi ke MataKuliah
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'kode_matkul', 'kode_matkul');
    }

    // Relasi ke RuangKelas
    public function ruangKelas()
    {
        return $this->belongsTo(RuangKelas::class, 'nama_ruangan', 'nama_ruangan');
    }

    // Relasi ke User (Dosen)
    public function dosen()
    {
        return $this->belongsTo(User::class, 'nidn', 'nidn');
    }

    // Relasi ke Kelas (INI FOKUS UTAMA KITA)
    //  public function kelas()
    // {
    //     // Hanya menggunakan foreign key 'kelas' dan local key 'kelas'
    //     return $this->belongsTo(Kelas::class, 'kelas', 'kelas');
    // }

    public function kelas()
{
    // Menggunakan kolom 'kelas' di jadwal untuk match dengan kolom 'kelas' di tabel kelas
    return $this->belongsTo(Kelas::class, 'kelas', 'kelas');
}
}