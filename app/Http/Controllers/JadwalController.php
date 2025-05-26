<?php

namespace App\Http\Controllers;

use App\Models\JadwalKuliah;
use App\Models\Kelas;
use App\Models\RuangKelas;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with(['mataKuliah', 'dosen', 'ruangKelas'])
            ->leftJoin('jadwal', function ($join) {
                $join->on('kelas.kode_matkul', '=', 'jadwal.kode_mata_kuliah')
                     ->on('kelas.kelas', '=', 'jadwal.kelas');
            })
            ->select('kelas.*', 'jadwal.id as jadwal_id', 'jadwal.nama_ruangan', 'jadwal.hari', 'jadwal.jam')
            ->get();

        $ruangKelasList = RuangKelas::all();

        // Prepare availableTimes (sama seperti sebelumnya)...
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
                                    'end'   => substr($item->end_time, 0, 5),
                                ];
                            })->values()->all();
                        })
                        ->toArray();
                }
            }
        }

        return view('admin.jadwal.index', compact('kelas', 'ruangKelasList', 'availableTimes'));
    }

    public function assignRuang(Request $request, $kelasId)
{
    $kelas = Kelas::with(['mataKuliah','dosen'])->findOrFail($kelasId);

    if (! $kelas->unique_number) {
        return redirect()->route('admin.jadwal.index')
            ->with('error', "Dosen untuk mata kuliah “{$kelas->mataKuliah->nama_matkul}” (Kelas {$kelas->kelas}) belum dipilih.");
    }

    $request->validate([
        'nama_ruangan' => 'required|string|exists:ruang_kelas,nama_ruangan',
        'hari'         => 'required|string',
        'jam'          => 'required|string',
    ]);

    // Cek bentrok jadwal, dst... (sesuai logika validasi kamu)

    // Simpan jadwal baru
    JadwalKuliah::create([
        'kode_mata_kuliah' => $kelas->kode_matkul,
        'kelas'            => $kelas->kelas,
        'unique_number'    => $kelas->unique_number,
        'nama_ruangan'     => $request->nama_ruangan,
        'hari'             => $request->hari,
        'jam'              => $request->jam,
    ]);

    return redirect()->route('admin.jadwal.index')
        ->with('success', 'Jadwal berhasil ditambahkan.');
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

}
