<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guru extends Model
{
    protected $table = 'guru';

    protected $fillable = [
        'nama',
        'nip',
        'jenis_kelamin',
        'alamat',
        'foto',
    ];

    public function mataPelajaran()
    {
        return $this->belongsToMany(MataPelajaran::class, 'pengajaran')
                    ->withPivot(['tahun_ajaran_id'])
                    ->withTimestamps();
    }

    // Relasi ke Kelas yang dibimbing sebagai wali kelas
    public function kelasAsWali()
    {
        return $this->belongsToMany(Kelas::class, 'wali_kelas')
                    ->withPivot('tahun_ajaran_id')
                    ->withTimestamps();
    }

    public function tahunAjaran(): BelongsToMany
    {
        return $this->belongsToMany(TahunAjaran::class, 'guru_tahun_ajaran')
                    ->withPivot('status')
                    ->withTimestamps();
    }
}
