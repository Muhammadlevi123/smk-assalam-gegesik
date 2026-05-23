<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organisasi', function (Blueprint $table) {
            $table->string('pembina')->nullable()->after('deskripsi');
            $table->text('jadwal_latihan')->nullable()->after('pembina');
        });
    }

    public function down(): void
    {
        Schema::table('organisasi', function (Blueprint $table) {
            $table->dropColumn(['pembina', 'jadwal_latihan']);
        });
    }
};
