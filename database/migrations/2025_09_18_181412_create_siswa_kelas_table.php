<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('siswa_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->onDelete('cascade');
            $table->timestamps();

            // Biar 1 siswa tidak bisa double kelas di tahun yang sama
            $table->unique(['siswa_id', 'kelas_id', 'tahun_ajaran_id'], 'siswa_kelas_unique');
        });
    }

    public function down(): void {
        Schema::dropIfExists('siswa_kelas');
    }
};
