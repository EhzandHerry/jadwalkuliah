<?php

namespace App\Exports;

use App\Models\JadwalKuliah;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class JadwalMatrixExport implements FromView, ShouldAutoSize, WithEvents
{
    /**
     * Menggunakan Blade View untuk merender data ke Excel.
     * Ini memberikan kontrol penuh atas tata letak, termasuk sel yang digabung (merge cells).
     *
     * @return View
     */
    public function view(): View
    {
        // Asumsi kita hanya mengekspor jadwal untuk hari 'SENIN' sesuai gambar.
        $hari = 'SENIN';

        // 1. Ambil semua nama ruangan yang unik untuk hari tersebut, urutkan.
        $rooms = JadwalKuliah::where('hari', $hari)
            ->orderBy('nama_ruangan', 'asc')
            ->distinct()
            ->pluck('nama_ruangan')
            ->toArray();

        // 2. Ambil semua data jadwal untuk hari tersebut.
        $jadwals = JadwalKuliah::with(['mataKuliah', 'dosen'])
            ->where('hari', $hari)
            ->get();
            
        // 3. Susun data jadwal ke dalam grid agar mudah diakses di view.
        // Key-nya adalah [jam][ruangan].
        $scheduleGrid = [];
        foreach ($jadwals as $jadwal) {
            if (!empty($jadwal->jam) && !empty($jadwal->nama_ruangan)) {
                $scheduleGrid[$jadwal->jam][$jadwal->nama_ruangan] = $jadwal;
            }
        }

        // 4. Kirim data yang sudah terstruktur ke file Blade view.
        return view('exports.jadwal', [
            'rooms' => $rooms,
            'scheduleGrid' => $scheduleGrid,
            'day' => $hari,
        ]);
    }

    /**
     * Menerapkan styling (seperti border, alignment, merge cell, warna background)
     * setelah sheet Excel selesai dibuat.
     *
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = $sheet->getHighestColumn();
                $lastRow = $sheet->getHighestRow();

                // Judul Utama (Baris 1)
                $sheet->mergeCells('A1:'.$lastCol.'1');
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Header Hari (Baris 3)
                $sheet->mergeCells('C3:'.$lastCol.'3');
                $sheet->getStyle('A3:'.$lastCol.'4')->getFont()->setBold(true);
                $sheet->getStyle('A3:'.$lastCol.'4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                
                // Style untuk header kolom (Sesi, Jam, Ruangan)
                $sheet->getStyle('A3:'.$lastCol.'4')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('F2F2F2');
                
                // Style untuk baris istirahat/pergantian sesi
                $breakRows = [9, 17, 21, 25, 32];
                foreach ($breakRows as $rowNum) {
                    $sheet->mergeCells('A'.$rowNum.':'.$lastCol.$rowNum);
                    $cellStyle = $sheet->getStyle('A'.$rowNum);
                    $cellStyle->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $cellStyle->getFont()->setBold(true);
                    if ($rowNum == 21 || $rowNum == 32) { // Istirahat
                        $cellStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('BFBFBF');
                    }
                }
                
                // Tambahkan border ke seluruh tabel yang berisi data
                $styleArray = [
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ]
                ];
                $sheet->getStyle('A3:' . $lastCol . $lastRow)->applyFromArray($styleArray);
            },
        ];
    }
}
