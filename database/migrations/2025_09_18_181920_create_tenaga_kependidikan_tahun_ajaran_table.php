<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenaga_kependidikan_tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenaga_kependidikan_id')->constrained('tenaga_kependidikan')->onDelete('cascade');
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->onDelete('cascade');
            $table->enum('status', ['Aktif','Nonaktif'])->default('Aktif');
            $table->timestamps();

            // Berikan nama custom yang lebih pendek untuk unique constraint
            $table->unique(['tenaga_kependidikan_id', 'tahun_ajaran_id'], 'tk_tahun_ajaran_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenaga_kependidikan_tahun_ajaran');
    }
};
