<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ruang_kelas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_ruangan')->unique();
            $table->string('nama_ruangan');
            $table->integer('lantai');
            $table->string('nama_gedung');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ruang_kelas');
    }
};
