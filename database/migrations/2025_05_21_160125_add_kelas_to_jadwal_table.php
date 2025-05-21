<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            $table->string('kelas')->after('kode_mata_kuliah'); // Tambah kolom kelas setelah kode_mata_kuliah
        });
    }

    public function down(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            $table->dropColumn('kelas'); // Hapus kolom kelas jika rollback
        });
    }
};
