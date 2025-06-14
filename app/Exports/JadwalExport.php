<?php

namespace App\Exports;

use App\Models\JadwalKuliah;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JadwalExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        // Ambil data jadwal beserta relasi yang diperlukan (kelas, matakuliah, dosen)
        return JadwalKuliah::with(['kelas', 'mataKuliah', 'dosen'])
            ->get()
            ->map(function ($jadwal) {
                return [
                    'Kode Mata Kuliah' => $jadwal->kode_mata_kuliah,
                    'Nama Mata Kuliah' => $jadwal->mataKuliah->nama_matkul ?? '-',
                    'Kelas'           => $jadwal->kelas,
                    'NIDN'            => $jadwal->unique_number,
                    'Nama Dosen'      => $jadwal->dosen->name ?? '-',
                    'Ruang Kelas'     => $jadwal->nama_ruangan,
                    'Hari'            => $jadwal->hari,
                    'Jam'             => $jadwal->jam,
                ];
            });
    }

    public function headings(): array
    {
        return [
            'Kode Mata Kuliah',
            'Nama Mata Kuliah',
            'Kelas',
            'NIDN',
            'Nama Dosen',
            'Ruang Kelas',
            'Hari',
            'Jam',
        ];
    }
}
