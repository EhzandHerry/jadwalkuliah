<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    Schema::table('dosen', function (Blueprint $table) {
        // Perintah untuk mengubah nama kolom
        $table->renameColumn('unique_number', 'nidn');
    });
}

public function down(): void
{
    Schema::table('dosen', function (Blueprint $table) {
        // Perintah untuk mengembalikan jika di-rollback
        $table->renameColumn('nidn', 'unique_number');
    });
}
};
