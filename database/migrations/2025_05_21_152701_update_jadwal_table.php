<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            // Hapus kolom kode_ruang_kelas
            $table->dropColumn('kode_ruang_kelas');

            // Tambahkan kolom nama_ruangan dan unique_number
            $table->string('nama_ruangan')->after('kode_mata_kuliah');
            $table->string('unique_number')->after('nama_ruangan');
        });
    }

    public function down(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            // Tambahkan kembali kolom kode_ruang_kelas
            $table->string('kode_ruang_kelas')->after('kode_mata_kuliah');

            // Hapus kolom yang baru ditambahkan
            $table->dropColumn('nama_ruangan');
            $table->dropColumn('unique_number');
        });
    }
};
