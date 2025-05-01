<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MataKuliah;
use App\Models\Kelas;

class MataKuliahSeeder extends Seeder
{
    public function run()
    {
        // Data Mata Kuliah
        $mataKuliah = MataKuliah::create([
            'kode_matkul' => 'TI201',
            'nama_matkul' => 'Pengembangan Game',
            'sks' => 3,
        ]);

        // Menambahkan 3 Kelas untuk Mata Kuliah TI201
        Kelas::create(['kode_matkul' => 'TI201', 'kelas' => 'A']);
        Kelas::create(['kode_matkul' => 'TI201', 'kelas' => 'B']);
        Kelas::create(['kode_matkul' => 'TI201', 'kelas' => 'C']);

        // Tambah Mata Kuliah lainnya dengan kelas
        $mataKuliah2 = MataKuliah::create([
            'kode_matkul' => 'TI202',
            'nama_matkul' => 'Desain Game',
            'sks' => 3,
        ]);
        Kelas::create(['kode_matkul' => 'TI202', 'kelas' => 'A']);
        Kelas::create(['kode_matkul' => 'TI202', 'kelas' => 'B']);

        $mataKuliah3 = MataKuliah::create([
            'kode_matkul' => 'TI203',
            'nama_matkul' => 'Pengembangan Aplikasi Mobile Lanjut',
            'sks' => 3,
        ]);
        Kelas::create(['kode_matkul' => 'TI203', 'kelas' => 'A']);
        Kelas::create(['kode_matkul' => 'TI203', 'kelas' => 'B']);
        Kelas::create(['kode_matkul' => 'TI203', 'kelas' => 'C']);
    }
}
