<?php

namespace App\Http\Controllers;

use App\Models\JadwalKuliah;
use App\Models\Kelas;
use App\Models\RuangKelas;
use App\Models\MataKuliah;
use App\Models\User; // Model User tetap digunakan untuk Dosen
use App\Models\Available;
use Illuminate\Http\Request;
use App\Exports\JadwalExport;
use App\Exports\JadwalMatrixExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;


class JadwalController extends Controller
{
    // app/Http/Controllers/JadwalController.php

public function index(Request $request)
{
    $search = $request->input('search');
    $semesterType = $request->input('semester_type', 'semua');

    $query = Kelas::query()->has('mataKuliah');

    // Modifikasi untuk menangani pilihan "semua"
    if ($semesterType && $semesterType !== 'semua') {
        $query->whereHas('mataKuliah', function ($q) use ($semesterType) {
            if ($semesterType === 'gasal') {
                $q->whereRaw('semester % 2 != 0');
            } elseif ($semesterType === 'genap') {
                $q->whereRaw('semester % 2 = 0');
            }
        });
    }

    if ($search) {
        $query->whereHas('mataKuliah', function ($q) use ($search) {
            $q->where('nama_matkul', 'like', "%{$search}%");
        });
    }

    $kelas = $query->with(['mataKuliah', 'dosen'])
        ->leftJoin('jadwal', function ($join) {
            $join->on('kelas.kode_matkul', '=', 'jadwal.kode_matkul')
                 ->on('kelas.kelas', '=', 'jadwal.kelas');
        })
        ->select(
            'kelas.*',
            'jadwal.id as jadwal_id',
            'jadwal.nama_ruangan',
            'jadwal.hari',
            'jadwal.jam'
        )
        ->orderBy('kelas.kode_matkul', 'asc')
        ->orderBy('kelas.kelas', 'asc')
        ->get();

    $ruangKelasList = RuangKelas::orderBy('nama_ruangan')->get();
    $roomCapacities = $ruangKelasList->pluck('kapasitas_kelas', 'nama_ruangan');
    $allDosen = User::where('is_admin', false)->with('available')->get();
    
    // Modifikasi untuk existing jadwals dengan filter "semua"
    $existingJadwalsQuery = JadwalKuliah::with('mataKuliah');

    if ($semesterType && $semesterType !== 'semua') {
        $existingJadwalsQuery->whereHas('mataKuliah', function ($q) use ($semesterType) {
            if ($semesterType === 'gasal') {
                $q->whereRaw('semester % 2 != 0');
            } elseif ($semesterType === 'genap') {
                $q->whereRaw('semester % 2 = 0');
            }
        });
    }

    $existingJadwals = $existingJadwalsQuery->get()->map(function($j) {
        list($start, $end) = explode(' - ', $j->jam);
        return [
            'hari'     => $j->hari,
            'ruang'    => $j->nama_ruangan,
            'dosen'    => $j->nidn,
            'matkul'   => $j->kode_matkul,
            'start'    => trim($start),
            'end'      => trim($end),
            'kelas'    => $j->kelas,
            'semester' => optional($j->mataKuliah)->semester,
        ];
    })->toArray();

    $availableTimes = [];
    foreach ($allDosen as $dosen) {
        $dosenAvailableTimes = [];
        
        $dosenSchedules = $dosen->available->groupBy('hari');
        
        foreach ($dosenSchedules as $hari => $times) {
            $dayAvailableTimes = [];
            
            foreach ($times as $timeSlot) {
                $slotStart = $timeSlot->waktu_mulai;
                $slotEnd = $timeSlot->waktu_selesai;
                $timeRange = $slotStart . ' - ' . $slotEnd;
                
                $conflictingSchedules = collect($existingJadwals)->where('dosen', $dosen->nidn)
                    ->where('hari', $hari)
                    ->filter(function($schedule) use ($slotStart, $slotEnd) {
                        return $this->timeOverlaps($slotStart, $slotEnd, $schedule['start'], $schedule['end']);
                    });
                
                if ($conflictingSchedules->isEmpty()) {
                    $dayAvailableTimes[] = $timeRange;
                } else {
                    $availableRooms = $this->getAvailableRoomsForTimeSlot(
                        $hari, 
                        $slotStart, 
                        $slotEnd, 
                        $existingJadwals, 
                        $ruangKelasList,
                        $conflictingSchedules
                    );
                    
                    if (!empty($availableRooms)) {
                        $dayAvailableTimes[] = [
                            'time' => $timeRange,
                            'available_rooms' => $availableRooms,
                            'existing_subjects' => $conflictingSchedules->pluck('matkul')->unique()->values()->toArray()
                        ];
                    }
                }
            }
            
            if (!empty($dayAvailableTimes)) {
                $dosenAvailableTimes[$hari] = $dayAvailableTimes;
            }
        }
        
        $availableTimes[$dosen->nidn] = $dosenAvailableTimes;
    }

    return view('admin.jadwal.index', compact(
        'kelas',
        'ruangKelasList',
        'availableTimes',
        'existingJadwals',
        'roomCapacities'
    ));
}

// [BARU] Helper method untuk cek overlap waktu
private function timeOverlaps($start1, $end1, $start2, $end2)
{
    $start1 = strtotime($start1);
    $end1 = strtotime($end1);
    $start2 = strtotime($start2);
    $end2 = strtotime($end2);
    
    return ($start1 < $end2) && ($end1 > $start2);
}

// [BARU] Helper method untuk mendapatkan ruang yang masih tersedia
private function getAvailableRoomsForTimeSlot($hari, $startTime, $endTime, $existingJadwals, $ruangKelasList, $conflictingSchedules)
{
    $occupiedRooms = collect($existingJadwals)
        ->where('hari', $hari)
        ->filter(function($schedule) use ($startTime, $endTime) {
            return $this->timeOverlaps($startTime, $endTime, $schedule['start'], $schedule['end']);
        })
        ->pluck('ruang')
        ->unique()
        ->values()
        ->toArray();
    
    $availableRooms = $ruangKelasList->whereNotIn('nama_ruangan', $occupiedRooms)
        ->pluck('nama_ruangan')
        ->toArray();
    
    return $availableRooms;
}

    public function assignRuang(Request $request, $kelasId)
{
    // Load data kelas
    $kelas = Kelas::with(['mataKuliah','dosen'])->findOrFail($kelasId);

    if (! $kelas->nidn) {
        return redirect()->route('admin.jadwal.index')
                         ->with('error', "Dosen untuk \"{$kelas->mataKuliah->nama_matkul}\" (Kelas {$kelas->kelas}) belum dipilih.");
    }

    // Validasi input
    $data = $request->validate([
        'nama_ruangan' => 'required|string|exists:ruang_kelas,nama_ruangan',
        'hari'         => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
        'jam'          => ['required','regex:/^\d{2}:\d{2}\s-\s\d{2}:\d{2}$/'],
    ],[
        'jam.regex' => 'Format jam harus "HH:mm - HH:mm", misal "07:00 - 07:50".'
    ]);

    // Hitung jam range
    $sessions = [
        "07:00 - 07:50","07:50 - 08:40","08:50 - 09:40","09:40 - 10:30",
        "10:40 - 11:30","12:10 - 13:10","13:20 - 14:10","14:10 - 15:00",
        "15:30 - 16:20","16:20 - 17:10","17:10 - 18:00","18:30 - 19:20",
        "19:20 - 20:10","20:10 - 21:00",
    ];
    $startIdx = array_search($data['jam'], $sessions);
    if ($startIdx === false) {
        return redirect()->route('admin.jadwal.index')->with('error', 'Sesi jam yang dipilih tidak valid.');
    }
    $endIdx = min($startIdx + ($kelas->mataKuliah->sks - 1), count($sessions) - 1);
    list($sNew) = explode(' - ', $sessions[$startIdx]);
    [, $eNew] = explode(' - ', $sessions[$endIdx]);
    $jamRange = "{$sNew} - {$eNew}";

    // Helper untuk cek overlap
    $overlaps = function($oldRange) use($sNew, $eNew) {
        list($sOld, $eOld) = explode(' - ', $oldRange);
        return !($eOld <= $sNew || $eNew <= $sOld);
    };

    // Ambil jadwal yang bentrok
    $allToday = JadwalKuliah::with('mataKuliah')->where('hari', $data['hari'])->get();
    $conflictingSchedules = $allToday->filter(fn($old) => $overlaps($old->jam));

    \Log::info("=== VALIDATION CHECK ===");
    \Log::info("New schedule: {$kelas->mataKuliah->kode_matkul} by {$kelas->nidn} in {$data['nama_ruangan']} on {$data['hari']} at {$jamRange}");

    // PERBAIKAN: Validasi dosen mengajar mata kuliah sama di ruang berbeda
    $dosenSameSubjectConflict = $conflictingSchedules->filter(function($old) use ($kelas, $data) {
        return $old->nidn === $kelas->nidn && 
               $old->kode_matkul === $kelas->kode_matkul && 
               $old->nama_ruangan !== $data['nama_ruangan'];
    });

    if ($dosenSameSubjectConflict->count() > 0) {
        $conflictRooms = $dosenSameSubjectConflict->pluck('nama_ruangan')->unique()->implode(', ');
        \Log::info("❌ Dosen already teaching same subject in other rooms: {$conflictRooms}");
        return redirect()->route('admin.jadwal.index')
            ->with('error', "Dosen {$kelas->dosen->nama} sudah mengajar mata kuliah {$kelas->mataKuliah->nama_matkul} di ruang lain ({$conflictRooms}) pada waktu yang sama. Dosen tidak bisa mengajar mata kuliah yang sama di ruang berbeda secara bersamaan.");
    }

    // Validasi ruangan
    $ruang = RuangKelas::where('nama_ruangan', $data['nama_ruangan'])->first();
    $schedulesInSameRoom = $conflictingSchedules->where('nama_ruangan', $data['nama_ruangan']);
    
    \Log::info("Room: {$data['nama_ruangan']}, Capacity: {$ruang->kapasitas_kelas}");
    \Log::info("Existing schedules in room: " . $schedulesInSameRoom->count());
    
    // Jika ruang sudah mencapai kapasitas
    if ($schedulesInSameRoom->count() >= $ruang->kapasitas_kelas) {
        \Log::info("Room at capacity, checking for parallel classes...");
        
        $isAllParallelClass = $schedulesInSameRoom->every(function($j) use ($kelas) {
            $isParallel = ($j->kode_matkul === $kelas->kode_matkul && $j->nidn === $kelas->nidn);
            \Log::info("Schedule {$j->id}: {$j->kode_matkul} by {$j->nidn} - Is parallel: " . ($isParallel ? 'YES' : 'NO'));
            return $isParallel;
        });

        if (!$isAllParallelClass) {
            \Log::info("❌ Room full with non-parallel classes");
            return redirect()->route('admin.jadwal.index')
                ->with('error', "Ruangan {$data['nama_ruangan']} sudah penuh (kapasitas: {$ruang->kapasitas_kelas}). Tidak dapat menambahkan jadwal karena ada mata kuliah/dosen lain yang menggunakan ruang ini di waktu yang sama.");
        } else {
            // PERBAIKAN: Bahkan untuk kelas paralel, jika ruang sudah penuh, tidak boleh tambah lagi
            \Log::info("❌ Room full even with parallel classes");
            return redirect()->route('admin.jadwal.index')
                ->with('error', "Ruangan {$data['nama_ruangan']} sudah penuh (kapasitas: {$ruang->kapasitas_kelas}). Semua slot sudah terisi oleh kelas paralel mata kuliah ini.");
        }
    }

    // Validasi konflik lainnya
    foreach ($conflictingSchedules as $old) {
        // Validasi dosen mengajar mata kuliah berbeda
        if ($old->nidn === $kelas->nidn && $old->kode_matkul !== $kelas->kode_matkul) {
            return redirect()->route('admin.jadwal.index')
                ->with('error', 'Jadwal bentrok: dosen sudah memiliki jadwal mata kuliah lain di slot waktu ini.');
        }

        // Validasi mahasiswa
        if (optional($old->mataKuliah)->semester === $kelas->mataKuliah->semester && $old->kelas === $kelas->kelas) {
            $peminatanLama = optional($old->mataKuliah)->peminatan;
            $peminatanBaru = $kelas->mataKuliah->peminatan;
            $isNewWajib = is_null($peminatanBaru);
            $isOldWajib = is_null($peminatanLama);

            if ($isNewWajib && $isOldWajib) {
                return redirect()->route('admin.jadwal.index')
                    ->with('error', "Jadwal bentrok: Mahasiswa Kelas {$kelas->kelas} sudah memiliki mata kuliah wajib lain di waktu ini.");
            }

            if (!$isNewWajib && !$isOldWajib && $peminatanBaru === $peminatanLama) {
                return redirect()->route('admin.jadwal.index')
                    ->with('error', "Jadwal bentrok: Mahasiswa Kelas {$kelas->kelas} sudah mengambil mata kuliah peminatan '{$peminatanBaru}' lain di waktu ini.");
            }
        }

        // PERBAIKAN: Validasi ruang dengan mata kuliah/dosen berbeda
        if ($old->nama_ruangan === $data['nama_ruangan'] && 
            !($old->kode_matkul === $kelas->kode_matkul && $old->nidn === $kelas->nidn)) {
            
            $remainingSlots = $ruang->kapasitas_kelas - $schedulesInSameRoom->count();
            if ($remainingSlots <= 0) {
                return redirect()->route('admin.jadwal.index')
                    ->with('error', "Ruangan {$data['nama_ruangan']} tidak memiliki slot tersisa untuk mata kuliah/dosen yang berbeda.");
            }
        }
    }

    // Simpan jadwal jika semua validasi lolos
    $newJadwal = JadwalKuliah::create([
        'hari'         => $data['hari'],
        'kode_matkul'  => $kelas->kode_matkul,
        'kelas'        => $kelas->kelas,
        'nama_ruangan' => $data['nama_ruangan'],
        'nidn'         => $kelas->nidn,
        'jam'          => $jamRange,
    ]);

    \Log::info("✅ Jadwal berhasil ditambahkan: ID {$newJadwal->id}");

    return redirect()->route('admin.jadwal.index')
                     ->with('success', "Jadwal berhasil ditambahkan ({$jamRange}).");
}

    public function edit($jadwalId)
{
    $jadwal = JadwalKuliah::with('dosen', 'mataKuliah')->findOrFail($jadwalId);
    $ruangKelasList = RuangKelas::orderBy('nama_ruangan')->get();
    $roomCapacities = $ruangKelasList->pluck('kapasitas_kelas', 'nama_ruangan');

    // Ambil semua dosen untuk consistency
    $allDosen = User::where('is_admin', false)->with('available')->get();
    
    // Ambil semua jadwal KECUALI yang sedang diedit untuk filtering yang akurat
    $existingJadwalsQuery = JadwalKuliah::with('mataKuliah')->where('id', '!=', $jadwalId);
    
    $existingJadwals = $existingJadwalsQuery->get()->map(function($j) {
        list($start, $end) = explode(' - ', $j->jam);
        return [
            'hari'     => $j->hari,
            'ruang'    => $j->nama_ruangan,
            'dosen'    => $j->nidn,
            'matkul'   => $j->kode_matkul,
            'start'    => trim($start),
            'end'      => trim($end),
            'kelas'    => $j->kelas,
            'semester' => optional($j->mataKuliah)->semester,
        ];
    })->toArray();

    // Buat mapping waktu tersedia dengan logic yang sama seperti di index
    $availableTimes = [];
    foreach ($allDosen as $dosen) {
        $dosenAvailableTimes = [];
        
        $dosenSchedules = $dosen->available->groupBy('hari');
        
        foreach ($dosenSchedules as $hari => $times) {
            $dayAvailableTimes = [];
            
            foreach ($times as $timeSlot) {
                $slotStart = $timeSlot->waktu_mulai;
                $slotEnd = $timeSlot->waktu_selesai;
                $timeRange = $slotStart . ' - ' . $slotEnd;
                
                // Cek apakah dosen sudah ada jadwal di waktu ini
                $conflictingSchedules = collect($existingJadwals)->where('dosen', $dosen->nidn)
                    ->where('hari', $hari)
                    ->filter(function($schedule) use ($slotStart, $slotEnd) {
                        return $this->timeOverlaps($slotStart, $slotEnd, $schedule['start'], $schedule['end']);
                    });
                
                if ($conflictingSchedules->isEmpty()) {
                    $dayAvailableTimes[] = $timeRange;
                } else {
                    $availableRooms = $this->getAvailableRoomsForTimeSlot(
                        $hari, 
                        $slotStart, 
                        $slotEnd, 
                        $existingJadwals, 
                        $ruangKelasList,
                        $conflictingSchedules
                    );
                    
                    if (!empty($availableRooms)) {
                        $dayAvailableTimes[] = [
                            'time' => $timeRange,
                            'available_rooms' => $availableRooms,
                            'existing_subjects' => $conflictingSchedules->pluck('matkul')->unique()->values()->toArray()
                        ];
                    }
                }
            }
            
            if (!empty($dayAvailableTimes)) {
                $dosenAvailableTimes[$hari] = $dayAvailableTimes;
            }
        }
        
        $availableTimes[$dosen->nidn] = $dosenAvailableTimes;
    }

    return view('admin.jadwal.edit', compact('jadwal', 'ruangKelasList', 'availableTimes', 'existingJadwals', 'roomCapacities'));
}

    public function update(Request $request, $jadwalId)
{
    $jadwal = JadwalKuliah::findOrFail($jadwalId);

    $data = $request->validate([
        'nama_ruangan' => 'required|string|exists:ruang_kelas,nama_ruangan',
        'hari'         => 'required|string|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
        'jam'          => ['required','regex:/^\d{2}:\d{2}\s-\s\d{2}:\d{2}$/'],
    ], ['jam.regex' => 'Format jam harus "HH:mm - HH:mm".']);

    $kelas = Kelas::with('mataKuliah')->where('kode_matkul', $jadwal->kode_matkul)->where('kelas', $jadwal->kelas)->firstOrFail();
    $sks = $kelas->mataKuliah->sks;
    $semesterBaru = $kelas->mataKuliah->semester;
    $kelasBaru = $kelas->kelas;

    $sessions = [
        "07:00 - 07:50","07:50 - 08:40","08:50 - 09:40","09:40 - 10:30",
        "10:40 - 11:30","12:10 - 13:10","13:20 - 14:10","14:10 - 15:00",
        "15:30 - 16:20","16:20 - 17:10","17:10 - 18:00","18:30 - 19:20",
        "19:20 - 20:10","20:10 - 21:00",
    ];

    $startIdx = array_search($data['jam'], $sessions);
    if ($startIdx === false) {
        return redirect()->back()->with('error', 'Sesi jam yang dipilih tidak valid.')->withInput();
    }
    $endIdx = min($startIdx + ($sks - 1), count($sessions) - 1);

    list($sNew) = explode(' - ', $sessions[$startIdx]);
    [, $eNew] = explode(' - ', $sessions[$endIdx]);
    $jamRange = "{$sNew} - {$eNew}";

    $toMin = function($t) { list($h,$m) = explode(':', substr($t,0,5)); return $h * 60 + $m; };
    $overlaps = function($oldRange) use($toMin, $sNew, $eNew) { 
        list($sOld, $eOld) = explode(' - ', $oldRange); 
        return ! ($toMin($eOld) <= $toMin($sNew) || $toMin($eNew) <= $toMin($sOld)); 
    };

    $others = JadwalKuliah::where('hari', $data['hari'])->where('id', '!=', $jadwal->id)->get();
    $conflictingSchedules = $others->filter(fn($old) => $overlaps($old->jam));

    // PERBAIKAN: Validasi kapasitas ruang untuk update
    $ruang = RuangKelas::where('nama_ruangan', $data['nama_ruangan'])->first();
    $schedulesInSameRoom = $conflictingSchedules->where('nama_ruangan', $data['nama_ruangan']);
    
    if ($schedulesInSameRoom->count() >= $ruang->kapasitas_kelas) {
        $isAllParallelClass = $schedulesInSameRoom->every(function($j) use ($jadwal) {
            return $j->kode_matkul === $jadwal->kode_matkul && $j->nidn === $jadwal->nidn;
        });

        if (!$isAllParallelClass) {
            return redirect()->back()
                ->with('error', "Ruangan {$data['nama_ruangan']} sudah penuh (kapasitas: {$ruang->kapasitas_kelas}). Tidak dapat memindahkan jadwal ke ruang ini.")
                ->withInput();
        }
    }

    foreach ($conflictingSchedules as $old) {
        if (! $overlaps($old->jam)) continue;
        
        if ($old->nama_ruangan === $data['nama_ruangan'] && !($old->kode_matkul === $jadwal->kode_matkul && $old->nidn === $jadwal->nidn)) {
            return redirect()->back()->with('error', 'Jadwal bentrok: ruang sudah dipakai pada slot ini.')->withInput();
        }

        if ($old->nidn === $jadwal->nidn && $old->kode_matkul !== $jadwal->kode_matkul) {
            return redirect()->back()->with('error', 'Jadwal bentrok: dosen sudah memiliki jadwal di slot ini.')->withInput();
        }

        $mkLamaSemester = MataKuliah::where('kode_matkul', $old->kode_matkul)->value('semester');
        if ($mkLamaSemester === $semesterBaru && $old->kelas === $kelasBaru) {
            return redirect()->back()->with('error', "Jadwal bentrok: mahasiswa Kelas {$kelasBaru} Semester {$semesterBaru} sudah memiliki mata kuliah lain pada slot ini.")->withInput();
        }
    }

    $jadwal->update(['nama_ruangan' => $data['nama_ruangan'], 'hari' => $data['hari'], 'jam' => $jamRange]);
    return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil diperbarui.');
}

    public function destroy($jadwalId)
    {
        $jadwal = JadwalKuliah::findOrFail($jadwalId);
        $jadwal->delete();
        return redirect()->route('admin.jadwal.index')->with('success', 'Jadwal berhasil dihapus.');
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

    public function previewMatrix()
{
    $allRoomsCollection = RuangKelas::orderBy('nama_ruangan')->get();
    $f6_rooms = $allRoomsCollection->filter(fn($r) => str_starts_with($r->nama_ruangan, 'F6'));
    $f4_rooms = $allRoomsCollection->filter(fn($r) => str_starts_with($r->nama_ruangan, 'F4'));
    $other_rooms = $allRoomsCollection->reject(fn($r) => str_starts_with($r->nama_ruangan, 'F6') || str_starts_with($r->nama_ruangan, 'F4'));
    $rooms = $f6_rooms->merge($f4_rooms)->merge($other_rooms)->pluck('nama_ruangan')->toArray();
    
    $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $sessionRanges = [
        1 => ['07:00-07:50', '07:50-08:40'], 2 => ['08:50-09:40', '09:40-10:30'],
        3 => ['10:40-11:30'], 4 => ['12:10-13:10'], 5 => ['13:20-14:10', '14:10-15:00'],
        6 => ['15:30-16:20', '16:20-17:10', '17:10-18:00'], 7 => ['18:30-19:20', '19:20-20:10', '20:10-21:00'],
    ];
    $breakSlots = [
        1 => ['time' => '08:40-08:50', 'text' => 'Pergantian Sesi'], 2 => ['time' => '10:30-10:40', 'text' => 'Pergantian Sesi'],
        3 => ['time' => '11:30-12:20', 'text' => 'Pergantian Sesi'], 4 => ['time' => '13:10-13:20', 'text' => 'Pergantian Sesi'],
        5 => ['time' => '15:00-15:30', 'text' => 'Pergantian Sesi'], 6 => ['time' => '17:45-18:30', 'text' => 'Pergantian Sesi'],
    ];
    $semesterColors = [2 => 'E2F0D9', 4 => 'FDE9D9', 6 => 'DDEBF7'];
    $peminatanColor = 'E6E6FA'; // Warna ungu untuk mata kuliah peminatan

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
                        $groups = $jadwals->groupBy(fn($j) => $j->kode_matkul . '|' . $j->nidn);
                        foreach ($groups as $items) {
                            $first = $items->first();
                            $kelasList = $items->pluck('kelas')->unique()->sort()->implode(',');
                            $text = "{$first->kode_matkul}({$kelasList})\n{$first->mataKuliah->nama_matkul}\nDosen: {$first->dosen->nama}";
                            $matrix[$sesi][$jam][$ruang][] = [
                                'text' => $text,
                                'semester' => $first->mataKuliah->semester,
                                'is_peminatan' => $first->mataKuliah->is_peminatan ?? false // Tambahkan informasi peminatan
                            ];
                        }
                    }
                }
            }
        }
        $dailyData[$hari] = $matrix;
    }

    return view('admin.jadwal.matrix_preview', compact('rooms', 'days', 'dailyData', 'dailyMaxSessions', 'sessionRanges', 'breakSlots', 'semesterColors', 'peminatanColor'));
}
}
