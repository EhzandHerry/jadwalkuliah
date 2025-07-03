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
        Schema::table('ketersediaan', function (Blueprint $table) {
            // Perintah untuk mengubah nama kolom dari 'end_time' menjadi 'waktu_selesai'
            $table->renameColumn('end_time', 'waktu_selesai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ketersediaan', function (Blueprint $table) {
            // Perintah untuk mengembalikan jika di-rollback
            $table->renameColumn('waktu_selesai', 'end_time');
        });
    }
};
