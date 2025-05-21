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
        'ruang_kelas_id',
        'unique_number',
    ];

    public function ruangKelas()
    {
        return $this->belongsTo(RuangKelas::class, 'ruang_kelas_id');
    }

    // Relasi dengan model User (Dosen)
    public function dosen()
    {
        return $this->belongsTo(User::class, 'unique_number', 'unique_number');
    }

    // Define the relationship with MataKuliah
    public function mataKuliah()
    {
        return $this->belongsTo(MataKuliah::class, 'kode_matkul', 'kode_matkul');
    }

    public function jadwal()
{
    return $this->hasOne(JadwalKuliah::class, 'kelas', 'kelas')
        ->where('kode_mata_kuliah', $this->kode_matkul);
}


}