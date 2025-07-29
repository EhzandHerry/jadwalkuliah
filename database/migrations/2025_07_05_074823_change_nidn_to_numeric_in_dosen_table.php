<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChangeNidnToNumericInDosenTable extends Migration
{
    public function up()
    {
        // 1. Drop FK constraint secara aman via raw SQL (kalau ada)
        DB::statement('ALTER TABLE kelas DROP CONSTRAINT IF EXISTS kelas_nidn_foreign');

        // 2. Ubah tipe data nidn di dosen
        DB::statement('ALTER TABLE dosen ALTER COLUMN nidn TYPE bigint USING nidn::bigint');

        // 3. Ubah tipe data nidn di kelas agar cocok
        DB::statement('ALTER TABLE kelas ALTER COLUMN nidn TYPE bigint USING nidn::bigint');

        // 4. Tambahkan kembali foreign key
        Schema::table('kelas', function ($table) {
            $table->foreign('nidn')->references('nidn')->on('dosen')->onDelete('cascade');
        });
    }

    public function down()
    {
        // Drop FK jika ada
        DB::statement('ALTER TABLE kelas DROP CONSTRAINT IF EXISTS kelas_nidn_foreign');

        // Revert tipe data
        DB::statement('ALTER TABLE dosen ALTER COLUMN nidn TYPE varchar(255)');
        DB::statement('ALTER TABLE kelas ALTER COLUMN nidn TYPE varchar(255)');

        // Tambahkan lagi FK
        Schema::table('kelas', function ($table) {
            $table->foreign('nidn')->references('nidn')->on('dosen')->onDelete('cascade');
        });
    }
}

