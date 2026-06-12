<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Identifier unik user dari SSO (isi field "sub" di JWT payload)
            $table->string('sso_sub')->nullable()->unique()->after('email');

            // Role yang datang dari SSO dosen (cloud role)
            $table->string('cloud_role')->nullable()->after('sso_sub');

            // Role lokal di sistem kita (admin/operator/service/viewer)
            $table->string('local_role')->nullable()->default('viewer')->after('cloud_role');

            // Password tidak wajib karena user bisa login via SSO
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['sso_sub', 'cloud_role', 'local_role']);
            $table->string('password')->nullable(false)->change();
        });
    }
};
