<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliah';

    protected $fillable = [
        'kode_matkul', 'nama_matkul', 'sks', 'unique_number'
    ];

    // Relasi dengan model User (Dosen)
    public function user()
    {
        return $this->belongsTo(User::class, 'unique_number', 'unique_number');
    }

    // In MataKuliah.php model
public function kelas()
{
    return $this->hasMany(Kelas::class, 'kode_matkul', 'kode_matkul');
}

}
