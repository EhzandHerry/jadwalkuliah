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

    /** slot per sesi */
    protected array $sessionRanges = [
        1 => ['07:00-07:50','07:50-08:40'],
        2 => ['08:50-09:40','09:40-10:30'],
        3 => ['10:40-11:30'],
        4 => ['12:10-13:10'],
        5 => ['13:20-14:10','14:10-15:00'],
        6 => ['15:30-16:20','16:20-17:10','17:10-18:00'],
        7 => ['18:30-19:20','19:20-20:10','20:10-21:00'],
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

        // daftar hari
        $this->days = JadwalKuliah::select('hari')
                        ->distinct()
                        ->orderByRaw("FIELD(hari,'".implode("','",$this->order)."')")
                        ->pluck('hari')
                        ->toArray();
    }

    public function collection()
    {
        $rows = [];

        // row kosong untuk judul nanti
        $rows[] = array_fill(0, 2 + count($this->rooms), '');

        foreach ($this->days as $hari) {
            // 1) Baris nama hari
            $rows[] = array_merge(
                [$hari],
                array_fill(1, 1 + count($this->rooms), '')
            );
            // 2) Header kolom
            $rows[] = array_merge(['SESI','JAM'], $this->rooms);

            // 3) Data sesi + break
            foreach ($this->sessionRanges as $sesi => $slots) {
                foreach ($slots as $jam) {
                    list($start, $end) = explode('-', $jam);
                    $row = ["Sesi {$sesi}", $jam];

                    foreach ($this->rooms as $ruang) {
                        // ambil semua jadwal di slot ini untuk ruangan tsb
                        $collection = JadwalKuliah::with('dosen')
                            ->where('hari', $hari)
                            ->where('nama_ruangan', $ruang)
                            ->whereRaw("SUBSTR(jam,1,5) <= ?", [$end])
                            ->whereRaw("SUBSTR(jam,-5) >= ?", [$start])
                            ->get();

                        if ($collection->isEmpty()) {
                            $row[] = '';
                        } else {
                            // grup per matkul+unique_number (dosen sama)
                            $grouped = $collection->groupBy(function($j) {
                                return $j->kode_mata_kuliah.'|'.$j->unique_number;
                            });

                            $texts = [];
                            foreach ($grouped as $items) {
                                $kode  = $items->first()->kode_mata_kuliah;
                                $klases = $items->pluck('kelas')->unique()->sort()->implode(',');
                                $dosen = $items->first()->dosen
                                          ? ($items->first()->dosen->nama ?? $items->first()->dosen->name)
                                          : '–';
                                $texts[] = "{$kode}({$klases}) Dosen: {$dosen}";
                            }

                            $row[] = implode("\n", $texts);
                        }
                    }

                    $rows[] = $row;
                }

                // sisipkan baris break jika perlu
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
            AfterSheet::class => function(AfterSheet $e) {
                $sheet   = $e->sheet->getDelegate();
                $colCnt  = 2 + count($this->rooms);
                $lastCol = Coordinate::stringFromColumnIndex($colCnt);

                // -- Judul di baris 1 --
                $sheet->setCellValue('A1', 'Jadwal Kuliah Prodi Teknologi Informasi');
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->getStyle('A1')->getFont()->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // hitung tinggi setiap blok hari
                $slotCnt   = array_sum(array_map('count', $this->sessionRanges));
                $breakCnt  = count($this->breakSlots);
                $blockSize = 1 + 1 + $slotCnt + $breakCnt;

                // styling untuk tiap hari
                $r = 2;
                foreach ($this->days as $hari) {
                    // a) merge A..Last di baris nama hari
                    $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
                    $sheet->getStyle("A{$r}")
                          ->getFont()->setBold(true);
                    $sheet->getStyle("A{$r}")
                          ->getAlignment()->setHorizontal('center');

                    // b) styling header kolom
                    $h = $r + 1;
                    $sheet->getStyle("A{$h}:{$lastCol}{$h}")
                          ->getFont()->setBold(true);
                    $sheet->getStyle("A{$h}:{$lastCol}{$h}")
                          ->getAlignment()
                          ->setHorizontal('center')
                          ->setVertical('center');

                    // c) merge vertikal kolom "Sesi" hanya pada baris slot
                    $m = $r + 2;
                    foreach ($this->sessionRanges as $s => $slots) {
                        $len = count($slots);
                        $sheet->mergeCells("A{$m}:A".($m+$len-1));
                        $sheet->getStyle("A{$m}:A".($m+$len-1))
                              ->getAlignment()
                              ->setHorizontal('center')
                              ->setVertical('center');
                        $m += $len + 1; // lompat slot + break
                    }

                    // d) styling baris break
                    $b = $r + 2;
                    foreach ($this->sessionRanges as $s => $slots) {
                        $b += count($slots);
                        $sheet->mergeCells("B{$b}:{$lastCol}{$b}");
                        $sheet->getStyle("B{$b}")
                              ->getFont()->setItalic(true);
                        $sheet->getStyle("B{$b}")
                              ->getAlignment()->setHorizontal('center');
                        $b++;
                    }

                    // e) pindah ke blok hari berikutnya
                    $r += $blockSize;
                }
            },
        ];
    }
}
