<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueNumberToKelasTableWithForeignKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->string('unique_number')->nullable()->after('kelas');  // Add the unique_number column

            // Define the foreign key to the unique_number column in users table
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
            // Drop foreign key and column
            $table->dropForeign(['unique_number']);
            $table->dropColumn('unique_number');
        });
    }
}
