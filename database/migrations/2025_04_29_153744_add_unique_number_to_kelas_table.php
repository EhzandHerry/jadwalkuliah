<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueNumberToKelasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kelas', function (Blueprint $table) {
            // Add unique_number column to kelas table
            $table->string('unique_number')->nullable()->after('kelas');  // Adding unique_number column
            // Define the foreign key constraint
            $table->foreign('unique_number')->references('unique_number')->on('users');
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
            // Drop foreign key first
            $table->dropForeign(['unique_number']);
            // Then drop the column
            $table->dropColumn('unique_number');
        });
    }
}
