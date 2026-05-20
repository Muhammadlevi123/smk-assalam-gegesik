<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pendaftaran extends Model
{
    protected $table = 'pendaftaran';

    protected $fillable = [
        // Step 1 - Data Siswa
        'nama_lengkap', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
        'nisn', 'agama', 'anak_ke', 'no_kartu_keluarga', 'nik', 'no_akte',
        'penerima_bantuan', 'nomor_kip', 'no_hp', 'asal_sekolah', 'tahun_lulus',
        // Step 2 - Orang Tua Ayah
        'nama_ayah', 'nik_ayah', 'pendidikan_ayah', 'tempat_lahir_ayah',
        'tanggal_lahir_ayah', 'pekerjaan_ayah', 'no_hp_ayah',
        // Step 2 - Orang Tua Ibu
        'nama_ibu', 'nik_ibu', 'pendidikan_ibu', 'tempat_lahir_ibu',
        'tanggal_lahir_ibu', 'pekerjaan_ibu', 'no_hp_ibu',
        // Step 2 - Alamat
        'jalan', 'dusun_blok', 'rt_rw', 'desa', 'kecamatan',
        // Step 2 - Jurusan
        'jurusan',
    ];

    protected $casts = [
        'tanggal_lahir'      => 'date',
        'tanggal_lahir_ayah' => 'date',
        'tanggal_lahir_ibu'  => 'date',
        'anak_ke'            => 'integer',
        'penerima_bantuan'   => 'array',
    ];
}
