<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRuangKelasIdToKelasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->unsignedBigInteger('ruang_kelas_id')->nullable();  // Menambahkan kolom ruang_kelas_id
            $table->foreign('ruang_kelas_id')->references('id')->on('ruang_kelas')->onDelete('set null');  // Foreign key ke tabel ruang_kelas
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->dropForeign(['ruang_kelas_id']);  // Drop foreign key
            $table->dropColumn('ruang_kelas_id');  // Drop kolom ruang_kelas_id
        });
    }
}
