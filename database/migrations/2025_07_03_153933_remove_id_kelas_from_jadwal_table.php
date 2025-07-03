<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            // Pastikan kolom id_kelas ada sebelum mencoba menghapusnya
            if (Schema::hasColumn('jadwal', 'id_kelas')) {
                // Hapus foreign key constraint
                // Gunakan nama default Laravel: {nama_tabel}_{nama_kolom}_foreign
                $table->dropConstrainedForeignId('id_kelas'); // Laravel 8+ way (recommended)
                // ATAU jika Laravel Anda versi lama atau dropConstrainedForeignId tidak bekerja:
                // $table->dropForeign('jadwal_id_kelas_foreign');

                // Hapus kolom id_kelas
                $table->dropColumn('id_kelas');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            // Tambahkan kembali kolom id_kelas jika rollback diperlukan
            $table->unsignedBigInteger('id_kelas')->nullable(); // Sesuaikan dengan tipe data aslinya

            // Tambahkan kembali foreign key constraint jika Anda memilikinya
            // Ini hanya jika Anda yakin relasi itu diperlukan lagi setelah rollback
            // $table->foreign('id_kelas')->references('id')->on('kelas')->onDelete('set null');
        });
    }
};