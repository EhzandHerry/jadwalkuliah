<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            // Hapus foreign key constraint dulu
            $table->dropForeign(['ruang_kelas_id']);  // gunakan nama kolom dalam array

            // Baru hapus kolomnya
            $table->dropColumn('ruang_kelas_id');
        });
    }

    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->unsignedBigInteger('ruang_kelas_id')->nullable();

            // Jika perlu, tambahkan foreign key constraint kembali
            $table->foreign('ruang_kelas_id')->references('id')->on('ruang_kelas')->onDelete('set null');
        });
    }
};
