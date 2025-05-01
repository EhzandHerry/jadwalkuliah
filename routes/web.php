<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\JadwalController;
use App\Models\MataKuliah;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\KelasController;
use App\Models\Kelas;
use App\Models\RuangKelas;

// Redirect root '/' ke halaman login
Route::get('/', function () {
    return redirect('/login');
});

// Route login
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Route setelah login
Route::middleware('auth')->group(function () {

    // Route Admin
    Route::middleware('admin')->group(function () {
        Route::get('/admin/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin.dashboard');

        // Mata Kuliah Routes
        Route::get('/admin/mata-kuliah', [AdminController::class, 'indexMataKuliah'])->name('admin.mata_kuliah.index');
        Route::get('/admin/mata-kuliah/create', [AdminController::class, 'createMataKuliah'])->name('admin.mata_kuliah.create');
        Route::post('/admin/mata-kuliah/store', [AdminController::class, 'storeMataKuliah'])->name('admin.mata_kuliah.store');
        Route::put('/admin/mata-kuliah/{id}/update-unique', [AdminController::class, 'updateUnique'])->name('admin.mata_kuliah.updateUnique');
        Route::delete('/admin/mata-kuliah/destroy-multiple', [AdminController::class, 'destroyMultipleMataKuliah'])->name('admin.mata_kuliah.destroyMultiple');
        Route::delete('/admin/mata-kuliah/destroy-kelas/{kelasId}', [AdminController::class, 'destroyKelas'])->name('admin.mata_kuliah.destroyKelas');

        // Route untuk tambah dosen ke kelas
Route::get('/admin/mata-kuliah/kelas/{kelas}/add-dosen', [AdminController::class, 'addDosen'])->name('admin.mata_kuliah.addDosen');

// Route untuk edit dosen di kelas
Route::get('/admin/mata-kuliah/kelas/{kelas}/edit-dosen', [AdminController::class, 'editDosen'])->name('admin.mata_kuliah.editDosen');

// Route untuk menambah dosen ke kelas
Route::post('/admin/mata-kuliah/{kelasId}/add-dosen', [AdminController::class, 'assignDosen'])->name('admin.mata_kuliah.assignDosen');

// Route untuk memperbarui dosen di kelas
Route::put('/admin/mata-kuliah/{kelasId}/edit-dosen', [AdminController::class, 'updateDosen'])->name('admin.mata_kuliah.updateDosen');


        // Ruang Kelas Routes
        Route::get('/admin/ruang-kelas', [AdminController::class, 'indexRuangKelas'])->name('admin.ruang_kelas.index');
        Route::get('/admin/ruang-kelas/create', [AdminController::class, 'createRuangKelas'])->name('admin.ruang_kelas.create');
        Route::post('/admin/ruang-kelas/store', [AdminController::class, 'storeRuangKelas'])->name('admin.ruang_kelas.store');

        // Kelas Routes
        Route::get('/admin/kelas', [KelasController::class, 'index'])->name('admin.kelas.index');
        Route::get('/admin/kelas/create', [KelasController::class, 'create'])->name('admin.kelas.create');
        Route::post('/admin/kelas/store', [KelasController::class, 'store'])->name('admin.kelas.store');

        Route::post('/admin/kelas/{kelasId}/assign-ruang', [KelasController::class, 'assignRuangKelas'])->name('admin.kelas.assignRuang');

    });

    // Route User (Dosen)
    Route::middleware('user')->group(function () {
        Route::get('/user/dashboard', function () {
            // Fetching courses taught by the logged-in user (dosen)
            $userUnique = Auth::user()->unique_number;
            $kelas = Kelas::with('mataKuliah', 'ruangKelas') // Include mataKuliah and ruangKelas
                          ->where('unique_number', $userUnique)
                          ->get();

            // Pass the fetched data to the view
            return view('user.dashboard', compact('kelas'));
        })->name('user.dashboard');
    });
});
