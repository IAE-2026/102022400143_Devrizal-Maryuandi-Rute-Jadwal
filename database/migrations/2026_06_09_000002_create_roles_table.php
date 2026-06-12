<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();

            // Role yang datang dari SSO dosen (contoh: "user", "admin", "mahasiswa", dll)
            $table->string('cloud_role')->unique();

            // Role lokal di sistem kita
            // Nilai yang valid: admin, operator, service, viewer
            $table->string('local_role');

            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed mapping default
        // Akan diupdate setelah kita tau struktur role SSO dosen
        DB::table('roles')->insert([
            [
                'cloud_role'  => 'admin',
                'local_role'  => 'admin',
                'description' => 'Administrator penuh',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'cloud_role'  => 'operator',
                'local_role'  => 'operator',
                'description' => 'Operator layanan',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'cloud_role'  => 'service',
                'local_role'  => 'service',
                'description' => 'Service-to-service (M2M)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'cloud_role'  => 'user',
                'local_role'  => 'viewer',
                'description' => 'User biasa SSO, hanya bisa lihat data',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'cloud_role'  => 'viewer',
                'local_role'  => 'viewer',
                'description' => 'Viewer only',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
