<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pengajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')->constrained('guru')->onDelete('cascade');
            $table->foreignId('mata_pelajaran_id')->constrained('mata_pelajaran')->onDelete('cascade');
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->onDelete('cascade');

            $table->timestamps();

            $table->unique(['guru_id', 'mata_pelajaran_id', 'tahun_ajaran_id'], 'pengajaran_unique');
        });
    }

    public function down(): void {
        Schema::dropIfExists('pengajaran');
    }
};
