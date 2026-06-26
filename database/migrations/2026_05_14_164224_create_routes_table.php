<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('route_code')->unique();
            $table->string('origin');
            $table->string('destination');
            $table->string('departure_point');
            $table->string('arrival_point');
            $table->date('departure_date');
            $table->time('departure_time');
            $table->time('arrival_time');
            $table->string('vehicle_type');
            $table->integer('price');
            $table->integer('seat_capacity');
            $table->integer('available_seats');
            $table->string('status')->default('available');
            $table->timestamps();

            $table->index('status');
            $table->index('origin');
            $table->index('destination');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routes');
    }
};
