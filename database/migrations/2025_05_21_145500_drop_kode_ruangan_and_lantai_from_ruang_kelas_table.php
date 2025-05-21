<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ruang_kelas', function (Blueprint $table) {
            $table->dropColumn(['kode_ruangan', 'lantai']);
        });
    }

    public function down(): void
    {
        Schema::table('ruang_kelas', function (Blueprint $table) {
            // Tambahkan kembali jika rollback
            $table->string('kode_ruangan')->unique()->after('id');
            $table->integer('lantai')->after('nama_ruangan');
        });
    }
};
