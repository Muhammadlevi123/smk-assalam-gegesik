<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendaftaran', function (Blueprint $table) {
            $table->id();

            // ── STEP 1: Data Siswa ────────────────────────────────
            $table->string('nama_lengkap');
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('tempat_lahir');
            $table->date('tanggal_lahir');
            $table->string('nisn', 20);
            $table->string('agama');
            $table->unsignedTinyInteger('anak_ke');
            $table->string('no_kartu_keluarga', 30);
            $table->string('nik', 20);
            $table->string('no_akte')->nullable();
            $table->enum('penerima_bantuan', ['KIP', 'KPS/KKS/PKH', 'SKTM', 'Tidak Ada'])->default('Tidak Ada');
            $table->string('nomor_kip')->nullable();
            $table->string('no_hp', 20);
            $table->string('asal_sekolah');
            $table->string('tahun_lulus', 4);

            // ── STEP 2: Data Orang Tua ────────────────────────────
            // Ayah
            $table->string('nama_ayah');
            $table->string('nik_ayah', 20);
            $table->string('pendidikan_ayah');
            $table->string('tempat_lahir_ayah');
            $table->date('tanggal_lahir_ayah')->nullable();
            $table->string('pekerjaan_ayah');
            $table->string('no_hp_ayah', 20);
            // Ibu
            $table->string('nama_ibu');
            $table->string('nik_ibu', 20);
            $table->string('pendidikan_ibu');
            $table->string('tempat_lahir_ibu');
            $table->date('tanggal_lahir_ibu')->nullable();
            $table->string('pekerjaan_ibu');
            $table->string('no_hp_ibu', 20);

            // ── STEP 2: Alamat ────────────────────────────────────
            $table->string('jalan');
            $table->string('dusun_blok');
            $table->string('rt_rw', 10);
            $table->string('desa');
            $table->string('kecamatan');

            // ── STEP 2: Kompetensi Jurusan ────────────────────────
            $table->enum('jurusan', ['TKRO', 'TJKT']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendaftaran');
    }
};
