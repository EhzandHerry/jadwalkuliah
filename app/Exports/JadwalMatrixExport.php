<?php

namespace App\Exports;

use App\Models\JadwalKuliah;
use App\Models\RuangKelas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class JadwalMatrixExport implements FromCollection, ShouldAutoSize, WithEvents
{
    protected $rooms;
    protected $days;
    protected $order = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];

    public function __construct()
    {
        // Daftar ruang kelas unik
        $this->rooms = RuangKelas::pluck('nama_ruangan')
            ->unique()->values()->toArray();

        // Daftar hari unik, diurut sesuai $order
        $this->days = JadwalKuliah::select('hari')
            ->distinct()
            ->orderByRaw("FIELD(hari, '" . implode("','", $this->order) . "')")
            ->pluck('hari')
            ->toArray();
    }

    public function collection()
    {
        $rows = [];

        // Baris 1 reserve untuk judul (diisi di registerEvents)
        $rows[] = array_fill(0, 2 + count($this->rooms), '');

        foreach ($this->days as $hari) {
            // Baris nama hari
            $rows[] = array_merge(
                [$hari], // kolom A
                array_fill(1, 1 + count($this->rooms), '')
            );

            // Baris header kolom
            $rows[] = array_merge(['SESI', 'JAM'], $this->rooms);

            // Baris data sesi & jam
            $jamList = JadwalKuliah::where('hari', $hari)
                ->orderBy('jam')
                ->pluck('jam')
                ->unique()
                ->values()
                ->toArray();

            foreach ($jamList as $i => $jam) {
                $sesi = 'Sesi ' . ((int) floor($i / 2) + 1);
                $row  = [$sesi, $jam];
                foreach ($this->rooms as $ruang) {
                    $m = JadwalKuliah::where('hari', $hari)
                        ->where('jam', $jam)
                        ->where('nama_ruangan', $ruang)
                        ->first();
                    $row[] = $m ? $m->kode_mata_kuliah : '';
                }
                $rows[] = $row;
            }
        }

        return collect($rows);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $e) {
                $sheet    = $e->sheet->getDelegate();
                $colCount = 2 + count($this->rooms);  // kolom A,B + ruangan
                $lastCol  = Coordinate::stringFromColumnIndex($colCount);
                $lastRow  = $sheet->getHighestRow();

                // 1) Judul di baris 1, merge A1:Last1
                $sheet->setCellValue('A1', 'Jadwal Kuliah Prodi Teknologi Informasi');
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle('A1')
                      ->getFont()->setBold(true);
                $sheet->getStyle('A1')
                      ->getAlignment()->setHorizontal('center');

                // 2) Mulai styling blok per hari, mulai row 2
                $currentRow = 2;
                foreach ($this->days as $hari) {
                    // a) Merge nama hari di A{row}:Last{row}
                    $sheet->mergeCells("A{$currentRow}:{$lastCol}{$currentRow}");
                    $sheet->getStyle("A{$currentRow}")
                          ->getFont()->setBold(true);
                    $sheet->getStyle("A{$currentRow}")
                          ->getAlignment()->setHorizontal('center');

                    // b) Style header kolom di baris berikutnya
                    $hr = $currentRow + 1;
                    $style = $sheet->getStyle("A{$hr}:{$lastCol}{$hr}");
                    $style->getFont()->setBold(true);
                    $style->getAlignment()
                          ->setHorizontal('center')
                          ->setVertical('center');

                    // c) Hitung jumlah baris data sesi untuk hari ini
                    $jamCount = JadwalKuliah::where('hari', $hari)
                        ->pluck('jam')
                        ->unique()
                        ->count();

                    // d) Maju ke blok hari berikutnya
                    //    1 baris hari + 1 baris header + jamCount baris data
                    $currentRow += 2 + $jamCount;
                }
            },
        ];
    }
}
