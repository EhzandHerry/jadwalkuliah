<?php

namespace App\Http\Controllers;

use App\Models\JadwalKuliah;
use App\Models\Kelas;
use App\Models\RuangKelas;
use App\Models\MataKuliah;
use Illuminate\Http\Request;
use App\Exports\JadwalExport;
use App\Exports\JadwalMatrixExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class JadwalController extends Controller
{
    public function index()
{
    // 1) Grab all kelas + mataKuliah + dosen + ruangKelas + (left‐join) jadwal
    $kelas = Kelas::with(['mataKuliah', 'dosen', 'ruangKelas'])
        ->leftJoin('jadwal', function ($join) {
            $join->on('kelas.kode_matkul', '=', 'jadwal.kode_mata_kuliah')
                 ->on('kelas.kelas',        '=', 'jadwal.kelas');
        })
        ->select(
            'kelas.*',
            'jadwal.id         as jadwal_id',
            'jadwal.nama_ruangan',
            'jadwal.hari',
            'jadwal.jam'
        )
        // **Tambah orderBy di sini supaya diurutkan berdasarkan kode_matakuliah**
        ->orderBy('kelas.kode_matkul', 'asc')
        ->get()
        // 2) Remove any rows whose mataKuliah has been deleted
        ->filter(function($k) {
            return $k->mataKuliah !== null;
        })
        ->values();  // reindex collection

    // 3) All ruangKelas for the dropdown
    $ruangKelasList = RuangKelas::all();

    // 4) Build availableTimes per dosen per hari
    $availableTimes = [];
    foreach ($kelas as $k) {
        if ($k->dosen && $k->dosen->available) {
            $uniq = $k->dosen->unique_number;
            if (! isset($availableTimes[$uniq])) {
                $availableTimes[$uniq] = $k->dosen->available
                    ->groupBy('hari')
                    ->map(function ($times) {
                        return $times->map(function ($item) {
                            return [
                                'start' => substr($item->start_time, 0, 5),
                                'end'   => substr($item->end_time,   0, 5),
                            ];
                        })->values()->all();
                    })
                    ->toArray();
            }
        }
    }

    // 5) Gather all existing jadwal into a flat array for the JS conflict checks
    $existingJadwals = JadwalKuliah::all()->map(function($j) {
        list($start, $end) = explode(' - ', $j->jam);
        return [
            'hari'   => $j->hari,
            'ruang'  => $j->nama_ruangan,
            'dosen'  => $j->unique_number,
            'start'  => $start,
            'end'    => $end,
            'matkul' => $j->kode_mata_kuliah,
        ];
    })->toArray();

    // 6) Pass everything to the view
    return view('admin.jadwal.index', compact(
        'kelas',
        'ruangKelasList',
        'availableTimes',
        'existingJadwals'
    ));
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
    $jadwal = JadwalKuliah::findOrFail($jadwalId);
    $ruangKelasList = RuangKelas::all();

    // Ambil available times dosen untuk jadwal ini (mirip index)
    $dosen = $jadwal->dosen;
    $availableTimes = [];
    if ($dosen && $dosen->available) {
        $availableTimes[$dosen->unique_number] = $dosen->available
            ->groupBy('hari')
            ->map(function ($times) {
                return $times->map(function ($item) {
                    return [
                        'start' => substr($item->start_time, 0, 5),
                        'end'   => substr($item->end_time, 0, 5),
                    ];
                })->values()->all();
            })
            ->toArray();
    }

    return view('admin.jadwal.edit', compact('jadwal', 'ruangKelasList', 'availableTimes'));
}


public function update(Request $request, $jadwalId)
{
    $jadwal = JadwalKuliah::findOrFail($jadwalId);

    $request->validate([
        'nama_ruangan' => 'required|string|exists:ruang_kelas,nama_ruangan',
        'hari'         => 'required|string',
        'jam'          => 'required|string',
    ]);

    // Cek bentrok jadwal (kecuali jadwal ini sendiri)
    $toMinutes = function ($t) {
        list($h, $m) = explode(':', substr($t, 0, 5));
        return $h * 60 + $m;
    };

    [$startNew, $endNew] = explode(' - ', $request->jam);

    $jadwalTabrakan = JadwalKuliah::where('hari', $request->hari)
        ->where('nama_ruangan', $request->nama_ruangan)
        ->where('id', '!=', $jadwal->id)
        ->get()
        ->filter(function ($j) use ($toMinutes, $startNew, $endNew) {
            [$s2, $e2] = explode(' - ', $j->jam);
            return ($toMinutes($startNew) < $toMinutes($e2))
                && ($toMinutes($endNew) > $toMinutes($s2));
        })->first();

    if ($jadwalTabrakan) {
        return redirect()->back()
            ->with('error', 'Jadwal bentrok dengan jadwal lain.')
            ->withInput();
    }

    $jadwal->update([
        'nama_ruangan' => $request->nama_ruangan,
        'hari'         => $request->hari,
        'jam'          => $request->jam,
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

}
