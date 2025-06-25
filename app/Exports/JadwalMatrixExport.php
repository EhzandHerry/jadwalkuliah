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
    protected array $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    protected array $sessionRanges = [
        1 => ['07:00-07:50', '07:50-08:40'],
        2 => ['08:50-09:40', '09:40-10:30'],
        3 => ['10:40-11:30'],
        4 => ['12:10-13:10'],
        5 => ['13:20-14:10', '14:10-15:00'],
        6 => ['15:30-16:20', '16:20-17:10', '17:10-18:00'],
        7 => ['18:30-19:20', '19:20-20:10', '20:10-21:00'],
    ];

    protected array $breakSlots = [
        1 => ['time' => '08:40-08:50', 'text' => 'Pergantian Sesi'],
        2 => ['time' => '10:30-10:40', 'text' => 'Pergantian Sesi'],
        3 => ['time' => '11:30-12:20', 'text' => 'Pergantian Sesi'],
        4 => ['time' => '13:10-13:20', 'text' => 'Pergantian Sesi'],
        5 => ['time' => '15:00-15:30', 'text' => 'Pergantian Sesi'],
        6 => ['time' => '17:45-18:30', 'text' => 'Pergantian Sesi'],
    ];

    protected array $semesterColors = [
        2 => 'E2F0D9', // Hijau Muda
        4 => 'FDE9D9', // Orange Muda
        6 => 'DDEBF7', // Biru Muda
    ];

    // app/Exports/JadwalMatrixExport.php

    public function __construct()
    {
        // Ambil semua ruangan, diurutkan berdasarkan nama sebagai dasar
        $allRooms = RuangKelas::orderBy('nama_ruangan')->get();

        // Pisahkan ruangan berdasarkan prefix F6, F4, dan lainnya
        $f6_rooms = $allRooms->filter(fn($r) => str_starts_with($r->nama_ruangan, 'F6'));
        $f4_rooms = $allRooms->filter(fn($r) => str_starts_with($r->nama_ruangan, 'F4'));
        $other_rooms = $allRooms->reject(fn($r) => str_starts_with($r->nama_ruangan, 'F6') || str_starts_with($r->nama_ruangan, 'F4'));

        // Gabungkan kembali dengan urutan yang diinginkan: F6 -> F4 -> Lainnya
        $this->rooms = $f6_rooms->merge($f4_rooms)
            ->merge($other_rooms)
            ->pluck('nama_ruangan')
            ->toArray();
    }

    public function collection()
    {
        $rows = [];
        $totalCols = 2 + count($this->rooms);

        $rows[] = array_fill(0, $totalCols, '');

        foreach ($this->days as $hari) {
            $jadwalHari = JadwalKuliah::with(['mataKuliah', 'dosen'])
                                        ->where('hari', $hari)
                                        ->get();

            if ($jadwalHari->isEmpty()) {
                continue;
            }

            $maxSesiForDay = 0;
            foreach (array_reverse($this->sessionRanges, true) as $sesiNum => $slots) {
                foreach ($slots as $jam) {
                    [$start, $end] = explode('-', $jam);
                    $isAnyClassInSlot = $jadwalHari->first(function ($j) use ($start, $end) {
                        return substr($j->jam, 0, 5) < trim($end) && trim($start) < substr($j->jam, -5);
                    });

                    if ($isAnyClassInSlot) {
                        $maxSesiForDay = $sesiNum;
                        break 2;
                    }
                }
            }

            // --- STRUKTUR HEADER BARU (2 BARIS) ---
            $header_row1 = ['SESI', 'JAM', $hari];
            $rows[] = $header_row1;

            $header_row2 = ['', ''];
            $header_row2 = array_merge($header_row2, $this->rooms);
            $rows[] = $header_row2;

            foreach ($this->sessionRanges as $sesi => $slots) {
                $sessionOutput = [];
                $currentSessionHasClasses = false;

                foreach ($slots as $jam) {
                    [$start, $end] = explode('-', $jam);
                    $line = ["Sesi {$sesi}", $jam];

                    $rowHasData = false;
                    foreach ($this->rooms as $ruang) {
                        $jds = $jadwalHari
                            ->where('nama_ruangan', $ruang)
                            ->filter(fn($j) =>
                                substr($j->jam, 0, 5) < trim($end)
                                && trim($start) < substr($j->jam, -5)
                            );

                        if ($jds->isEmpty()) {
                            $line[] = '';
                        } else {
                            $rowHasData = true;
                            $cells = [];
                            foreach ($jds->groupBy(fn($j) => $j->kode_mata_kuliah . '|' . $j->unique_number) as $grp) {
                                $f = $grp->first();
                                $kl = $grp->pluck('kelas')->unique()->sort()->implode(',');
                                $mk = $f->mataKuliah->nama_matkul;
                                $dsn = $f->dosen->name;
                                
                                $semester = $f->mataKuliah->semester;
                                $prefix = "SEM{$semester}::";
                                $cells[] = $prefix . "{$f->kode_mata_kuliah}({$kl})\n{$mk}\nDosen: {$dsn}";
                            }
                            $line[] = implode("\n\n", $cells);
                        }
                    }

                    if ($rowHasData) {
                        $currentSessionHasClasses = true;
                        $sessionOutput[] = $line;
                    }
                }
                
                if ($currentSessionHasClasses) {
                    foreach ($sessionOutput as $outputLine) {
                        $rows[] = $outputLine;
                    }
                }

                if (isset($this->breakSlots[$sesi]) && $sesi < $maxSesiForDay && $currentSessionHasClasses) {
                    $breakInfo = $this->breakSlots[$sesi];
                    $rows[] = [
                        '', 
                        $breakInfo['time'],
                        $breakInfo['text'],
                    ];
                }
            }
        }

        return collect($rows);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $lastCol = Coordinate::stringFromColumnIndex(2 + count($this->rooms));
                $totalRow = $sheet->getHighestRow();

                // Judul utama
                $sheet->setCellValue('A1', 'JADWAL PERKULIAHAN SEMESTER GENAP PRODI TEKNOLOGI INFORMASI UMY 2024/2025');
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C00000']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER]
                ]);

                // Wrap text
                $sheet->getStyle("A2:{$lastCol}{$totalRow}")->getAlignment()->setWrapText(true);

                // Center kolom JAM (berlaku untuk semua, termasuk jam istirahat)
                $sheet->getStyle("B2:B{$totalRow}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

                // --- LOGIKA BARU UNTUK HEADER 2 BARIS ---
                for ($r = 2; $r <= $totalRow; $r++) {
                    $valA = trim((string)$sheet->getCell("A{$r}")->getValue());
                    $valB = trim((string)$sheet->getCell("B{$r}")->getValue());

                    if ($valA === 'SESI' && $valB === 'JAM') {
                        $topRow = $r;
                        $bottomRow = $r + 1;

                        // 1. Atur style untuk NAMA HARI
                        $sheet->mergeCells("C{$topRow}:{$lastCol}{$topRow}");
                        $sheet->getStyle("C{$topRow}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']]
                        ]);

                        // 2. Atur style untuk "SESI" (merge vertikal)
                        $sheet->mergeCells("A{$topRow}:A{$bottomRow}");
                        $sheet->getStyle("A{$topRow}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']]
                        ]);
                        
                        // 3. Atur style untuk "JAM" (merge vertikal)
                        $sheet->mergeCells("B{$topRow}:B{$bottomRow}");
                        $sheet->getStyle("B{$topRow}")->applyFromArray([
                            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F4E78']]
                        ]);

                        // 4. Atur style untuk NAMA RUANGAN
                        $sheet->getStyle("C{$bottomRow}:{$lastCol}{$bottomRow}")->applyFromArray([
                            'font' => ['bold' => true],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFF00']]
                        ]);
                        
                        $r++;
                    }
                }

                // Merge vertikal kolom SESI (untuk data Sesi 1, Sesi 2, dst)
                $startMergeRow = 0;
                for ($r = 3; $r <= $totalRow + 1; $r++) {
                    $val = trim((string)$sheet->getCell("A{$r}")->getValue());
                    if (str_starts_with($val, 'Sesi ')) {
                        if ($startMergeRow === 0) {
                            $startMergeRow = $r;
                        }
                    } else {
                        if ($startMergeRow !== 0) {
                            $endMergeRow = $r - 1;
                            if ($startMergeRow < $endMergeRow) {
                                $sheet->mergeCells("A{$startMergeRow}:A{$endMergeRow}");
                                $sheet->getStyle("A{$startMergeRow}")->getAlignment()
                                    ->setVertical(Alignment::VERTICAL_CENTER);
                            }
                            $startMergeRow = 0;
                        }
                    }
                }

                // Color "Pergantian Sesi" rows
                for ($r = 2; $r <= $totalRow; $r++) {
                    $val = trim((string)$sheet->getCell("C{$r}")->getValue());
                    if ($val === 'Pergantian Sesi') {
                        $sheet->mergeCells("C{$r}:{$lastCol}{$r}");
                        $sheet->getStyle("C{$r}")->applyFromArray([
                            'font'      => ['bold' => true],
                            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E0E0E0']]
                        ]);
                    }
                }

                // Warnai sel mata kuliah berdasarkan semester
                $maxColIndex = Coordinate::columnIndexFromString($lastCol);
                for ($col = 3; $col <= $maxColIndex; $col++) {
                    for ($row = 4; $row <= $totalRow; $row++) {
                        $cellCoordinate = Coordinate::stringFromColumnIndex($col) . $row;
                        $cell = $sheet->getCell($cellCoordinate);
                        $value = $cell->getValue();

                        if (is_string($value) && str_starts_with($value, 'SEM')) {
                            preg_match('/^SEM(\d+)::/', $value, $matches);
                            if (isset($matches[1])) {
                                $semester = (int) $matches[1];
                                if (isset($this->semesterColors[$semester])) {
                                    $color = $this->semesterColors[$semester];
                                    $sheet->getStyle($cellCoordinate)->getFill()->applyFromArray([
                                        'fillType' => Fill::FILL_SOLID,
                                        'startColor' => ['rgb' => $color]
                                    ]);
                                }
                                $cleanedValue = preg_replace('/^SEM(\d+)::/', '', $value);
                                $cell->setValue($cleanedValue);
                            }
                        }
                    }
                }

                // Beri border pada semua sel
                for ($row = 2; $row <= $totalRow; $row++) {
                    for ($col = 1; $col <= $maxColIndex; $col++) {
                        $sheet->getStyleByColumnAndRow($col, $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    }
                }
            },
        ];
    }
}