<?php

namespace App\Http\Controllers;

use App\Models\Kelas;  // Import the Kelas model
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index() {
        // Fetching all Kelas data with related MataKuliah, RuangKelas, and Dosen (User)
        $kelas = Kelas::with(['mataKuliah', 'ruangKelas', 'dosen'])->get();

        // Passing the fetched data to the view
        return view('dashboard', compact('kelas'));
    }
}
