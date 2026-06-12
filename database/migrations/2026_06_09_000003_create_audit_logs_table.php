<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Referensi booking dari Ticket & Payment Service
            $table->string('booking_reference');

            // Route yang direservasi
            $table->unsignedBigInteger('route_id');

            // Jumlah kursi yang direservasi
            $table->integer('reserved_seats');

            // Nama aktivitas yang diaudit (RESERVE_SEATS)
            $table->string('action');

            // ReceiptNumber yang dikembalikan oleh SOAP server dosen
            // Null jika SOAP gagal
            $table->string('receipt_number')->nullable();

            // Status hasil SOAP audit: SUCCESS atau FAILED
            $table->string('audit_status')->default('PENDING');

            // Raw XML/JSON yang dikirim ke SOAP server
            $table->text('request_payload')->nullable();

            // Raw XML response dari SOAP server
            $table->text('response_payload')->nullable();

            $table->timestamps();

            // Index untuk pencarian cepat
            $table->index('booking_reference');
            $table->index('route_id');
            $table->index('audit_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
