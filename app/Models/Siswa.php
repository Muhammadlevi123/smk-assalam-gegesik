<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Siswa extends Model
{
    protected $table = 'siswa';

    protected $fillable = [
        'nis',
        'nama',
        'jenis_kelamin',
        'alamat',
        'kelas_id',
        'angkatan',
        'foto'
    ];

    // Relasi Siswa ↔ Kelas (melalui siswa_kelas + tahun ajaran)
    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'siswa_kelas')
                    ->withPivot('tahun_ajaran_id')
                    ->withTimestamps();
    }

    // Relasi Siswa ↔ Tahun Ajaran (via siswa_kelas)
    public function tahunAjaranKelas(): BelongsToMany
    {
        return $this->belongsToMany(TahunAjaran::class, 'siswa_kelas')
                    ->withPivot('kelas_id')
                    ->withTimestamps();
    }

    // Relasi Siswa ↔ Tahun Ajaran (via siswa_tahun_ajaran)
    public function tahunAjaranStatus(): BelongsToMany
    {
        return $this->belongsToMany(TahunAjaran::class, 'siswa_tahun_ajaran')
                    ->withPivot('status', 'kelulusan')
                    ->withTimestamps();
    }

    // Relasi ke Alumni
    public function alumni(): HasOne
    {
        return $this->hasOne(Alumni::class);
    }

    public function prestasi(): BelongsToMany
{
    return $this->belongsToMany(Prestasi::class, 'prestasi_siswa')
                ->withTimestamps();
}
}
