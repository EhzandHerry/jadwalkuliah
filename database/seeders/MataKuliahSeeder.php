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
            'kode_matkul' => 'TI207',
            'nama_matkul' => 'Al-Islam dan Kemuhammadiyahan 2 (Ibadah dan Muamalah)',
            'sks' => 2,
            'semester' => 'Genap',
        ]);

        // Menambahkan 3 Kelas untuk Mata Kuliah TI201
        Kelas::create(['kode_matkul' => 'TI207', 'kelas' => 'A']);
        Kelas::create(['kode_matkul' => 'TI207', 'kelas' => 'B']);
        Kelas::create(['kode_matkul' => 'TI207', 'kelas' => 'C']);

        // Tambah Mata Kuliah lainnya dengan kelas
        $mataKuliah2 = MataKuliah::create([
            'kode_matkul' => 'TI201',
            'nama_matkul' => 'Matematika Teknologi Informasi 2',
            'sks' => 3,
            'semester' => 'Genap',
        ]);
        Kelas::create(['kode_matkul' => 'TI201', 'kelas' => 'A']);
        Kelas::create(['kode_matkul' => 'TI201', 'kelas' => 'B']);

        $mataKuliah3 = MataKuliah::create([
            'kode_matkul' => 'TI203',
            'nama_matkul' => 'Pemrograman Dasar',
            'sks' => 3,
            'semester' => 'Genap',
        ]);
        Kelas::create(['kode_matkul' => 'TI203', 'kelas' => 'A']);
        Kelas::create(['kode_matkul' => 'TI203', 'kelas' => 'B']);
        Kelas::create(['kode_matkul' => 'TI203', 'kelas' => 'C']);

        $mataKuliah4 = MataKuliah::create([
            'kode_matkul' => 'TI204',
            'nama_matkul' => 'Administrasi Basis Data',
            'sks' => 3,
            'semester' => 'Genap',
        ]);
        Kelas::create(['kode_matkul' => 'TI204', 'kelas' => 'A']);
        Kelas::create(['kode_matkul' => 'TI204', 'kelas' => 'B']);
        Kelas::create(['kode_matkul' => 'TI204', 'kelas' => 'C']);

        $mataKuliah5 = MataKuliah::create([
            'kode_matkul' => 'TI208',
            'nama_matkul' => 'Bahasa Inggris Menengah',
            'sks' => 2,
            'semester' => 'Genap',
        ]);
        Kelas::create(['kode_matkul' => 'TI208', 'kelas' => 'A']);
        Kelas::create(['kode_matkul' => 'TI208', 'kelas' => 'B']);
        Kelas::create(['kode_matkul' => 'TI208', 'kelas' => 'C']);
    }
}
