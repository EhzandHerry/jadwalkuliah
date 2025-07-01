<?php

namespace App\Http\Controllers;

use App\Models\JadwalKuliah;
use App\Models\Kelas;
use App\Models\RuangKelas;
use App\Models\MataKuliah;
use App\Models\Available;
use Illuminate\Http\Request;
use App\Exports\JadwalExport;
use App\Exports\JadwalMatrixExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;


class JadwalController extends Controller
{
    public function index(Request $request)
    {
        // 1) Ambil input dari form, beri nilai default 'genap' untuk semester
        $search = $request->input('search');
        $semesterType = $request->input('semester_type', 'genap'); // Perubahan di sini

        // 2) Mulai query builder
        $kelasQuery = Kelas::query();

        // Terapkan filter berdasarkan semester (sekarang akan selalu ada nilainya)
        if ($semesterType === 'gasal') {
            $kelasQuery->whereHas('mataKuliah', function ($query) {
                $query->whereRaw('semester % 2 != 0');
            });
        } elseif ($semesterType === 'genap') {
            $kelasQuery->whereHas('mataKuliah', function ($query) {
                $query->whereRaw('semester % 2 = 0');
            });
        }

        // Terapkan filter pencarian nama matakuliah jika diisi
        if ($search) {
            $kelasQuery->whereHas('mataKuliah', function ($query) use ($search) {
                $query->where('nama_matkul', 'like', '%' . $search . '%');
            });
        }

        // 3) Lanjutkan query yang sudah ada (tidak ada perubahan di sisa method)
        $kelas = $kelasQuery->with(['mataKuliah', 'dosen', 'ruangKelas'])
            ->leftJoin('jadwal', function ($join) {
                $join->on('kelas.kode_matkul', '=', 'jadwal.kode_mata_kuliah')
                     ->on('kelas.kelas', '=', 'jadwal.kelas');
            })
            ->select('kelas.*', 'jadwal.id as jadwal_id', 'jadwal.nama_ruangan', 'jadwal.hari', 'jadwal.jam')
            ->orderBy('kelas.kode_matkul', 'asc')
            ->get()
            ->filter(fn($k) => $k->mataKuliah !== null)
            ->values();
        
        // ... sisa method index() biarkan sama persis ...

        $ruangKelasList = RuangKelas::orderBy('nama_ruangan', 'asc')->get();
        $availableTimes = [];
        foreach ($kelas as $k) {
            if ($k->dosen && $k->dosen->available) {
                $uniq = $k->dosen->unique_number;
                if (!isset($availableTimes[$uniq])) {
                    $availableTimes[$uniq] = $k->dosen->available
                        ->groupBy('hari')
                        ->map(fn($times) => $times->map(fn($item) => substr($item->start_time, 0, 5) . ' - ' . substr($item->end_time, 0, 5))->values()->all())
                        ->toArray();
                }
            }
        }
        $existingJadwals = JadwalKuliah::all()->map(function($j) {
            list($start, $end) = explode(' - ', $j->jam);
            return ['hari' => $j->hari, 'ruang' => $j->nama_ruangan, 'dosen' => $j->unique_number, 'start' => $start, 'end' => $end, 'matkul' => $j->kode_mata_kuliah];
        })->toArray();
        return view('admin.jadwal.index', compact('kelas', 'ruangKelasList', 'availableTimes', 'existingJadwals'));
    }

public function assignRuang(Request $request, $kelasId)
{
    // 1) Load Kelas + MataKuliah + Dosen
    $kelas = Kelas::with(['mataKuliah','dosen'])->findOrFail($kelasId);

    // 2) Pastikan dosen sudah di‐assign
    if (! $kelas->unique_number) {
        return redirect()->route('admin.jadwal.index')
                         ->with('error', "Dosen untuk “{$kelas->mataKuliah->nama_matkul}” (Kelas {$kelas->kelas}) belum dipilih.");
    }

    // 3) Validasi input
    $data = $request->validate([
        'nama_ruangan' => 'required|string|exists:ruang_kelas,nama_ruangan',
        'hari'         => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
        'jam'          => ['required','regex:/^\d{2}:\d{2}\s-\s\d{2}:\d{2}$/'],
    ],[
        'jam.regex' => 'Format jam harus "HH:mm - HH:mm", misal "07:00 - 07:50".'
    ]);

    // 4) Daftar sesi master
    $sessions = [
      "07:00 - 07:50","07:50 - 08:40","08:50 - 09:40","09:40 - 10:30",
      "10:40 - 11:30","12:10 - 13:10","13:20 - 14:10","14:10 - 15:00",
      "15:30 - 16:20","16:20 - 17:10","17:10 - 18:00","18:30 - 19:20",
      "19:20 - 20:10","20:10 - 21:00",
    ];

    // 5) Hitung start/end berdasarkan SKS
    $startIdx = array_search($data['jam'], $sessions);
    if ($startIdx === false) {
        return redirect()->route('admin.jadwal.index')
                         ->with('error', 'Sesi jam yang dipilih tidak valid.');
    }
    $endIdx = min($startIdx + ($kelas->mataKuliah->sks - 1), count($sessions) - 1);

    list($sNew)      = explode(' - ', $sessions[$startIdx]);
    [,       $eNew] = explode(' - ', $sessions[$endIdx]);
    $jamRange       = "{$sNew} - {$eNew}";

    // helper untuk overlap interval
    $overlaps = function($oldRange) use($sNew, $eNew) {
        list($sOld, $eOld) = explode(' - ', $oldRange);
        return !($eOld <= $sNew || $eNew <= $sOld);
    };

    // 6) Capacity‐check untuk MATKUL+DOSEN yang sama di RUANG yang sama
    $ruang     = RuangKelas::where('nama_ruangan', $data['nama_ruangan'])->first();
    $usedCount = JadwalKuliah::where('hari', $data['hari'])
        ->where('nama_ruangan', $data['nama_ruangan'])
        ->where('kode_mata_kuliah', $kelas->kode_matkul)
        ->where('unique_number',    $kelas->unique_number)
        ->get()
        ->filter(fn($row) => $overlaps($row->jam))
        ->count();

    if ($usedCount >= $ruang->kapasitas_kelas) {
        return redirect()->route('admin.jadwal.index')
                         ->with('error', "Kapasitas ruangan “{$data['nama_ruangan']}” untuk matakuliah & dosen ini sudah penuh ({$ruang->kapasitas_kelas}).");
    }

    // 7) Overlap‐check Umum
    $allToday     = JadwalKuliah::where('hari', $data['hari'])->get();
    $semesterBaru = $kelas->mataKuliah->semester;
    $kelasBaru    = $kelas->kelas;

    foreach ($allToday as $old) {
        if (! $overlaps($old->jam)) {
            continue;
        }

        // 7a) Dosen bentrok (hanya jika matkul beda)
        if ($old->unique_number === $kelas->unique_number
         && $old->kode_mata_kuliah !== $kelas->kode_matkul
        ) {
            return redirect()->route('admin.jadwal.index')
                             ->with('error', 'Jadwal bentrok: dosen sudah memiliki jadwal di slot ini.');
        }

        // 7b) Ruang bentrok (kecuali jika matkul & dosen sama)
        if ($old->nama_ruangan === $data['nama_ruangan']
         && !($old->kode_mata_kuliah === $kelas->kode_matkul
           && $old->unique_number    === $kelas->unique_number)
        ) {
            return redirect()->route('admin.jadwal.index')
                             ->with('error', 'Jadwal bentrok: ruang sudah dipakai pada slot ini.');
        }

        // 7c) Mahasiswa bentrok: kelas+semester sama
        $mkLamaSemester = \App\Models\MataKuliah::where('kode_matkul', $old->kode_mata_kuliah)
                                ->value('semester');
        $kelasLama = $old->kelas;
        if ($mkLamaSemester === $semesterBaru && $kelasLama === $kelasBaru) {
            return redirect()->route('admin.jadwal.index')
                             ->with('error', "Jadwal bentrok: mahasiswa Kelas {$kelasBaru} Semester {$semesterBaru} sudah memiliki mata kuliah lain pada slot ini.");
        }
    }

    // 8) Simpan jadwal
    JadwalKuliah::create([
        'hari'               => $data['hari'],
        'kode_mata_kuliah'   => $kelas->kode_matkul,
        'kelas'              => $kelas->kelas,
        'nama_ruangan'       => $data['nama_ruangan'],
        'unique_number'      => $kelas->unique_number,
        'jam'                => $jamRange,
    ]);

    return redirect()->route('admin.jadwal.index')
                     ->with('success', "Jadwal berhasil ditambahkan ({$jamRange}).");
}

    // Form edit jadwal
    public function edit($jadwalId)
    {
        $jadwal = JadwalKuliah::with('dosen', 'mataKuliah')->findOrFail($jadwalId);
        
        // Ambil semua ruang kelas untuk dropdown
        $ruangKelasList = RuangKelas::orderBy('nama_ruangan')->get();

        // Buat map kapasitas ruangan untuk digunakan di JavaScript
        $roomCapacities = $ruangKelasList->pluck('kapasitas_kelas', 'nama_ruangan');

        // Ambil available times dosen untuk jadwal ini
        $dosen = $jadwal->dosen;
        $availableTimes = [];
        if ($dosen && $dosen->available) {
            $availableTimes[$dosen->unique_number] = $dosen->available
                ->groupBy('hari')
                ->mapWithKeys(function ($times, $hari) {
                    return [$hari => $times->map(function ($item) {
                        return substr($item->start_time, 0, 5) . ' - ' . substr($item->end_time, 0, 5);
                    })->values()->all()];
                })
                ->toArray();
        }

        // Ambil semua jadwal lain untuk pengecekan konflik, KECUALI jadwal yang sedang diedit ini
        // Kita sertakan info penting untuk validasi kompleks
        $existingJadwals = JadwalKuliah::where('id', '!=', $jadwalId)
            ->get()
            ->map(function($j) {
                list($start, $end) = explode(' - ', $j->jam);
                return [
                    'hari'  => $j->hari,
                    'ruang' => $j->nama_ruangan,
                    'start' => trim($start),
                    'end'   => trim($end),
                    'kode_mata_kuliah' => $j->kode_mata_kuliah,
                    'unique_number' => $j->unique_number,
                ];
            })->toArray();

        return view('admin.jadwal.edit', compact(
            'jadwal', 
            'ruangKelasList', 
            'availableTimes',
            'existingJadwals',
            'roomCapacities' // Kirim data kapasitas ke view
        ));
    }


public function update(Request $request, $jadwalId)
{
    $jadwal = JadwalKuliah::findOrFail($jadwalId);

    // 1) Validasi input
    $data = $request->validate([
        'nama_ruangan' => 'required|string|exists:ruang_kelas,nama_ruangan',
        'hari'         => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat, Sabtu',
        'jam'          => ['required','regex:/^\d{2}:\d{2}\s-\s\d{2}:\d{2}$/'],
    ],[
        'jam.regex' => 'Format jam harus "HH:mm - HH:mm".'
    ]);

    // 2) Ambil entitas Kelas untuk SKS + semester + kelas
    $kelas = Kelas::with('mataKuliah')
        ->where('kode_matkul', $jadwal->kode_mata_kuliah)
        ->where('kelas',        $jadwal->kelas)
        ->firstOrFail();

    $sks          = $kelas->mataKuliah->sks;
    $semesterBaru = $kelas->mataKuliah->semester;
    $kelasBaru    = $kelas->kelas;

    // 3) Daftar sesi
    $sessions = [
        "07:00 - 07:50","07:50 - 08:40","08:50 - 09:40","09:40 - 10:30",
        "10:40 - 11:30","12:10 - 13:10","13:20 - 14:10","14:10 - 15:00",
        "15:30 - 16:20","16:20 - 17:10","17:10 - 18:00","18:30 - 19:20",
        "19:20 - 20:10","20:10 - 21:00",
    ];

    // 4) Hitung startIdx & endIdx berdasar SKS
    $startIdx = array_search($data['jam'], $sessions);
    if ($startIdx === false) {
        return redirect()->back()
                         ->with('error', 'Sesi jam yang dipilih tidak valid.')
                         ->withInput();
    }
    $endIdx = min($startIdx + ($sks - 1), count($sessions) - 1);

    // 5) Bangun jamRange baru
    list($sNew)      = explode(' - ', $sessions[$startIdx]);
    [,       $eNew] = explode(' - ', $sessions[$endIdx]);
    $jamRange       = "{$sNew} - {$eNew}";

    // Helper konversi ke menit
    $toMin = function($t) {
        [$h,$m] = explode(':', substr($t,0,5));
        return $h * 60 + $m;
    };

    // Helper cek overlap
    $overlaps = function($oldRange) use($toMin, $sNew, $eNew) {
        list($sOld, $eOld) = explode(' - ', $oldRange);
        return ! (
            $toMin($eOld) <= $toMin($sNew) ||
            $toMin($eNew) <= $toMin($sOld)
        );
    };

    // 6) Ambil semua jadwal hari itu kecuali ini
    $others = JadwalKuliah::where('hari', $data['hari'])
        ->where('id', '!=', $jadwal->id)
        ->get();

    // 7) Loop untuk cek bentrok
    foreach ($others as $old) {
        if (! $overlaps($old->jam)) {
            continue;
        }

        // 7a) Ruang bentrok?
        if ($old->nama_ruangan === $data['nama_ruangan']
            && !($old->kode_mata_kuliah === $jadwal->kode_mata_kuliah
              && $old->unique_number    === $jadwal->unique_number)
        ) {
            return redirect()->back()
                ->with('error', 'Jadwal bentrok: ruang sudah dipakai pada slot ini.')
                ->withInput();
        }

        // 7b) Dosen bentrok (jika matkul berbeda)
        if ($old->unique_number === $jadwal->unique_number
            && $old->kode_mata_kuliah !== $jadwal->kode_mata_kuliah
        ) {
            return redirect()->back()
                ->with('error', 'Jadwal bentrok: dosen sudah memiliki jadwal di slot ini.')
                ->withInput();
        }

        // 7c) Mahasiswa bentrok: kelas+semester sama
        $mkLamaSemester = MataKuliah::where('kode_matkul', $old->kode_mata_kuliah)
                                ->value('semester');
        if ($mkLamaSemester === $semesterBaru && $old->kelas === $kelasBaru) {
            return redirect()->back()
                ->with('error', "Jadwal bentrok: mahasiswa Kelas {$kelasBaru} Semester {$semesterBaru} sudah memiliki mata kuliah lain pada slot ini.")
                ->withInput();
        }
    }

    // 8) Simpan perubahan
    $jadwal->update([
        'nama_ruangan' => $data['nama_ruangan'],
        'hari'         => $data['hari'],
        'jam'          => $jamRange,
    ]);

    return redirect()->route('admin.jadwal.index')
                     ->with('success', 'Jadwal berhasil diperbarui.');
}


public function destroy($jadwalId)
{
    $jadwal = JadwalKuliah::findOrFail($jadwalId);
    $jadwal->delete();

    return redirect()->route('admin.jadwal.index')
        ->with('success', 'Jadwal berhasil dihapus.');
}

public function exportExcel()
{
    $fileName = 'jadwal_' . date('Ymd_His') . '.xlsx';
    return Excel::download(new JadwalExport, $fileName);
}

public function exportMatrix()
    {
        $fileName = 'jadwal_matrix_'.date('Ymd_His').'.xlsx';
        return Excel::download(new JadwalMatrixExport, $fileName);
    }


// app/Http/Controllers/JadwalController.php

// app/Http/Controllers/JadwalController.php

public function previewMatrix()
{
    // --- PERUBAHAN LOGIKA PENGAMBILAN RUANGAN ---
    // Ambil semua ruangan, diurutkan berdasarkan nama sebagai dasar
    $allRoomsCollection = RuangKelas::orderBy('nama_ruangan')->get();

    // Pisahkan ruangan berdasarkan prefix F6, F4, dan lainnya
    $f6_rooms = $allRoomsCollection->filter(fn($r) => str_starts_with($r->nama_ruangan, 'F6'));
    $f4_rooms = $allRoomsCollection->filter(fn($r) => str_starts_with($r->nama_ruangan, 'F4'));
    $other_rooms = $allRoomsCollection->reject(fn($r) => str_starts_with($r->nama_ruangan, 'F6') || str_starts_with($r->nama_ruangan, 'F4'));

    // Gabungkan kembali dengan urutan yang diinginkan dan ambil namanya saja
    $rooms = $f6_rooms->merge($f4_rooms)
        ->merge($other_rooms)
        ->pluck('nama_ruangan')
        ->toArray();
    // --- AKHIR PERUBAHAN ---

    // Definisi dasar lainnya tetap sama
    $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $sessionRanges = [
        1 => ['07:00-07:50', '07:50-08:40'],
        2 => ['08:50-09:40', '09:40-10:30'],
        3 => ['10:40-11:30'],
        4 => ['12:10-13:10'],
        5 => ['13:20-14:10', '14:10-15:00'],
        6 => ['15:30-16:20', '16:20-17:10', '17:10-18:00'],
        7 => ['18:30-19:20', '19:20-20:10', '20:10-21:00'],
    ];
    $breakSlots = [
        1 => ['time' => '08:40-08:50', 'text' => 'Pergantian Sesi'],
        2 => ['time' => '10:30-10:40', 'text' => 'Pergantian Sesi'],
        3 => ['time' => '11:30-12:20', 'text' => 'Pergantian Sesi'],
        4 => ['time' => '13:10-13:20', 'text' => 'Pergantian Sesi'],
        5 => ['time' => '15:00-15:30', 'text' => 'Pergantian Sesi'],
        6 => ['time' => '17:45-18:30', 'text' => 'Pergantian Sesi'],
    ];
    $semesterColors = [
        2 => 'E2F0D9',
        4 => 'FDE9D9',
        6 => 'DDEBF7',
    ];

    $allJadwal = JadwalKuliah::with(['mataKuliah', 'dosen'])->get();

    $dailyData = [];
    $dailyMaxSessions = [];

    foreach ($days as $hari) {
        $jadwalHari = $allJadwal->where('hari', $hari);
        if ($jadwalHari->isEmpty()) continue;

        $maxSesiForDay = 0;
        foreach (array_reverse($sessionRanges, true) as $sesiNum => $slots) {
            foreach ($slots as $jam) {
                [$start, $end] = explode('-', $jam);
                $isAnyClassInSlot = $jadwalHari->first(function ($j) use ($start, $end) {
                    [$os, $oe] = explode(' - ', $j->jam);
                    return !(trim($oe) <= trim($start) || trim($end) <= trim($os));
                });
                if ($isAnyClassInSlot) {
                    $maxSesiForDay = $sesiNum;
                    break 2;
                }
            }
        }
        $dailyMaxSessions[$hari] = $maxSesiForDay;

        $matrix = [];
        foreach ($sessionRanges as $sesi => $slots) {
            if ($sesi > $maxSesiForDay) continue;
            foreach ($slots as $jam) {
                foreach ($rooms as $ruang) {
                    $jadwals = $jadwalHari
                        ->where('nama_ruangan', $ruang)
                        ->filter(function ($j) use ($jam) {
                            [$s, $e] = explode('-', $jam);
                            [$os, $oe] = explode(' - ', $j->jam);
                            return !(trim($oe) <= trim($s) || trim($e) <= trim($os));
                        });
                    if (!$jadwals->isEmpty()) {
                        $groups = $jadwals->groupBy(fn($j) => $j->kode_mata_kuliah . '|' . $j->unique_number);
                        foreach ($groups as $items) {
                            $first = $items->first();
                            $kelasList = $items->pluck('kelas')->unique()->sort()->implode(',');
                            $text = "{$first->kode_mata_kuliah}({$kelasList})\n{$first->mataKuliah->nama_matkul}\nDosen: {$first->dosen->name}";
                            $matrix[$sesi][$jam][$ruang][] = [
                                'text' => $text,
                                'semester' => $first->mataKuliah->semester
                            ];
                        }
                    }
                }
            }
        }
        $dailyData[$hari] = $matrix;
    }

    return view('admin.jadwal.matrix_preview', compact(
        'rooms', 'days', 'dailyData', 'dailyMaxSessions', 'sessionRanges', 'breakSlots', 'semesterColors'
    ));
}

}


