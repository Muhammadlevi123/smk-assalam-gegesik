<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('siswa_tahun_ajaran', function (Blueprint $table) {
            $table->enum('kelulusan', ['Lulus', 'Tidak Lulus'])
                  ->nullable()
                  ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('siswa_tahun_ajaran', function (Blueprint $table) {
            $table->dropColumn('kelulusan');
        });
    }
};
