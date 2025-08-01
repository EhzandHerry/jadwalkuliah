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
        Schema::table('dosen', function (Blueprint $table) {
            // Perintah untuk mengubah nama kolom dari 'name' menjadi 'nama'
            $table->renameColumn('name', 'nama');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dosen', function (Blueprint $table) {
            // Perintah untuk mengembalikan jika di-rollback
            $table->renameColumn('nama', 'name');
        });
    }
};
