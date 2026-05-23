<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('berita', function (Blueprint $table) {
            // Kolom JSON untuk menyimpan array path foto tambahan
            // Diletakkan setelah kolom 'foto' yang sudah ada
            $table->json('images')->nullable()->after('foto');
        });
    }

    public function down(): void
    {
        Schema::table('berita', function (Blueprint $table) {
            $table->dropColumn('images');
        });
    }
};
