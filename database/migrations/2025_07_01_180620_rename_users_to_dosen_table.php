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
        // Perintah untuk mengubah nama tabel dari 'users' menjadi 'dosen'
        Schema::rename('users', 'dosen');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Perintah untuk mengembalikan nama tabel jika di-rollback
        Schema::rename('dosen', 'users');
    }
};
