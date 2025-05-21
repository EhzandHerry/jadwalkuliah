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
        'kode_mata_kuliah',
        'kelas',
        'nama_ruangan',
        'unique_number',
        'jam',
    ];

    // Relasi ke MataKuliah berdasarkan kode_mata_kuliah
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'kode_mata_kuliah', 'kode_matkul');
    }

    // Relasi ke RuangKelas berdasarkan nama_ruangan
    public function ruangKelas()
    {
        return $this->belongsTo(RuangKelas::class, 'nama_ruangan', 'nama_ruangan');
    }

    // Relasi ke User (Dosen) berdasarkan unique_number
    public function dosen()
    {
        return $this->belongsTo(User::class, 'unique_number', 'unique_number');
    }

    // Relasi ke Kelas berdasarkan kolom kelas (huruf) dan kode_matkul (gabungan kunci)
    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas', 'kelas')
                    ->where('kode_matkul', $this->kode_mata_kuliah);
    }
}
