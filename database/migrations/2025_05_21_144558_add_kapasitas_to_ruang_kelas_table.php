<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ruang_kelas', function (Blueprint $table) {
            // tambahkan kolom kapasitas setelah nama_gedung
            $table->integer('kapasitas')->after('nama_gedung');
        });
    }

    public function down(): void
    {
        Schema::table('ruang_kelas', function (Blueprint $table) {
            // hapus kolom kapasitas jika rollback
            $table->dropColumn('kapasitas');
        });
    }
};
