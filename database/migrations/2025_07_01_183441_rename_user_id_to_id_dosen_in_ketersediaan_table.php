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
            // Perintah untuk mengubah nama kolom dari 'user_id' menjadi 'id_dosen'
            $table->renameColumn('user_id', 'id_dosen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ketersediaan', function (Blueprint $table) {
            // Perintah untuk mengembalikan jika di-rollback
            $table->renameColumn('id_dosen', 'user_id');
        });
    }
};
