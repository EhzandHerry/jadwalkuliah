<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeNidnToNumericInDosenTable extends Migration
{
    public function up()
    {
        DB::statement('ALTER TABLE dosen ALTER COLUMN nidn TYPE bigint USING nidn::bigint');
    }

    public function down()
    {
        DB::statement('ALTER TABLE dosen ALTER COLUMN nidn TYPE varchar(255)');
    }
}

