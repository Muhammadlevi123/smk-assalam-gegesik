<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $fillable = [
        'nama_kelas',
        'jurusan',
        'tingkat',
    ];

    // Relasi ke Guru sebagai wali kelas
    public function waliKelas()
    {
        return $this->belongsToMany(Guru::class, 'wali_kelas')
                    ->withPivot('tahun_ajaran_id')
                    ->withTimestamps();
    }

    public function siswa()
    {
        return $this->belongsToMany(Siswa::class, 'siswa_kelas')
                    ->withPivot('tahun_ajaran_id')
                    ->withTimestamps();
    }

    public function jadwalPelajaran()
    {
        return $this->hasMany(JadwalPelajaran::class);
    }

}
