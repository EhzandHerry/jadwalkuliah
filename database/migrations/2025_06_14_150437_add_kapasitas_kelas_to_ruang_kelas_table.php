<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddKapasitasKelasToRuangKelasTable extends Migration
{
    public function up()
    {
        Schema::table('ruang_kelas', function (Blueprint $table) {
            // kolom baru yang menyimpan berapa banyak kelas (A, B, C, dst) yang boleh masuk
            $table->unsignedInteger('kapasitas_kelas')->default(1)->after('kapasitas');
        });
    }

    public function down()
    {
        Schema::table('ruang_kelas', function (Blueprint $table) {
            $table->dropColumn('kapasitas_kelas');
        });
    }
}
