<?php

namespace App\Exports;

use App\Models\JadwalKuliah;
use App\Models\RuangKelas;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class JadwalMatrixExport implements FromCollection, ShouldAutoSize, WithEvents
{
    protected array $rooms;
    protected array $days;
    protected array $order = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

    /** slot per sesi */
    protected array $sessionRanges = [
        1 => ['07:00-07:50', '07:50-08:40'],
        2 => ['08:50-09:40', '09:40-10:30'],
        3 => ['10:40-11:30'],
        4 => ['12:10-13:10'],
        5 => ['13:20-14:10', '14:10-15:00'],
        6 => ['15:30-16:20', '16:20-17:10', '17:10-18:00'],
        7 => ['18:30-19:20', '19:20-20:10', '20:10-21:00'],
    ];

    /** jeda antar sesi */
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
        // daftar semua ruangan
        $this->rooms = RuangKelas::pluck('nama_ruangan')
                                 ->unique()
                                 ->values()
                                 ->toArray();

        // daftar hari, urut sesuai $this->order
        $this->days = JadwalKuliah::select('hari')
                        ->distinct()
                        ->orderByRaw("FIELD(hari,'" . implode("','", $this->order) . "')")
                        ->pluck('hari')
                        ->toArray();
    }

    public function collection()
    {
        $rows = [];
        $totalCols = 2 + count($this->rooms);

        // baris 1 kosong (untuk judul nanti)
        $rows[] = array_fill(0, $totalCols, '');

        foreach ($this->days as $hari) {
            // 1) Baris nama hari di kolom A, lalu blank sampai akhir
            $rows[] = array_pad([$hari], $totalCols, '');

            // 2) Header kolom: SESI | JAM | [ruangan...]
            $rows[] = array_merge(['SESI', 'JAM'], $this->rooms);

            // 3) Data sesi + break
            foreach ($this->sessionRanges as $sesi => $slots) {
                foreach ($slots as $jam) {
                    list($start, $end) = explode('-', $jam);
                    $row = ["Sesi {$sesi}", $jam];

                    foreach ($this->rooms as $ruang) {
                        $jadwals = JadwalKuliah::with('dosen', 'mataKuliah')
                            ->where('hari', $hari)
                            ->where('nama_ruangan', $ruang)
                            ->whereRaw("SUBSTR(jam,1,5) <= ?", [$end])
                            ->whereRaw("SUBSTR(jam,-5) >= ?", [$start])
                            ->get();

                        if ($jadwals->isEmpty()) {
                            $row[] = '';
                        } else {
                            $cells = [];
                            foreach ($jadwals->groupBy(fn($j) => $j->kode_mata_kuliah.'|'.$j->unique_number) as $items) {
                                $first   = $items->first();
                                $kode    = $first->kode_mata_kuliah;
                                $klas    = $items->pluck('kelas')->unique()->sort()->implode(',');
                                $namaMk  = $first->mataKuliah->nama_matkul;
                                $dosen   = optional($first->dosen)->nama
                                           ?? optional($first->dosen)->name
                                           ?? '–';

                                $cells[] = "{$kode}({$klas})\n{$namaMk}\nDosen: {$dosen}";
                            }
                            $row[] = implode("\n\n", $cells);
                        }
                    }

                    $rows[] = $row;
                }

                // 4) Jika ada jeda, sisipkan baris break
                if (isset($this->breakSlots[$sesi])) {
                    $rows[] = array_merge(
                        [''], 
                        [$this->breakSlots[$sesi]], 
                        array_fill(0, count($this->rooms), '')
                    );
                }
            }
        }

        return collect($rows);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet    = $event->sheet->getDelegate();
                $lastCol  = Coordinate::stringFromColumnIndex(2 + count($this->rooms));
                $totalRow = $sheet->getHighestRow();

                // — Judul Utama
                $sheet->setCellValue('A1', 'Jadwal Kuliah Prodi Teknologi Informasi');
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle('A1')->getFont()->setBold(true);
                $sheet->getStyle('A1')
                      ->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // — Wrap text di seluruh area data
                $sheet->getStyle("A3:{$lastCol}{$totalRow}")
                      ->getAlignment()
                      ->setWrapText(true);

                // — Center semua jam (kolom B)
                $sheet->getStyle("B4:B{$totalRow}")
                      ->getAlignment()
                      ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                      ->setVertical(Alignment::VERTICAL_CENTER);

                // hitung tinggi tiap blok hari
                $slotCnt   = array_sum(array_map('count', $this->sessionRanges));
                $breakCnt  = count($this->breakSlots);
                $blockSize = 1 + 1 + $slotCnt + $breakCnt;

                // — Styling masing-masing hari
                $r = 2;
                foreach ($this->days as $hari) {
                    // A) merge + center baris nama hari
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$r}")
                          ->getAlignment()
                          ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // B) styling header kolom
                    $hdr = $r + 1;
                    $sheet->getStyle("A{$hdr}:{$lastCol}{$hdr}")
                          ->getFont()->setBold(true);
                    $sheet->getStyle("A{$hdr}:{$lastCol}{$hdr}")
                          ->getAlignment()
                          ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                          ->setVertical(Alignment::VERTICAL_CENTER);

                    // C) merge vertikal “Sesi”
                    $pos = $r + 2;
                    foreach ($this->sessionRanges as $s => $slots) {
                        $len = count($slots);
                        $sheet->mergeCells("A{$pos}:A".($pos + $len - 1));
                        $sheet->getStyle("A{$pos}:A".($pos + $len - 1))
                              ->getAlignment()
                              ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                              ->setVertical(Alignment::VERTICAL_CENTER);
                        $pos += $len + 1;
                    }

                    // D) styling baris break
                    $pos = $r + 2;
                    foreach ($this->sessionRanges as $s => $slots) {
                        $pos += count($slots);
                        $sheet->mergeCells("B{$pos}:{$lastCol}{$pos}");
                        $sheet->getStyle("B{$pos}")->getFont()->setItalic(true);
                        $sheet->getStyle("B{$pos}")
                              ->getAlignment()
                              ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $pos++;
                    }

                    // Set same color for each day
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()->setFillType(Fill::FILL_SOLID);
                    $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()->getStartColor()->setARGB('D3D3D3');

                    // Apply borders to each cell with data
                    $this->applyBorders($sheet, $r, $totalRow, $lastCol);

                    $r += $blockSize;
                }
            },
        ];
    }

    private function applyBorders($sheet, $startRow, $totalRow, $lastCol)
    {
        // Apply borders to all cells with data
        for ($row = $startRow; $row <= $totalRow; $row++) {
            for ($col = 1; $col <= Coordinate::columnIndexFromString($lastCol); $col++) { // Fix: Ensure valid column range
                $sheet->getStyleByColumnAndRow($col, $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            }
        }
    }
}
