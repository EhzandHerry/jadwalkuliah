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
            // Perintah untuk mengubah nama kolom dari 'start_time' menjadi 'waktu_mulai'
            $table->renameColumn('start_time', 'waktu_mulai');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ketersediaan', function (Blueprint $table) {
            // Perintah untuk mengembalikan jika di-rollback
            $table->renameColumn('waktu_mulai', 'start_time');
        });
    }
};
