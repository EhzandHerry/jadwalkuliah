<?php

namespace App\Http\Controllers;

use App\Models\JadwalKuliah;
use App\Models\Kelas;
use App\Models\RuangKelas;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    /**
     * Tampilkan daftar kelas + jadwal, plus available times per dosen
     */
    public function index()
    {
        // Ambil semua kelas beserta relasi mataKuliah, dosen, dan (jika ada) jadwal
        $kelas = Kelas::with(['mataKuliah', 'dosen', 'ruangKelas'])
            ->leftJoin('jadwal', function ($join) {
                $join->on('kelas.kode_matkul', '=', 'jadwal.kode_mata_kuliah')
                     ->on('kelas.kelas',       '=', 'jadwal.kelas');
            })
            ->select('kelas.*', 'jadwal.nama_ruangan', 'jadwal.hari', 'jadwal.jam')
            ->get();

        // Semua ruang kelas untuk opsi
        $ruangKelasList = RuangKelas::all();

        // Siapkan struktur availableTimes per dosen (key = unique_number)
        // Format: [ '0506098902' => [ 'Senin' => [ ['start'=>'07:00','end'=>'09:00'], ... ], ... ], ... ]
        $availableTimes = [];
        foreach ($kelas as $k) {
            if ($k->dosen && $k->dosen->available) {
                $uniq = $k->dosen->unique_number;
                if (! isset($availableTimes[$uniq])) {
                    $availableTimes[$uniq] = $k->dosen->available
                        ->groupBy('hari')
                        ->map(function ($times) {
                            // Untuk setiap baris available, ambil start_time & end_time (HH:MM)
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

        return view('admin.jadwal.index', compact('kelas', 'ruangKelasList', 'availableTimes'));
    }

    /**
     * Simpan penugasan ruang + hari + jam untuk satu kelas
     */
    public function assignRuang(Request $request, $kelasId)
    {
        $kelas = Kelas::with(['mataKuliah','dosen'])->findOrFail($kelasId);

        // Pastikan dosen sudah di–assign
        if (! $kelas->unique_number) {
            return redirect()->route('admin.jadwal.index')
                ->with('error', "Dosen untuk mata kuliah “{$kelas->mataKuliah->nama_matkul}” (Kelas {$kelas->kelas}) belum dipilih. Silakan pilih dosen di halaman Matakuliah & Dosen!");
        }

        // Validasi input
        $request->validate([
            'nama_ruangan' => 'required|string|exists:ruang_kelas,nama_ruangan',
            'hari'         => 'required|string',
            'jam'          => 'required|string',
        ]);

        // Daftar sesi guna perhitungan jam selesai (jika mau dipakai)
        $jamSesi = [
            "07:00 - 07:50","07:50 - 08:40",
            "08:50 - 09:40","09:40 - 10:30",
            "10:40 - 11:30","12:10 - 13:10",
            "13:20 - 14:10","14:10 - 15:00",
            "15:30 - 16:20","16:20 - 17:10",
            "17:10 - 18:00","18:30 - 19:20",
            "19:20 - 20:10","20:10 - 21:00"
        ];

        // (Optional) fungsi untuk menghitung jam lengkap berdasar SKS...
        function hitungJamSelesai($jamMulai, $durasiMenit, $jamSesi) {
            $idx = array_search($jamMulai, array_map(fn($r)=>explode(' - ',$r)[0], $jamSesi));
            if ($idx === false) return null;
            $jmlSesi = ceil($durasiMenit/50);
            $idx2 = min(count($jamSesi)-1, $idx + $jmlSesi - 1);
            $akhir = explode(' - ',$jamSesi[$idx2])[1];
            return "{$jamMulai} - {$akhir}";
        }

        // Hitung jam lengkap berdasar SKS (durasi = sks*50 menit)
        $jamMulai = substr($request->jam, 0, 5);
        $durasi   = ($kelas->mataKuliah->sks ?? 0) * 50;
        $jamLengkap = hitungJamSelesai($jamMulai, $durasi, $jamSesi);

        // Cek bentrok jadwal pada hari & ruang yang sama
        $toMinutes = fn($t)=>array_sum(explode(':',$t))*60/60; // cara sederhana
        $jadwalTabrakan = JadwalKuliah::where('hari',$request->hari)
            ->where('nama_ruangan',$request->nama_ruangan)
            ->get()
            ->filter(function($j) use($toMinutes, $jamLengkap){
                [$s1,$e1] = explode(' - ',$jamLengkap);
                [$s2,$e2] = explode(' - ',$j->jam);
                return ($toMinutes($s1) < $toMinutes($e2))
                    && ($toMinutes($e1) > $toMinutes($s2));
            })
            ->first();

        if ($jadwalTabrakan) {
            return redirect()->route('admin.jadwal.index')
                ->with('error', 'Jadwal bentrok dengan kelas lain pada hari, ruang, dan jam yang sama.');
        }

        // Pastikan belum pernah input jadwal untuk kelas & matkul ini
        $exists = JadwalKuliah::where('kode_mata_kuliah',$kelas->kode_matkul)
            ->where('kelas',$kelas->kelas)
            ->exists();

        if ($exists) {
            return redirect()->route('admin.jadwal.index')
                ->with('error','Jadwal untuk kelas ini sudah ada, tidak bisa diinput ulang.');
        }

        // Simpan
        JadwalKuliah::create([
            'kode_mata_kuliah' => $kelas->kode_matkul,
            'kelas'            => $kelas->kelas,
            'unique_number'    => $kelas->unique_number,
            'nama_ruangan'     => $request->nama_ruangan,
            'hari'             => $request->hari,
            'jam'              => $jamLengkap,
        ]);

        return redirect()->route('admin.jadwal.index')
                         ->with('success','Jadwal berhasil ditambahkan.');
    }
}
