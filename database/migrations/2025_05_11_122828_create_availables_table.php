<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvailablesTable extends Migration
{
    public function up()
    {
        Schema::create('availables', function (Blueprint $table) {
            $table->id(); // auto-increment id
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // foreign key to users table
            $table->string('hari'); // e.g., Monday, Tuesday
            $table->time('start_time'); // Start time of availability
            $table->time('end_time'); // End time of availability
            $table->timestamps(); // created_at and updated_at timestamps
        });
    }

    public function down()
    {
        Schema::dropIfExists('availables');
    }
}
