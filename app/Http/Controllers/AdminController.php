<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MataKuliah;
use App\Models\Kelas;
use App\Models\RuangKelas;
use App\Models\User;
use App\Models\Available;

class AdminController extends Controller
{
    public function dashboard()
    {
        return redirect()->route('admin.mata_kuliah.index');
    }

/**
 * Tampilkan daftar semua kelas beserta dropdown dosen
 */
public function indexMatKulDosen()
{
    $query = Kelas::has('mataKuliah')
        ->with(['mataKuliah', 'dosen']);

    if ($search = request('search')) {
        $query->whereHas('mataKuliah', function($q) use ($search) {
            $q->where('nama_matkul', 'like', "%{$search}%");
        });
    }

    // Ambil semua, lalu sort di-memory:
    $kelas = $query->get()->sort(function($a, $b) {
        // 1) Urut berdasar kode_matakuliah (alfabet)
        $cmp = strcmp($a->mataKuliah->kode_matkul, $b->mataKuliah->kode_matkul);
        if ($cmp !== 0) {
            return $cmp;
        }
        // 2) Kalau sama kode, urut berdasar kelas (A, B, C, dst)
        return strcmp($a->kelas, $b->kelas);
    });

    $dosenList = User::where('is_admin', false)
        ->orderBy('name', 'asc')
        ->get();

    return view('admin.matakuliah_dosen.index', compact('kelas', 'dosenList'));
}


/**
 * Assign dosen ke kelas (from matakuliah_dosen/index.blade.php)
 */
public function assignDosen(Request $request, $kelasId)
{
    $request->validate([
      'unique_number' => 'required|exists:users,unique_number'
    ]);
    $kelas = Kelas::findOrFail($kelasId);
    $kelas->unique_number = $request->unique_number;
    $kelas->save();

    return redirect()->route('admin.matakuliah_dosen.index')
                     ->with('success', 'Dosen berhasil di–assign.');
}

public function updateDosenKelas(Request $request, $kelasId)
{
    $request->validate([
        'unique_number' => 'required|exists:users,unique_number',
    ]);

    $kelas = Kelas::findOrFail($kelasId);
    $kelas->unique_number = $request->unique_number;
    $kelas->save();

    return redirect()
        ->route('admin.matakuliah_dosen.index')
        ->with('success', 'Dosen berhasil di–update.');
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
        'semester'     => 'required',
        'jumlah_kelas' => 'required|integer|min:1',
    ]);

    $mk = MataKuliah::create($request->only(
        'kode_matkul','nama_matkul','sks','semester','jumlah_kelas'
    ));

    $huruf = range('A','Z');
    for ($i = 0; $i < $mk->jumlah_kelas; $i++) {
        Kelas::create([
            'kode_matkul' => $mk->kode_matkul,
            'kelas'       => $huruf[$i],
        ]);
    }

    return redirect()
        ->route('admin.mata_kuliah.index')
        ->with('success', 'Mata Kuliah & Kelas berhasil ditambahkan.');
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
        'semester'     => 'required',
        'jumlah_kelas' => 'required|integer|min:1',
    ]);

    $matkul = MataKuliah::findOrFail($id);
    $matkul->update($request->only(
        'kode_matkul','nama_matkul','sks','semester','jumlah_kelas'
    ));

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
        ->with('success', 'Data Mata Kuliah & Jumlah Kelas diperbarui.');
}

    public function destroyMataKuliah($id)
    {
        MataKuliah::findOrFail($id)->delete();
        return redirect()->route('admin.mata_kuliah.index')
                         ->with('success','Mata Kuliah berhasil dihapus.');
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
        'nama_ruangan'     => 'required|string|max:255',
        'nama_gedung'      => 'required|string|max:255',
        'kapasitas'        => 'required|integer|min:1',
        'kapasitas_kelas'  => 'required|integer|min:1',
    ]);

    RuangKelas::create($request->only(
        'nama_ruangan',
        'nama_gedung',
        'kapasitas',
        'kapasitas_kelas'
    ));

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
            'nama_ruangan'=>'required|string|max:255',
            'nama_gedung' =>'required|string|max:255',
            'kapasitas'   =>'required|integer|min:1',
            'kapasitas_kelas'   =>'required|integer|min:1',
        ]);

        $ruang = RuangKelas::findOrFail($id);
        $ruang->update($request->only(
            'nama_ruangan','nama_gedung','kapasitas', 'kapasitas_kelas',
        ));

        return redirect()->route('admin.ruang_kelas.index')
                         ->with('success','Ruang Kelas berhasil diperbarui.');
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

    // 1) Ambil semua dosen non-admin beserta relasi available
    $dosen = User::where('is_admin', false)
        ->when($search, fn($q) =>
            $q->where('name', 'like', "%{$search}%")
        )
        ->orderBy('name', 'asc')
        ->with('available')      // eager-load relasi availability
        ->get();

    // 2) Susun ringkasan availability per dosen
    //    kita pakai urutan hari untuk mengetahui rentang berurutan
    $dayOrder = [
      'Senin'   => 1, 'Selasa'  => 2,
      'Rabu'    => 3, 'Kamis'   => 4,
      'Jumat'   => 5, 'Sabtu'   => 6,
      'Minggu'  => 7,
    ];

    $availabilitySummaries = [];
    foreach ($dosen as $u) {
        // ambil semua hari yang ada availability, unique dan urutkan
        $days = $u->available
                  ->pluck('hari')
                  ->unique()
                  ->sortBy(fn($h) => $dayOrder[$h])
                  ->values()
                  ->all();

        if (empty($days)) {
            $availabilitySummaries[$u->id] = '-';
            continue;
        }

        // kompres hari berurutan ke rentang
        $ranges = [];
        $start = $prev = array_shift($days);
        foreach ($days as $d) {
            if ($dayOrder[$d] === $dayOrder[$prev] + 1) {
                // masih berurutan
                $prev = $d;
            } else {
                // rentang selesai
                $ranges[] = ($start === $prev) ? $start : "$start – $prev";
                $start = $prev = $d;
            }
        }
        $ranges[] = ($start === $prev) ? $start : "$start – $prev";

        $availabilitySummaries[$u->id] = implode(', ', $ranges);
    }

    return view('admin.listdosen.index', compact(
      'dosen', 'search', 'availabilitySummaries'
    ));
}




    public function createDosen()
    {
        return view('admin.listdosen.create');
    }

    public function storeDosen(Request $request)
    {
        $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|email|unique:users,email',
            'password'=>'required|string|min:8',
            'unique_number'=>'required|unique:users,unique_number',
        ]);

        User::create([
            'name'=> $request->name,
            'email'=> $request->email,
            'password'=> bcrypt($request->password),
            'unique_number'=> $request->unique_number,
            'is_admin'=> false,
        ]);

        return redirect()->route('admin.dosen.index')
                         ->with('success','Dosen berhasil ditambahkan.');
    }

    public function editDosen($id)
    {
        $dosen = User::findOrFail($id);
        return view('admin.listdosen.edit', compact('dosen'));
    }

    public function updateDosen(Request $request, $id)
    {
        $request->validate([
            'name'=>'required|string|max:255',
            'email'=>"required|email|unique:users,email,{$id}",
            'unique_number'=>"required|unique:users,unique_number,{$id}",
        ]);

        User::findOrFail($id)->update($request->only(
            'name','email','phone','unique_number'
        ));

        return redirect()->route('admin.dosen.index')
                         ->with('success','Dosen berhasil diperbarui.');
    }

    public function deleteDosen($id)
    {
        User::findOrFail($id)->delete();
        return redirect()->route('admin.dosen.index')
                         ->with('success','Dosen berhasil dihapus.');
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
        // PERBAIKAN: Memuat relasi 'user', bukan 'dosen'.
        $available->load('user');
        
        return view('admin.available.edit', compact('available'));
    }

    /**
     * Update data available time di database.
     */
    public function updateAvailableTime(Request $request, Available $available)
    {
        $request->validate([
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
        ]);

        $available->update($request->only('start_time', 'end_time'));

        // PERBAIKAN: Menggunakan 'user_id' untuk redirect.
        return redirect()
            ->route('admin.available.manage', $available->user_id)
            ->with('success', 'Available time berhasil diperbarui.');
    }

    public function storeAvailableTimes(Request $request, $id)
{
    $request->validate([
        'hari'       => 'required|string',
        'start_time' => 'required|date_format:H:i',
        'end_time'   => 'required|date_format:H:i|after:start_time',
    ]);

    $dosen = User::findOrFail($id);

    // **cek duplikat hari** sebelum create
    if ($dosen->available()->where('hari', $request->hari)->exists()) {
        return redirect()
            ->back()
            ->withInput()
            ->with('error', "Available time untuk hari “{$request->hari}” sudah diinput sebelumnya.");
    }

    $dosen->available()->create($request->only('hari','start_time','end_time'));

    return redirect()
        ->route('admin.available.manage', $id)
        ->with('success', 'Available time berhasil ditambahkan.');
}

    public function manageAvailable($id)
    {
        $dosen    = User::findOrFail($id);
        $availables = $dosen->available;
        return view('admin.available.manage', compact('dosen','availables'));
    }

    public function deleteAvailableTime($id)
    {
        Available::findOrFail($id)->delete();
        return back()->with('success','Available time berhasil dihapus.');
    }

    public function addAvailableTime($id)
{
    $dosen = User::findOrFail($id);
    // ambil list hari yang sudah ada
    $existingDays = $dosen->available()->pluck('hari')->toArray();

    return view('admin.available.add', compact('dosen','existingDays'));
}

}
