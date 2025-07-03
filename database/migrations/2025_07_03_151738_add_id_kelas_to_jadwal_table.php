<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            // Tambahkan kolom id_kelas
            $table->unsignedBigInteger('id_kelas')->nullable()->after('kelas'); // nullable jika ada data lama yang mungkin belum punya id_kelas

            // Tambahkan foreign key constraint (opsional tapi bagus untuk integritas data)
            $table->foreign('id_kelas')->references('id')->on('kelas')->onDelete('set null'); // Atau onDelete('cascade')
        });

        // *** PENTING: Update data yang sudah ada ***
        // Anda perlu mengisi kolom id_kelas berdasarkan kolom 'kelas' yang sudah ada.
        // Ini bisa dilakukan dengan query SQL di sini atau secara manual setelah migrasi.
        // Contoh SQL untuk mengupdate:
        // UPDATE jadwal j
        // JOIN kelas k ON j.kelas = k.kelas
        // SET j.id_kelas = k.id;
    }

    public function down(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            // Hapus foreign key constraint terlebih dahulu
            $table->dropForeign(['id_kelas']);
            // Hapus kolom id_kelas
            $table->dropColumn('id_kelas');
        });
    }
};