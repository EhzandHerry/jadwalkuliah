<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeNidnToNumericInDosenTable extends Migration
{
    public function up()
    {
        // Drop foreign key constraint dari tabel kelas
        Schema::table('kelas', function ($table) {
            $table->dropForeign(['nidn']); // sesuaikan nama kolomnya
        });

        // Ubah tipe kolom nidn di dosen
        DB::statement('ALTER TABLE dosen ALTER COLUMN nidn TYPE bigint USING nidn::bigint');

        // Ubah tipe kolom nidn di kelas juga (agar cocok untuk relasi foreign key)
        DB::statement('ALTER TABLE kelas ALTER COLUMN nidn TYPE bigint USING nidn::bigint');

        // Tambahkan kembali foreign key
        Schema::table('kelas', function ($table) {
            $table->foreign('nidn')->references('nidn')->on('dosen');
        });
    }

    public function down()
    {
        Schema::table('kelas', function ($table) {
            $table->dropForeign(['nidn']);
        });

        DB::statement('ALTER TABLE dosen ALTER COLUMN nidn TYPE varchar(255)');
        DB::statement('ALTER TABLE kelas ALTER COLUMN nidn TYPE varchar(255)');

        Schema::table('kelas', function ($table) {
            $table->foreign('nidn')->references('nidn')->on('dosen');
        });
    }
}

