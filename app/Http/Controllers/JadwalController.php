<?php

namespace App\Http\Controllers;

use App\Models\JadwalKuliah;
use App\Models\Kelas;
use App\Models\RuangKelas;
use Illuminate\Http\Request;
use App\Exports\JadwalExport;
use App\Exports\JadwalMatrixExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class JadwalController extends Controller
{
    public function index()
    {
        // 1) Ambil daftar kelas beserta relasi mataKuliah, dosen, dan (jika ada) jadwal yang sudah ter-join
        $kelas = Kelas::with(['mataKuliah', 'dosen', 'ruangKelas'])
            ->leftJoin('jadwal', function ($join) {
                $join->on('kelas.kode_matkul', '=', 'jadwal.kode_mata_kuliah')
                     ->on('kelas.kelas',        '=', 'jadwal.kelas');
            })
            ->select('kelas.*', 
                     'jadwal.id        as jadwal_id',
                     'jadwal.nama_ruangan',
                     'jadwal.hari',
                     'jadwal.jam')
            ->get();

        // 2) Ambil semua RuangKelas untuk dropdown di form
        $ruangKelasList = RuangKelas::all();

        // 3) Siapkan availableTimes per dosen per hari (dari tabel availability dosen)
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

        // 4) Ambil semua jadwal yang sudah terdaftar di database
        //    Untuk setiap entry kita pecah menjadi hari, ruang, start, end, dan dosen
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

        // 5) Kirim ke view
        return view('admin.jadwal.index', compact(
            'kelas',
            'ruangKelasList',
            'availableTimes',
            'existingJadwals'
        ));
    }


public function assignRuang(Request $request, $kelasId)
{
    // — 1) Ambil entitas Kelas + MataKuliah + Dosen
    $kelas = Kelas::with(['mataKuliah','dosen'])->findOrFail($kelasId);

    // — 2) Pastikan dosen sudah di-assign
    if (! $kelas->unique_number) {
        return redirect()->route('admin.jadwal.index')
            ->with('error', "Dosen untuk “{$kelas->mataKuliah->nama_matkul}” (Kelas {$kelas->kelas}) belum dipilih.");
    }

    // — 3) Validasi input
    $data = $request->validate([
        'nama_ruangan' => 'required|string|exists:ruang_kelas,nama_ruangan',
        'hari'         => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
        'jam'          => ['required','regex:/^\d{2}:\d{2}\s-\s\d{2}:\d{2}$/'],
    ],[
        'jam.regex' => 'Format jam harus "HH:mm - HH:mm", misal "07:00 - 07:50".'
    ]);

    // — 4) Daftar sesi (harus sesuai dropdown di view)
    $sessions = [
      "07:00 - 07:50","07:50 - 08:40","08:50 - 09:40","09:40 - 10:30",
      "10:40 - 11:30","12:10 - 13:10","13:20 - 14:10","14:10 - 15:00",
      "15:30 - 16:20","16:20 - 17:10","17:10 - 18:00","18:30 - 19:20",
      "19:20 - 20:10","20:10 - 21:00"
    ];

    // — 5) Hitung sesi mulai & akhir berdasar SKS
    $startIdx = array_search($data['jam'], $sessions);
    if ($startIdx === false) {
        return redirect()->route('admin.jadwal.index')
            ->with('error', 'Sesi jam yang dipilih tidak valid.');
    }
    $endIdx = min($startIdx + ($kelas->mataKuliah->sks - 1), count($sessions) - 1);

    list($sNew)      = explode(' - ', $sessions[$startIdx]);
    [,       $eNew] = explode(' - ', $sessions[$endIdx]);
    $jamRange = "{$sNew} - {$eNew}";

    // — 6) Capacity‐check untuk matkul+dosen yang sama
    $ruang     = RuangKelas::where('nama_ruangan', $data['nama_ruangan'])->first();
    $already   = JadwalKuliah::where([
        ['hari',            $data['hari']],
        ['nama_ruangan',    $data['nama_ruangan']],
        ['kode_mata_kuliah',$kelas->kode_matkul],
        ['unique_number',   $kelas->unique_number],
        ['jam',             $jamRange],
    ])->count();
    if ($already >= $ruang->kapasitas_kelas) {
        return redirect()->route('admin.jadwal.index')
            ->with('error', "Kapasitas ruangan {$data['nama_ruangan']} untuk dosen/matkul ini sudah penuh ({$ruang->kapasitas_kelas}).");
    }

    // — 7) **Overlap check**: lihat SEMUA jadwal yang sudah ada
    //    hari + ruang/atau dosen sama, kecuali entry persis ini
    $exists = JadwalKuliah::where('hari', $data['hari'])
        ->where(function($q) use ($kelas, $data) {
            // ruang sama **atau** dosen sama
            $q->where('nama_ruangan', $data['nama_ruangan'])
              ->orWhere('unique_number', $kelas->unique_number);
        })
        // kecualikan jika entry itu sendiri (tepat sama matkul/dosen/ruang/jam)
        ->whereNot(function($q) use ($kelas, $jamRange) {
            $q->where('kode_mata_kuliah', $kelas->kode_matkul)
              ->where('unique_number',    $kelas->unique_number)
              ->where('nama_ruangan',     request('nama_ruangan'))
              ->where('jam',              $jamRange);
        })
        // dan **overlap** waktunya
        ->get()  // ambil dulu semua, karena kita harus parse jam
        ->filter(function($row) use ($sNew, $eNew) {
            list($sOld, $eOld) = explode(' - ', $row->jam);
            return ! ( $eOld <= $sNew || $eNew <= $sOld );
        })
        ->isNotEmpty();

    if ($exists) {
        return redirect()->route('admin.jadwal.index')
            ->with('error', 'Jadwal bentrok: interval waktunya tumpang tindih dengan jadwal lain.');
    }

    // — 8) Simpan
    JadwalKuliah::create([
        'hari'             => $data['hari'],
        'kode_mata_kuliah'=> $kelas->kode_matkul,
        'kelas'           => $kelas->kelas,
        'nama_ruangan'    => $data['nama_ruangan'],
        'unique_number'   => $kelas->unique_number,
        'jam'             => $jamRange,
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
