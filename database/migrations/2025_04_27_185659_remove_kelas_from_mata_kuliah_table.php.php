<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mata_kuliah', function (Blueprint $table) {
            if (Schema::hasColumn('mata_kuliah', 'kelas')) {
                $table->dropColumn('kelas');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mata_kuliah', function (Blueprint $table) {
            $table->string('kelas')->after('sks')->nullable();
        });
    }
};