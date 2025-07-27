<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MataKuliah;
use App\Models\Kelas;
use App\Models\RuangKelas;
use App\Models\User;
use App\Models\Available;
use App\Models\JadwalKuliah;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;


class AdminController extends Controller
{
    public function showHome()
    {
        Carbon::setLocale('id');

        // Mengambil hari saat ini secara dinamis
        // Untuk pengujian, Anda bisa tetap menggunakan $hariIni = 'Senin';
        $hariIni = Carbon::now()->isoFormat('dddd'); // Mendapatkan nama hari dalam bahasa Indonesia (e.g., "Senin", "Selasa")
        // Jika ingin spesifik hari tertentu untuk pengujian, uncomment baris di bawah:
        // $hariIni = 'Senin';


        // Mengambil jadwal kuliah
$jadwalHariIni = JadwalKuliah::with(['mataKuliah', 'dosen', 'ruangKelas']) // Hapus 'kelas'
    ->whereRaw('LOWER(TRIM(hari)) = ?', [strtolower(trim($hariIni))])
    ->orderBy('jam', 'asc')
    ->get();
        

        // Jangan gunakan dd() di production! Uncomment baris di bawah ini setelah pengujian berhasil.
        // dd($jadwalHariIni);

        return view('admin.dashboard', compact('jadwalHariIni', 'hariIni'));
    }

    public function dashboard()
    {
        return redirect()->route('admin.mata_kuliah.index');
    }

/**
 * Tampilkan daftar semua kelas beserta dropdown dosen
 */
public function indexMatKulDosen(Request $request)
    {
        $query = Kelas::has('mataKuliah')
                        ->with(['mataKuliah', 'dosen']);

        if ($search = $request->input('search')) {
            $query->whereHas('mataKuliah', function($q) use ($search) {
                $q->where('nama_matkul', 'like', "%{$search}%");
            });
        }

        if ($semesterType = $request->input('semester_type')) {
            $query->whereHas('mataKuliah', function ($q) use ($semesterType) {
                if ($semesterType === 'gasal') {
                    $q->whereRaw('semester % 2 != 0');
                } elseif ($semesterType === 'genap') {
                    $q->whereRaw('semester % 2 = 0');
                }
            });
        }

        $kelas = $query->join('mata_kuliah', 'kelas.kode_matkul', '=', 'mata_kuliah.kode_matkul')
                       ->orderBy('mata_kuliah.kode_matkul', 'asc')
                       ->orderBy('kelas.kelas', 'asc')
                       ->select('kelas.*')
                       ->get();

        $dosenList = User::where('is_admin', false)
            ->orderBy('nama', 'asc') // Menggunakan 'nama'
            ->get();

        return view('admin.matakuliah_dosen.index', compact('kelas', 'dosenList'));
    }

    public function assignDosen(Request $request, $kelasId)
    {
        // Menggunakan 'nidn' dan tabel 'dosen'
        $request->validate(['nidn' => 'required|exists:dosen,nidn']);
        $kelas = Kelas::findOrFail($kelasId);
        $kelas->nidn = $request->nidn;
        $kelas->save();
        return redirect()->route('admin.matakuliah_dosen.index')->with('success', 'Dosen berhasil disimpan.');
    }

    public function updateDosenKelas(Request $request, $kelasId)
    {
        // Menggunakan 'nidn' dan tabel 'dosen'
        $request->validate(['nidn' => 'required|exists:dosen,nidn']);
        $kelas = Kelas::findOrFail($kelasId);
        $newDosenNidn = $request->nidn;

        if ($kelas->nidn === $newDosenNidn) {
            return redirect()->route('admin.matakuliah_dosen.index')->with('success', 'Dosen berhasil diubah (tidak ada perubahan).');
        }

        if ($kelas->nidn) {
            $isOldDosenScheduled = JadwalKuliah::where('kode_matkul', $kelas->kode_matkul)
                ->where('kelas', $kelas->kelas)
                ->where('nidn', $kelas->nidn) // Menggunakan 'nidn'
                ->exists();
            if ($isOldDosenScheduled) {
                $oldDosenName = optional($kelas->dosen)->nama ?? 'dosen sebelumnya'; // Menggunakan 'nama'
                return redirect()->back()->with('error', "Gagal mengubah! Dosen {$oldDosenName} sudah memiliki jadwal untuk kelas ini. Hapus jadwal terlebih dahulu untuk mengganti dosen.");
            }
        }
        
        $isNewDosenTeachingThisMatkul = JadwalKuliah::where('kode_matkul', $kelas->kode_matkul)
            ->where('nidn', $newDosenNidn) // Menggunakan 'nidn'
            ->exists();
        if ($isNewDosenTeachingThisMatkul) {
            $newDosen = User::where('nidn', $newDosenNidn)->first(); // Menggunakan 'nidn'
            $matkulName = optional($kelas->mataKuliah)->nama_matkul ?? $kelas->kode_matkul;
            return redirect()->back()->with('error', "Gagal ubah! Dosen {$newDosen->nama} sudah terdaftar di jadwal untuk mengajar mata kuliah {$matkulName}."); // Menggunakan 'nama'
        }

        $kelas->nidn = $newDosenNidn; // Menggunakan 'nidn'
        $kelas->save();
        return redirect()->route('admin.matakuliah_dosen.index')->with('success', 'Dosen berhasil diubah.');
    }

    /**
     * Hapus (un-assign) dosen dari sebuah kelas.
     */
    public function deleteDosenKelas($kelasId)
    {
        $kelas = Kelas::with('dosen', 'mataKuliah')->findOrFail($kelasId);
        if ($kelas->nidn) { // Menggunakan 'nidn'
            $isScheduled = JadwalKuliah::where('kode_matkul', $kelas->kode_matkul)
                                       ->where('kelas', $kelas->kelas)
                                       ->where('nidn', $kelas->nidn) // Menggunakan 'nidn'
                                       ->exists();
            if ($isScheduled) {
                $dosenName = optional($kelas->dosen)->nama ?? 'Dosen ini'; // Menggunakan 'nama'
                $matkulName = optional($kelas->mataKuliah)->nama_matkul ?? $kelas->kode_matkul;
                return redirect()->back()->with('error', "Gagal menghapus! Dosen {$dosenName} sudah memiliki jadwal untuk mata kuliah {$matkulName} kelas {$kelas->kelas}. Hapus jadwal di halaman Manajemen Jadwal.");
            }
        }
        $kelas->nidn = null; // Menggunakan 'nidn'
        $kelas->save();
        return redirect()->route('admin.matakuliah_dosen.index')->with('success', 'Dosen berhasil dihapus dari Mata Kuliah.');
    }

    //
    // === CRUD MATAKULIAH ===
    //
    public function indexMataKuliah()
{
    $query = MataKuliah::with('kelas.dosen')
                       ->orderBy('nama_matkul', 'asc');

    // filter nama mata kuliah
    if ($search = request('search')) {
        $query->where('nama_matkul', 'like', "%{$search}%");
    }

    // filter semester genap/gasal
    if ($semesterFilter = request('semester_filter')) {
        if ($semesterFilter === 'Genap') {
            // semester genap: 2,4,6,8
            $query->whereIn('semester', [2,4,6,8]);
        } elseif ($semesterFilter === 'Gasal') {
            // semester ganjil: 1,3,5,7
            $query->whereIn('semester', [1,3,5,7]);
        }
    }

    $mataKuliahs = $query->get();
    $dosenList   = User::where('is_admin', false)->get();

    return view('admin.mata_kuliah.index', compact('mataKuliahs', 'dosenList'));
}

    public function createMataKuliah()
    {
        return view('admin.mata_kuliah.create');
    }

     public function storeMataKuliah(Request $request)
{
    $request->validate([
        'kode_matkul'  => 'required|unique:mata_kuliah,kode_matkul',
        'nama_matkul'  => 'required',
        'sks'          => 'required|integer',
        'semester'     => 'required|integer',
        'jumlah_kelas' => 'required|integer|min:1',
        'peminatan'    => 'nullable|string|in:Programming,Data,UX,Network',
    ]);

    try {
        DB::beginTransaction();
        
        // Cek dan hapus kelas lama jika ada (untuk kasus data tidak konsisten)
        Kelas::where('kode_matkul', $request->kode_matkul)->delete();
        
        $mk = MataKuliah::create($request->only(
            'kode_matkul','nama_matkul','sks','semester','peminatan','jumlah_kelas'
        ));

        $huruf = range('A','Z');
        for ($i = 0; $i < $mk->jumlah_kelas; $i++) {
            Kelas::create([
                'kode_matkul' => $mk->kode_matkul,
                'kelas'       => $huruf[$i],
            ]);
        }
        
        DB::commit();

        return redirect()
            ->route('admin.mata_kuliah.index')
            ->with('success', 'Mata Kuliah berhasil ditambahkan.');
            
    } catch (\Exception $e) {
        DB::rollback();
        
        return redirect()
            ->route('admin.mata_kuliah.index')
            ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

    public function editMataKuliah($id)
    {
        $matkul = MataKuliah::findOrFail($id);
        return view('admin.mata_kuliah.edit', compact('matkul'));
    }

    public function updateMataKuliah(Request $request, $id)
    {
        $request->validate([
            'kode_matkul'  => 'required|unique:mata_kuliah,kode_matkul,'.$id,
            'nama_matkul'  => 'required',
            'sks'          => 'required|integer',
            'semester'     => 'required|integer',
            'jumlah_kelas' => 'required|integer|min:1',
            // PENAMBAHAN: Validasi untuk peminatan (boleh null/kosong)
            'peminatan'    => 'nullable|string|in:Programming,Data,UX,Network',
        ]);

        $matkul = MataKuliah::findOrFail($id);
        
        // PENAMBAHAN: Memasukkan 'peminatan' ke dalam data yang diupdate
        $matkul->update($request->only(
            'kode_matkul','nama_matkul','sks','semester','peminatan','jumlah_kelas'
        ));

        // Logika untuk menyesuaikan jumlah kelas (tidak diubah)
        $existing = $matkul->kelas()->orderBy('kelas')->get();
        $oldCount = $existing->count();
        $newCount = $matkul->jumlah_kelas;
        $huruf    = range('A','Z');

        if ($newCount > $oldCount) {
            for ($i = $oldCount; $i < $newCount; $i++) {
                Kelas::create([
                    'kode_matkul' => $matkul->kode_matkul,
                    'kelas'       => $huruf[$i],
                ]);
            }
        } elseif ($newCount < $oldCount) {
            $toDelete = $existing->slice($newCount);
            foreach ($toDelete as $k) {
                $k->delete();
            }
        }

        return redirect()
            ->route('admin.mata_kuliah.index')
            ->with('success', 'Data Mata Kuliah berhasil diperbarui.');
    }

    public function destroyMataKuliah($id)
{
    try {
        DB::beginTransaction();
        
        $mataKuliah = MataKuliah::findOrFail($id);
        
        // Hapus semua kelas yang terkait
        Kelas::where('kode_matkul', $mataKuliah->kode_matkul)->delete();
        
        // Hapus mata kuliah
        $mataKuliah->delete();
        
        DB::commit();
        
        return redirect()->route('admin.mata_kuliah.index')
                         ->with('success', 'Mata Kuliah beserta kelasnya berhasil dihapus.');
    } catch (\Exception $e) {
        DB::rollback();
        
        return redirect()->route('admin.mata_kuliah.index')
                         ->with('error', 'Terjadi kesalahan saat menghapus mata kuliah: ' . $e->getMessage());
    }
}

    //
    // === CRUD RUANG KELAS ===
    //
    public function indexRuangKelas()
{
    // ambil semua ruang kelas, urut berdasarkan nama_ruangan (A→Z)
    $ruangKelas = RuangKelas::orderBy('nama_ruangan', 'asc')->get();

    return view('admin.ruang_kelas.index', compact('ruangKelas'));
}


    public function createRuangKelas()
    {
        return view('admin.ruang_kelas.create');
    }

    public function storeRuangKelas(Request $request)
{
    $request->validate([
        'nama_ruangan'     => 'required|string|max:255|unique:ruang_kelas,nama_ruangan',
        'nama_gedung'      => 'required|string|max:255',
        'kapasitas_kelas'  => 'required|integer|min:1',
    ], [
        'nama_ruangan.required' => 'Nama ruangan wajib diisi.',
        'nama_ruangan.unique' => 'Nama ruangan ini sudah terdaftar.',
        'nama_gedung.required' => 'Nama gedung wajib diisi.',
        'kapasitas_kelas.required' => 'Kapasitas kelas wajib diisi.',
        'kapasitas_kelas.min' => 'Kapasitas kelas minimal harus 1.',
    ]);

    // Hitung kapasitas otomatis berdasarkan kapasitas_kelas
    $kapasitas = $request->kapasitas_kelas * 50;

    RuangKelas::create([
        'nama_ruangan' => $request->nama_ruangan,
        'nama_gedung' => $request->nama_gedung,
        'kapasitas' => $kapasitas,
        'kapasitas_kelas' => $request->kapasitas_kelas
    ]);

    return redirect()->route('admin.ruang_kelas.index')
                     ->with('success','Ruang Kelas berhasil ditambahkan.');
}


    public function editRuangKelas($id)
    {
        $ruang = RuangKelas::findOrFail($id);
        return view('admin.ruang_kelas.edit', compact('ruang'));
    }

    public function updateRuangKelas(Request $request, $id)
{
    $request->validate([
        'nama_ruangan' => 'required|string|max:255|unique:ruang_kelas,nama_ruangan,' . $id,
        'nama_gedung'  => 'required|string|max:255',
        'kapasitas_kelas' => 'required|integer|min:1',
    ], [
        // Pesan error kustom
        'nama_ruangan.unique' => 'Nama ruangan ini sudah terdaftar.',
        'nama_ruangan.required' => 'Nama ruangan wajib diisi.',
        'nama_gedung.required' => 'Nama gedung wajib diisi.',
        'kapasitas_kelas.required' => 'Kapasitas kelas wajib diisi.',
        'kapasitas_kelas.min' => 'Kapasitas kelas minimal 1.',
    ]);

    $ruang = RuangKelas::findOrFail($id);
    
    // Hitung kapasitas otomatis
    $kapasitas = $request->kapasitas_kelas * 50;
    
    $ruang->update([
        'nama_ruangan' => $request->nama_ruangan,
        'nama_gedung' => $request->nama_gedung,
        'kapasitas' => $kapasitas,
        'kapasitas_kelas' => $request->kapasitas_kelas,
    ]);

    return redirect()->route('admin.ruang_kelas.index')
                     ->with('success', 'Ruang Kelas berhasil diperbarui.');
}

    public function destroyRuangKelas($id)
    {
        RuangKelas::findOrFail($id)->delete();
        return redirect()->route('admin.ruang_kelas.index')
                         ->with('success','Ruang Kelas berhasil dihapus.');
    }

    //
    // === CRUD DOSEN ===
    //
     public function listDosen(Request $request)
    {
        $search = $request->input('search');
        $dosen = User::where('is_admin', false)
            ->when($search, fn($q) => $q->where('nama', 'like', "%{$search}%")) // Menggunakan 'nama'
            ->orderBy('nama', 'asc') // Menggunakan 'nama'
            ->with('available')
            ->get();
        
        $dayOrder = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];
        $availabilitySummaries = [];
        foreach ($dosen as $u) {
            $days = $u->available->pluck('hari')->unique()->sortBy(fn($h) => $dayOrder[$h] ?? 8)->values()->all();
            if (empty($days)) {
                $availabilitySummaries[$u->id] = '-';
                continue;
            }
            $ranges = [];
            $start = $prev = array_shift($days);
            foreach ($days as $d) {
                if (isset($dayOrder[$d], $dayOrder[$prev]) && $dayOrder[$d] === $dayOrder[$prev] + 1) {
                    $prev = $d;
                } else {
                    $ranges[] = ($start === $prev) ? $start : "$start – $prev";
                    $start = $prev = $d;
                }
            }
            $ranges[] = ($start === $prev) ? $start : "$start – $prev";
            $availabilitySummaries[$u->id] = implode(', ', $ranges);
        }
        return view('admin.listdosen.index', compact('dosen', 'search', 'availabilitySummaries'));
    }

    public function createDosen()
    {
        return view('admin.listdosen.create');
    }

    public function storeDosen(Request $request)
{
    $request->validate([
        'nama' => 'required|string|max:255',
        'email' => 'required|email|unique:dosen,email',
        'password' => 'required|string|min:8',
        'nidn' => 'required|unique:dosen,nidn|numeric|digits:10',
    ], [
        'nama.required' => 'Nama dosen wajib diisi.',
        'email.unique' => 'Email ini sudah terdaftar.',
        'password.min' => 'Password minimal harus 8 karakter.',
        'nidn.unique' => 'NIDN ini sudah terdaftar.',
        'nidn.numeric' => 'NIDN harus berupa angka.',
        'nidn.digits' => 'NIDN harus terdiri dari tepat 10 angka.',
    ]);
    
    User::create([
        'nama' => $request->nama,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'nidn' => $request->nidn,
        'is_admin' => false,
    ]);
    
    return redirect()->route('admin.dosen.index')->with('success','Dosen berhasil ditambahkan.');
}

    public function editDosen($id)
    {
        $dosen = User::findOrFail($id);
        return view('admin.listdosen.edit', compact('dosen'));
    }

    public function updateDosen(Request $request, $id)
{
    $request->validate([
        'nama' => 'required|string|max:255',
        'email' => "required|email|unique:dosen,email,{$id}",
        'nidn' => "required|numeric|min_digits:10|max_digits:10|unique:dosen,nidn,{$id}",
    ], [
        'nama.required' => 'Nama dosen wajib diisi.',
        'email.unique' => 'Email ini sudah terdaftar.',
        'nidn.unique' => 'NIDN ini sudah terdaftar.',
        'nidn.numeric' => 'NIDN harus berupa angka.',
        'nidn.min_digits' => 'NIDN harus terdiri dari minimal 10 digit.',
        'nidn.max_digits' => 'NIDN tidak boleh lebih dari 10 digit.',
    ]);

    $dosen = User::findOrFail($id);
    $oldNidn = $dosen->nidn;
    
    // Jika NIDN akan diubah dan ada referensi di kelas
    if ($dosen->nidn != $request->nidn) {
        $nidnExistsInKelas = DB::table('kelas')->where('nidn', $oldNidn)->exists();

        if ($nidnExistsInKelas) {
            // Opsi 1: Blokir perubahan (seperti solusi di atas)
            return redirect()->back()
                ->withInput()
                ->with('error_nidn_kelas', 'NIDN dosen ini tidak dapat diubah karena telah terdaftar pada kelas di suatu mata kuliah!');
            
            // Opsi 2: Update juga referensi di tabel kelas (gunakan transaction)
            // DB::transaction(function () use ($dosen, $request, $oldNidn) {
            //     $dosen->update($request->only('nama', 'email', 'nidn'));
            //     DB::table('kelas')->where('nidn', $oldNidn)->update(['nidn' => $request->nidn]);
            // });
        }
    }

    // Jika tidak ada masalah, lakukan update
    $dosen->update($request->only('nama', 'email', 'nidn'));
    
    return redirect()->route('admin.dosen.index')->with('success','Dosen berhasil diperbarui.');
}

    public function deleteDosen($id)
    {
        try {
            // Menggunakan model User sesuai kode Anda, pastikan ini benar
            User::findOrFail($id)->delete();
            return redirect()->route('admin.dosen.index')->with('success', 'Dosen berhasil dihapus.');
        } catch (QueryException $e) {
            // Cek apakah error code adalah 1451 (foreign key constraint violation)
            if ($e->errorInfo[1] == 1451) {
                return redirect()->route('admin.dosen.index')->with('error', 'Dosen tidak dapat dihapus karena masih terdaftar di kelas.');
            }

            // Jika error selain constraint, lempar kembali exception
            throw $e;
        }
    }

    //
    // === AVAILABLE TIME ===
    //
     public function showAvailableTimes()
    {
        $dosen = User::where('is_admin', false)->get();
        return view('admin.available.dashboard', compact('dosen'));
    }   

     public function editAvailableTime(Available $available)
    {
        $available->load('user');
        return view('admin.available.edit', compact('available'));
    }

    /**
     * Update data available time di database.
     */
    public function updateAvailableTime(Request $request, Available $available)
    {
        $request->validate([
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
        ]);
        $available->update($request->only('waktu_mulai', 'waktu_selesai'));
        return redirect()->route('admin.available.manage', $available->id_dosen)->with('success', 'Waktu ketersediaan berhasil diperbarui.');
    }

      public function storeAvailableTimes(Request $request, $id)
    {
        // PERUBAHAN: Menambahkan pesan error kustom untuk aturan 'after'
        $request->validate([
            'hari' => 'required|string',
            'waktu_mulai' => 'required|date_format:H:i',
            'waktu_selesai' => 'required|date_format:H:i|after:waktu_mulai',
        ], [
            'waktu_selesai.after' => 'Waktu selesai harus setelah waktu mulai.'
        ]);

        $dosen = User::findOrFail($id);
        
        if ($dosen->available()->where('hari', $request->hari)->exists()) {
            return redirect()->back()->withInput()->with('error', "Waktu ketersediaan untuk hari “{$request->hari}” sudah diinput sebelumnya.");
        }
        
        $dosen->available()->create($request->only('hari','waktu_mulai','waktu_selesai'));
        
        return redirect()->route('admin.available.manage', $id)->with('success', 'Waktu ketersediaan berhasil ditambahkan.');
    }

    public function manageAvailable($id)
    {
        $dosen = User::findOrFail($id);
        $availables = $dosen->available;
        return view('admin.available.manage', compact('dosen','availables'));
    }

    public function deleteAvailableTime($id)
    {
        Available::findOrFail($id)->delete();
        return back()->with('success','Waktu Ketersediaan berhasil dihapus.');
    }

    public function addAvailableTime($id)
    {
        $dosen = User::findOrFail($id);
        $existingDays = $dosen->available()->pluck('hari')->toArray();
        return view('admin.available.add', compact('dosen','existingDays'));
    }

}
