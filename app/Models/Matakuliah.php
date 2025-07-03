<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MataKuliah extends Model
{
    use HasFactory;

    protected $table = 'mata_kuliah';

    protected $fillable = [
        'kode_matkul',
        'nama_matkul',
        'sks',
        'semester',
        'peminatan',
        'jumlah_kelas',    
    ];

    // In MataKuliah.php model
public function kelas()
{
    return $this->hasMany(Kelas::class, 'kode_matkul', 'kode_matkul');
}

}
