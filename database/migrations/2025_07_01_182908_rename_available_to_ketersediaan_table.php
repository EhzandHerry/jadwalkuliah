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
        // Perintah untuk mengubah nama tabel dari 'available' menjadi 'ketersediaan'
        Schema::rename('availables', 'ketersediaan');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Perintah untuk mengembalikan nama tabel jika di-rollback
        Schema::rename('ketersediaan', 'availables');
    }
};
