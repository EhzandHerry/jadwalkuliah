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
            // Perintah untuk mengubah nama kolom dari 'unique_number' menjadi 'nidn'
            $table->renameColumn('unique_number', 'nidn');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal', function (Blueprint $table) {
            // Perintah untuk mengembalikan jika di-rollback
            $table->renameColumn('nidn', 'unique_number');
        });
    }
};
