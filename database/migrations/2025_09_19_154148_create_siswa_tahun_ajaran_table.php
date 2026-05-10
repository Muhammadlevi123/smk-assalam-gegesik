<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('siswa_tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')
                  ->constrained('siswa')
                  ->onDelete('cascade');
            $table->foreignId('tahun_ajaran_id')
                  ->constrained('tahun_ajaran')
                  ->onDelete('cascade');
            $table->enum('status', ['Aktif', 'Nonaktif', 'Pindah', 'Lulus'])->default('Aktif');
            $table->timestamps();

            $table->unique(['siswa_id', 'tahun_ajaran_id'], 'siswa_tahun_ajaran_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('siswa_tahun_ajaran');
    }
};
