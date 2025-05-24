<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MataKuliah;
use App\Models\Kelas;

class MataKuliahSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'kode_matkul'  => 'TI207',
                'nama_matkul'  => 'Al-Islam dan Kemuhammadiyahan 2 (Ibadah dan Muamalah)',
                'sks'          => 2,
                'semester'     => 'Genap',
                'jumlah_kelas' => 3,
            ],
            [
                'kode_matkul'  => 'TI201',
                'nama_matkul'  => 'Matematika Teknologi Informasi 2',
                'sks'          => 3,
                'semester'     => 'Genap',
                'jumlah_kelas' => 2,
            ],
            [
                'kode_matkul'  => 'TI203',
                'nama_matkul'  => 'Pemrograman Dasar',
                'sks'          => 3,
                'semester'     => 'Genap',
                'jumlah_kelas' => 3,
            ],
            [
                'kode_matkul'  => 'TI204',
                'nama_matkul'  => 'Administrasi Basis Data',
                'sks'          => 3,
                'semester'     => 'Genap',
                'jumlah_kelas' => 3,
            ],
            [
                'kode_matkul'  => 'TI208',
                'nama_matkul'  => 'Bahasa Inggris Menengah',
                'sks'          => 2,
                'semester'     => 'Genap',
                'jumlah_kelas' => 3,
            ],
        ];

        $letters = range('A', 'Z');

        foreach ($data as $item) {
            // 1) Create the MataKuliah with jumlah_kelas
            $mk = MataKuliah::create([
                'kode_matkul'  => $item['kode_matkul'],
                'nama_matkul'  => $item['nama_matkul'],
                'sks'          => $item['sks'],
                'semester'     => $item['semester'],
                'jumlah_kelas' => $item['jumlah_kelas'],
            ]);

            // 2) Generate Kelas A, B, C… up to jumlah_kelas
            for ($i = 0; $i < $item['jumlah_kelas']; $i++) {
                Kelas::create([
                    'kode_matkul' => $mk->kode_matkul,
                    'kelas'       => $letters[$i],
                ]);
            }
        }
    }
}
