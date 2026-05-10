<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni', function (Blueprint $table) {
            $table->id();

            // Relasi wajib ke siswa - alumni harus berasal dari siswa
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');

            // Field yang spesifik untuk alumni (tidak diambil dari siswa)
            $table->string('pekerjaan')->nullable();
            $table->string('foto')->nullable(); // Foto alumni bisa berbeda dari foto siswa
            $table->text('alamat_sekarang')->nullable(); // Alamat terkini alumni
            $table->string('no_telepon')->nullable();
            $table->string('email')->nullable();
            $table->year('tahun_lulus'); // Tahun kelulusan

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni');
    }
};
