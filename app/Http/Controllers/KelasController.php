<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\Ruangkelas;
use App\Models\User;

class KelasController extends Controller
{
    public function index()
    {
        // Get all classes and their related data
        $kelas = Kelas::with('ruangKelas', 'dosen')->get();
        $ruangKelas = RuangKelas::all(); // Get all room classes
        return view('admin.kelas.index', compact('kelas', 'ruangKelas')); // Pass data to view
    }


    public function create()
    {
        $mataKuliahs = MataKuliah::all();
        return view('admin.kelas.create', compact('mataKuliahs'));
    }

    public function store(Request $request)
{
    $request->validate([
        'kode_matkul' => 'required',
        'kelas' => 'required',
    ]);

    $exists = Kelas::where('kode_matkul', $request->kode_matkul)
        ->where('kelas', $request->kelas)
        ->exists();

    if ($exists) {
        return back()->withErrors(['kelas' => 'Kelas ini sudah terdaftar untuk mata kuliah tersebut.'])->withInput();
    }

    Kelas::create($request->all());

    return redirect()->route('admin.kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
}

// Edit Kelas method in KelasController
public function edit($id)
{
    $kelas = Kelas::findOrFail($id); // Find the class by ID
    $ruangKelas = RuangKelas::all(); // Get all available rooms
    return view('admin.kelas.edit', compact('kelas', 'ruangKelas')); // Pass data to the view
}

// Update Kelas method in KelasController
public function update(Request $request, $id)
{
    $kelas = Kelas::findOrFail($id); // Find the class by ID

    // Only update the ruang_kelas_id
    $kelas->ruang_kelas_id = $request->ruang_kelas_id; 
    $kelas->save(); // Save the updated data

    return redirect()->route('admin.kelas.index')->with('success', 'Kelas updated successfully!');
}



public function assignRuangKelas(Request $request, $kelasId)
{
    $request->validate([
        'ruang_kelas_id' => 'required|exists:ruang_kelas,id',
    ]);

    $kelas = Kelas::findOrFail($kelasId);
    $kelas->ruang_kelas_id = $request->ruang_kelas_id;
    $kelas->save();

    return redirect()->route('admin.kelas.index')->with('success', 'Ruang kelas berhasil dipilih untuk mata kuliah.');
}

}
