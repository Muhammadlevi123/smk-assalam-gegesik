<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guru_tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guru_id')
                  ->constrained('guru')
                  ->onDelete('cascade');
            $table->foreignId('tahun_ajaran_id')
                  ->constrained('tahun_ajaran')
                  ->onDelete('cascade');
            $table->enum('status', ['Aktif', 'Nonaktif'])->default('Aktif');
            $table->timestamps();

            $table->unique(['guru_id', 'tahun_ajaran_id'], 'guru_tahun_ajaran_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guru_tahun_ajaran');
    }
};
