<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('wali_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['kelas_id', 'tahun_ajaran_id'], 'wali_kelas_unique');
            // karena 1 kelas di 1 tahun ajaran hanya boleh punya 1 wali kelas
        });
    }

    public function down(): void {
        Schema::dropIfExists('wali_kelas');
    }
};
