<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MataKuliah;
use App\Models\RuangKelas;
use App\Models\User;
use App\Models\Kelas;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    // Mata Kuliah CRUD
    public function indexMataKuliah()
{
    // Eager load kelas with dosen to avoid N+1 problem
    $mataKuliahs = MataKuliah::with('kelas.dosen')->get(); // eager load dosen for each kelas
    $dosenUniqueNumbers = User::where('is_admin', false)->pluck('unique_number');
    return view('admin.mata_kuliah.index', compact('mataKuliahs', 'dosenUniqueNumbers'));
}



    public function createMataKuliah()
    {
        return view('admin.mata_kuliah.create');
    }

    public function storeMataKuliah(Request $request)
{
    $request->validate([
        'kode_matkul' => 'required|unique:mata_kuliah,kode_matkul',
        'nama_matkul' => 'required',
        'sks' => 'required|integer',
        'jumlah_kelas' => 'required|integer|min:1',
    ]);

    // Simpan data mata kuliah
    MataKuliah::create([
        'kode_matkul' => $request->kode_matkul,
        'nama_matkul' => $request->nama_matkul,
        'sks' => $request->sks,
    ]);

    // Simpan data kelas berdasarkan jumlah kelas
    $jumlahKelas = (int) $request->jumlah_kelas;
    $huruf = range('A', 'Z');

    for ($i = 0; $i < $jumlahKelas; $i++) {
        Kelas::create([
            'kode_matkul' => $request->kode_matkul,
            'kelas' => $huruf[$i], // Kelas A, B, C, D, ...
        ]);
    }

    return redirect()->route('admin.mata_kuliah.index')->with('success', 'Mata Kuliah dan Kelas berhasil ditambahkan.');
}

    // Ruang Kelas CRUD
public function indexRuangKelas()
{
    $ruangKelas = RuangKelas::all();
    return view('admin.ruang_kelas.index', compact('ruangKelas'));
}

public function createRuangKelas()
{
    return view('admin.ruang_kelas.create');
}

public function storeRuangKelas(Request $request)
{
    $request->validate([
        'kode_ruangan' => 'required|unique:ruang_kelas,kode_ruangan',
        'nama_ruangan' => 'required',
        'lantai' => 'required|integer',
        'nama_gedung' => 'required',
    ]);

    RuangKelas::create($request->all());

    return redirect()->route('admin.ruang_kelas.index')->with('success', 'Ruang Kelas berhasil ditambahkan.');
}

public function editRuangKelas($id)
{
    $ruang = RuangKelas::findOrFail($id); // Find the room by ID
    return view('admin.ruang_kelas.edit', compact('ruang')); // Pass the room data to the view
}

public function updateRuangKelas(Request $request, $id)
{
    $request->validate([
        'kode_ruangan' => 'required',
        'nama_ruangan' => 'required',
        'lantai' => 'required|integer',
        'nama_gedung' => 'required',
    ]);

    // Find the room and update it
    $ruang = RuangKelas::findOrFail($id);
    $ruang->update($request->all()); // Update the room data with the new input

    return redirect()->route('admin.ruang_kelas.index')->with('success', 'Ruang Kelas updated successfully!');
}


public function destroyRuangKelas($id)
{
    // Delete the ruang kelas
    $ruang = RuangKelas::findOrFail($id);
    $ruang->delete();

    return redirect()->route('admin.ruang_kelas.index')->with('success', 'Ruang Kelas berhasil dihapus.');
}


    public function updateUnique(Request $request, $kelasId)
{
    $request->validate([
        'unique_number' => 'required|exists:users,unique_number', // Ensure the NIDN exists in the users table
    ]);

    // Find the class by ID
    $kelas = Kelas::findOrFail($kelasId);

    // Assign the instructor (dosen) by unique_number (NIDN)
    $kelas->unique_number = $request->unique_number; // Update the unique_number for this class
    $kelas->save(); // Save the changes

    return redirect()->route('admin.mata_kuliah.index')->with('success', 'Dosen berhasil diupdate untuk kelas.');
}

public function destroyMultipleMataKuliah(Request $request)
{
    $kelasIds = $request->input('kelas_ids', []);  // Ambil ID kelas yang dipilih untuk dihapus

    // Loop melalui ID kelas yang dipilih
    foreach ($kelasIds as $kelasId) {
        $kelas = Kelas::find($kelasId);
        if ($kelas) {
            // Hapus NIDN (opsional)
            $kelas->update(['unique_number' => null]);

            // Hapus kelas
            $kelas->delete();
        }
    }

    return redirect()->route('admin.mata_kuliah.index')->with('success', 'Kelas yang dipilih berhasil dihapus.');
}


public function destroyKelas($kelasId)
{
    // Temukan kelas berdasarkan ID
    $kelas = Kelas::findOrFail($kelasId);
    
    // Hapus pengaitan NIDN (opsional)
    $kelas->update(['unique_number' => null]);

    // Hapus kelas
    $kelas->delete();

    return redirect()->route('admin.mata_kuliah.index')->with('success', 'Kelas berhasil dihapus.');
}

public function addDosen($kelasId)
{
    // Ambil data kelas
    $kelas = Kelas::findOrFail($kelasId);
    
    // Ambil semua dosen yang tersedia
    $dosen = User::where('is_admin', false)->get();

    return view('admin.mata_kuliah.add-dosen', compact('kelas', 'dosen'));
}

public function editDosenKelas($kelasId)
{
    // Ambil data kelas
    $kelas = Kelas::findOrFail($kelasId);
    
    // Ambil semua dosen yang tersedia
    $dosen = User::where('is_admin', false)->get();

    return view('admin.mata_kuliah.edit-dosen', compact('kelas', 'dosen'));
}

public function assignDosen(Request $request, $kelasId)
{
    // Validasi bahwa unique_number ada dan sesuai dengan dosen yang ada
    $request->validate([
        'unique_number' => 'required|exists:users,unique_number', // pastikan dosen ada
    ]);

    // Temukan kelas berdasarkan ID
    $kelas = Kelas::findOrFail($kelasId);

    // Update unique_number (mengassign dosen ke kelas)
    $kelas->unique_number = $request->unique_number;
    $kelas->save(); // Simpan perubahan

    return redirect()->route('admin.mata_kuliah.index')->with('success', 'Dosen berhasil ditambahkan ke kelas.');
}

public function updateDosenKelas(Request $request, $kelasId)
{
    // Validasi bahwa unique_number ada dan sesuai dengan dosen yang ada
    $request->validate([
        'unique_number' => 'required|exists:users,unique_number', // pastikan dosen ada
    ]);

    // Temukan kelas berdasarkan ID
    $kelas = Kelas::findOrFail($kelasId);

    // Update unique_number (mengupdate dosen di kelas)
    $kelas->unique_number = $request->unique_number;
    $kelas->save(); // Simpan perubahan

    return redirect()->route('admin.mata_kuliah.index')->with('success', 'Dosen berhasil diperbarui di kelas.');
}

public function listDosen()
{
    $dosen = User::where('is_admin', false)->get();
    return view('admin.listdosen.index', compact('dosen'));
}

public function showDosen($id)
{
    // Find the dosen by ID
    $dosen = User::findOrFail($id);

    // Return the detail view and pass the dosen data
    return view('admin.listdosen.detail', compact('dosen'));
}

public function createDosen()
{
    return view('admin.listdosen.create');
}

public function storeDosen(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:8',
        'phone' => 'required|string|max:15',
        'unique_number' => 'required|unique:users,unique_number',
    ]);

    // Create new dosen (user) with is_admin set to false
    User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => bcrypt($request->password),
        'phone' => $request->phone,
        'unique_number' => $request->unique_number,
        'is_admin' => false,
    ]);

    return redirect()->route('admin.dosen.index')->with('success', 'Dosen berhasil ditambahkan.');
}

public function deleteDosen($id)
{
    // Find the dosen by ID
    $dosen = User::findOrFail($id);

    // Delete the dosen
    $dosen->delete();

    // Redirect back to the list with a success message
    return redirect()->route('admin.dosen.index')->with('success', 'Dosen berhasil dihapus.');
}

public function editDosen($id)
{
    // Find the dosen by ID
    $dosen = User::findOrFail($id);

    // Return the edit view and pass the dosen data
    return view('admin.listdosen.edit', compact('dosen'));
}

public function updateDosen(Request $request, $id)
{
    // Validate the input data
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $id,
        'phone' => 'required|string|max:15',
        'unique_number' => 'required|unique:users,unique_number,' . $id,
    ]);

    // Find the dosen by ID and update the data
    $dosen = User::findOrFail($id);
    $dosen->update([
        'name' => $request->name,
        'email' => $request->email,
        'phone' => $request->phone,
        'unique_number' => $request->unique_number,
    ]);

    // Redirect to the list page with a success message
    return redirect()->route('admin.dosen.index')->with('success', 'Dosen berhasil diperbarui.');
}

}
