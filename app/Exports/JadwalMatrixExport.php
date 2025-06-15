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
    protected array $rooms;
    protected array $days;
    protected array $order = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu'];

    // definisi slot per sesi
    protected array $sessionRanges = [
        1 => ['07:00-07:50','07:50-08:40'],
        2 => ['08:50-09:40','09:40-10:30'],
        3 => ['10:40-11:30'],
        4 => ['12:10-13:10'],
        5 => ['13:20-14:10','14:10-15:00'],
        6 => ['15:30-16:20','16:20-17:10','17:10-18:00'],
        7 => ['18:30-19:20','19:20-20:10','20:10-21:00'],
    ];

    // jeda antar sesi: teks "[jam]  Pergantian Sesi"
    protected array $breakSlots = [
        1 => '08:40-08:50  Pergantian Sesi',
        2 => '10:30-10:40  Pergantian Sesi',
        3 => '11:30-12:20  Pergantian Sesi',
        4 => '13:10-13:20  Pergantian Sesi',
        5 => '15:00-15:30  Pergantian Sesi',
        6 => '17:45-18:30  Pergantian Sesi',
    ];

    public function __construct()
    {
        $this->rooms = RuangKelas::pluck('nama_ruangan')
            ->unique()->values()->toArray();

        $this->days = JadwalKuliah::select('hari')
            ->distinct()
            ->orderByRaw("FIELD(hari,'".implode("','",$this->order)."')")
            ->pluck('hari')
            ->toArray();
    }

    public function collection()
    {
        $rows = [];

        // row 1 kosong (judul akan di-set di AfterSheet)
        $rows[] = array_fill(0, 2 + count($this->rooms), '');

        foreach ($this->days as $hari) {
            // baris nama hari
            $rows[] = array_merge(
                [$hari],
                array_fill(1, 1 + count($this->rooms), '')
            );

            // baris header kolom
            $rows[] = array_merge(['SESI','JAM'], $this->rooms);

            // sesi + break
            foreach ($this->sessionRanges as $sesi => $slots) {
                // tiap slot jam dalam sesi
                foreach ($slots as $jam) {
                    $row = ["Sesi {$sesi}", $jam];
                    list($start, $end) = explode('-', $jam);

                    // isi kode matkul jika overlap
                    foreach ($this->rooms as $ruang) {
                        $m = JadwalKuliah::where('hari', $hari)
                            ->where('nama_ruangan', $ruang)
                            ->whereRaw("SUBSTR(jam,1,5) <= ?", [$end])
                            ->whereRaw("SUBSTR(jam,-5) >= ?", [$start])
                            ->first();
                        $row[] = $m ? $m->kode_mata_kuliah : '';
                    }

                    $rows[] = $row;
                }

                // setelah sesi, sisipkan baris break
                if (isset($this->breakSlots[$sesi])) {
                    $rows[] = array_merge(
                        [''],                                   // kolom A kosong
                        [$this->breakSlots[$sesi]],            // kolom B: "[jam] Pergantian Sesi"
                        array_fill(0, count($this->rooms), '') // sisanya kosong
                    );
                }
            }
        }

        return collect($rows);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $e) {
                $sheet    = $e->sheet->getDelegate();
                $colCount = 2 + count($this->rooms);
                $lastCol  = Coordinate::stringFromColumnIndex($colCount);

                //
                // 1) Judul di baris 1
                //
                $sheet->setCellValue('A1', 'Jadwal Kuliah Prodi Teknologi Informasi');
                $sheet->mergeCells("A1:{$lastCol}1");
                // bold + center
                $sheet->getStyle("A1")->getFont()->setBold(true);
                $sheet->getStyle("A1")->getAlignment()->setHorizontal('center');

                //
                // 2) Hitung tinggi block per hari: hari + header + semua slot + break
                //
                $slotCount  = array_sum(array_map('count', $this->sessionRanges));
                $breakCount = count($this->breakSlots);
                $blockSize  = 1 /*hari*/ + 1 /*header*/ + $slotCount + $breakCount;

                //
                // 3) Loop styling per‐hari
                //
                $startRow = 2;
                foreach ($this->days as $hari) {
                    // a) merge entire A..Last pada baris nama hari
                    $sheet->mergeCells("A{$startRow}:{$lastCol}{$startRow}");
                    $sheet->getStyle("A{$startRow}")
                          ->getFont()->setBold(true);
                    $sheet->getStyle("A{$startRow}")
                          ->getAlignment()->setHorizontal('center');

                    // b) bold & center header kolom
                    $hdr = $startRow + 1;
                    $sheet->getStyle("A{$hdr}:{$lastCol}{$hdr}")
                          ->getFont()->setBold(true);
                    $sheet->getStyle("A{$hdr}:{$lastCol}{$hdr}")
                          ->getAlignment()->setHorizontal('center')
                                          ->setVertical('center');

                    // c) merge vertikal SESI hanya di baris‐baris slot (lewat break)
                    $r = $startRow + 2;
                    foreach ($this->sessionRanges as $sesi => $slots) {
                        $len = count($slots);
                        $sheet->mergeCells("A{$r}:A".($r + $len - 1));
                        $sheet->getStyle("A{$r}:A".($r + $len - 1))
                              ->getAlignment()->setHorizontal('center')
                                              ->setVertical('center');
                        $r += $len + 1; // lompat slot + break
                    }

                    // d) styling baris break: merge B..Last, italic + center
                    $r = $startRow + 2;
                    foreach ($this->sessionRanges as $sesi => $slots) {
                        $r += count($slots);
                        $sheet->mergeCells("B{$r}:{$lastCol}{$r}");
                        $sheet->getStyle("B{$r}")
                              ->getFont()->setItalic(true);
                        $sheet->getStyle("B{$r}")
                              ->getAlignment()->setHorizontal('center');
                        $r++;
                    }

                    // e) geser ke hari berikutnya
                    $startRow += $blockSize;
                }
            },
        ];
    }
}
