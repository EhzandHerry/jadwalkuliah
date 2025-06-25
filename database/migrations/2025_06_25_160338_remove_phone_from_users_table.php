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
        Schema::table('users', function (Blueprint $table) {
            // Menghapus kolom 'phone' dari tabel
            $table->dropColumn('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Menambahkan kembali kolom 'phone' jika migration di-rollback
            // ->after('password') untuk menempatkannya kembali di posisi semula
            $table->string('phone')->nullable()->after('password');
        });
    }
};
