<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::table('mata_kuliah', function (Blueprint $table) {
        $table->integer('jumlah_kelas')->after('sks')->default(1);
    });
}

public function down()
{
    Schema::table('mata_kuliah', function (Blueprint $table) {
        $table->dropColumn('jumlah_kelas');
    });
}

};
