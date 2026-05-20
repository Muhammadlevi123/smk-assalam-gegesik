<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ubah ke TEXT dulu agar bisa menampung JSON string
        Schema::table('pendaftaran', function (Blueprint $table) {
            $table->text('penerima_bantuan')->change();
        });

        // 2. Konversi data lama (string) ke JSON array
        DB::table('pendaftaran')->get()->each(function ($row) {
            $val = $row->penerima_bantuan;
            // Kalau belum berbentuk JSON array, konversi
            if ($val && !str_starts_with(trim($val), '[')) {
                DB::table('pendaftaran')
                    ->where('id', $row->id)
                    ->update([
                        'penerima_bantuan' => json_encode([$val]),
                    ]);
            }
        });

        // 3. Ubah ke JSON
        Schema::table('pendaftaran', function (Blueprint $table) {
            $table->json('penerima_bantuan')->change();
        });
    }

    public function down(): void
    {
        // Kembalikan ke text dulu
        Schema::table('pendaftaran', function (Blueprint $table) {
            $table->text('penerima_bantuan')->change();
        });

        // Konversi JSON array kembali ke string (ambil elemen pertama)
        DB::table('pendaftaran')->get()->each(function ($row) {
            $decoded = json_decode($row->penerima_bantuan, true);
            if (is_array($decoded)) {
                DB::table('pendaftaran')
                    ->where('id', $row->id)
                    ->update([
                        'penerima_bantuan' => $decoded[0] ?? 'Tidak Ada',
                    ]);
            }
        });

        // Kembalikan ke varchar
        Schema::table('pendaftaran', function (Blueprint $table) {
            $table->string('penerima_bantuan', 50)->change();
        });
    }
};
