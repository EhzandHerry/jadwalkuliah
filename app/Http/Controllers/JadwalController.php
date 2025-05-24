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
        ->select('kelas.*', 'jadwal.nama_ruangan', 'jadwal.hari', 'jadwal.jam')
        ->get();

    $ruangKelasList = RuangKelas::all();

    return view('admin.jadwal.index', compact('kelas', 'ruangKelasList'));
}

public function assignRuang(Request $request, $kelasId)
{
    // 1) ambil dulu data kelas (beserta relasi mataKuliah)
    $kelas = Kelas::with('mataKuliah', 'dosen')->findOrFail($kelasId);

    // 2) cek apakah dosen sudah diset
    if (! $kelas->unique_number) {
        return redirect()->route('admin.jadwal.index')
            ->with('error', "Dosen untuk mata kuliah “{$kelas->mataKuliah->nama_matkul}” (Kelas {$kelas->kelas}) belum dipilih. Silakan pilih dosen di halaman Matakuliah.");
    }

    // 3) validasi input ruang/hari/jam
    $request->validate([
        'nama_ruangan' => 'required|string|exists:ruang_kelas,nama_ruangan',
        'hari'         => 'required|string',
        'jam'          => 'required|string',
    ]);


    // Jam sesi tersedia (untuk menghitung jam selesai)
    $jamSesi = [
        "07:00 - 07:50",
        "07:50 - 08:40",
        "08:50 - 09:40",
        "09:40 - 10:30",
        "10:40 - 11:30",
        "12:10 - 13:10",
        "13:20 - 14:10",
        "14:10 - 15:00",
        "15:30 - 16:20",
        "16:20 - 17:10",
        "17:10 - 18:00",
        "18:30 - 19:20",
        "19:20 - 20:10",
        "20:10 - 21:00"
    ];

    // Fungsi hitung jam selesai
    function hitungJamSelesai($jamMulai, $durasiMenit, $jamSesi) {
        $indexMulai = null;
        foreach ($jamSesi as $i => $range) {
            $parts = explode(" - ", $range);
            if ($parts[0] === $jamMulai) {
                $indexMulai = $i;
                break;
            }
        }
        if ($indexMulai === null) {
            return null;
        }
        $jumlahSesi = ceil($durasiMenit / 50);
        $indexSelesai = $indexMulai + $jumlahSesi - 1;
        if ($indexSelesai >= count($jamSesi)) {
            $indexSelesai = count($jamSesi) - 1;
        }
        $partsSelesai = explode(" - ", $jamSesi[$indexSelesai]);
        $jamSelesai = $partsSelesai[1];
        return $jamMulai . " - " . $jamSelesai;
    }

    // Hitung jam lengkap berdasarkan durasi (sks * 50 menit)
    $jamMulai = substr($request->jam, 0, 5);
    $sks = $kelas->mataKuliah->sks ?? 0;
    $durasi = $sks * 50;
    $jamLengkap = hitungJamSelesai($jamMulai, $durasi, $jamSesi);

    // Parsing interval baru
    [$startNew, $endNew] = explode(' - ', $jamLengkap);

    // Fungsi bantu konversi waktu ke menit dari tengah malam
    $toMinutes = function($time) {
        list($h, $m) = explode(':', $time);
        return ((int)$h) * 60 + ((int)$m);
    };

    $startNewM = $toMinutes($startNew);
    $endNewM = $toMinutes($endNew);

    // Cek tabrakan jadwal dengan overlap waktu di hari dan ruang yang sama
    $jadwalTabrakan = JadwalKuliah::where('hari', $request->hari)
        ->where('nama_ruangan', $request->nama_ruangan)
        ->get()
        ->filter(function ($jadwal) use ($toMinutes, $startNewM, $endNewM) {
            [$startExist, $endExist] = explode(' - ', $jadwal->jam);
            $startExistM = $toMinutes($startExist);
            $endExistM = $toMinutes($endExist);

            // Cek overlap interval: 
            // Jika mulai baru < selesai lama dan selesai baru > mulai lama, berarti overlap
            return ($startNewM < $endExistM) && ($endNewM > $startExistM);
        })
        ->first();

    if ($jadwalTabrakan) {
        return redirect()->route('admin.jadwal.index')
            ->with('error', 'Jadwal bentrok dengan jadwal lain pada hari, ruang, dan jam yang sama.');
    }

    // Cek jadwal kelas sama sudah ada
    $jadwal = JadwalKuliah::where('kode_mata_kuliah', $kelas->kode_matkul)
                ->where('kelas', $kelas->kelas)
                ->first();

    if ($jadwal) {
        return redirect()->route('admin.jadwal.index')->with('error', 'Jadwal sudah ada, tidak bisa diinput ulang.');
    }

    $jadwal = new JadwalKuliah();
    $jadwal->kode_mata_kuliah = $kelas->kode_matkul;
    $jadwal->kelas = $kelas->kelas;
    $jadwal->unique_number = $kelas->unique_number;
    $jadwal->nama_ruangan = $request->nama_ruangan;
    $jadwal->hari = $request->hari;
    $jadwal->jam = $jamLengkap;
    $jadwal->save();

    // … setelah $jadwal->save();
return redirect()->route('admin.jadwal.index')
    ->with('success', 'Jadwal berhasil ditambahkan.');
}

}
