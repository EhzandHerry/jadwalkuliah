<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This method is executed when you run 'php artisan migrate'.
     * It changes the 'nidn' column to a numeric type.
     */
    public function up(): void
    {
        Schema::table('dosen', function (Blueprint $table) {
            // Mengubah tipe data kolom 'nidn' menjadi unsignedBigInteger.
            // Tipe ini dipilih karena NIDN adalah angka 10 digit dan tidak pernah negatif.
            // Integer biasa tidak cukup untuk menampung 10 digit.
            // Method change() digunakan untuk memodifikasi kolom yang sudah ada.
            $table->unsignedBigInteger('nidn')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * This method is executed when you run 'php artisan migrate:rollback'.
     * It reverts the 'nidn' column back to its original varchar type.
     */
    public function down(): void
    {
        Schema::table('dosen', function (Blueprint $table) {
            // Mengembalikan tipe data kolom 'nidn' ke varchar(10)
            // jika migration di-rollback. Sesuaikan panjang '10' jika sebelumnya berbeda.
            $table->string('nidn', 10)->change();
        });
    }
};
