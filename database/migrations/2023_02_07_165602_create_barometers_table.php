<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barometers', function (Blueprint $table) {
            $table->id();
            $table->string('city', 20)->unique();
            $table->string('weather_condition', 20);
            $table->unsignedSmallInteger('pressure');
            $table->unsignedSmallInteger('previous_pressure')->nullable();
            $table->boolean('is_rising')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('barometers');
    }
};
