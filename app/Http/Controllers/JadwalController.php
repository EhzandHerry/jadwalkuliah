<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jadwal;
use App\Models\MataKuliah;
use App\Models\RuangKelas;

class JadwalController extends Controller
{
    public function index()
    {
        $jadwals = Jadwal::all();
        return view('jadwal.index', compact('jadwals'));
    }

    public function create()
    {
        $mataKuliahs = MataKuliah::all();
        $ruangKelas = RuangKelas::all();
        return view('jadwal.create', compact('mataKuliahs', 'ruangKelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'hari' => 'required',
            'kode_mata_kuliah' => 'required',
            'kode_ruang_kelas' => 'required',
            'jam' => 'required',
        ]);

        // Cek bentrok
        $cekBentrok = Jadwal::where('hari', $request->hari)
            ->where('jam', $request->jam)
            ->where('kode_ruang_kelas', $request->kode_ruang_kelas)
            ->exists();

        if ($cekBentrok) {
            return back()->withErrors(['jadwal' => 'Ruangan sudah dipakai pada jam tersebut!']);
        }

        Jadwal::create($request->all());

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }
}
