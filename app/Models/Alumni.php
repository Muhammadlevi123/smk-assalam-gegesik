<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alumni extends Model
{
    protected $table = 'alumni';

    protected $fillable = [
        'siswa_id',
        'pekerjaan',
        'foto',
        'alamat_sekarang',
        'no_telepon',
        'email',
        'tahun_lulus'
    ];

    protected $appends = [
        'nama',
        'nis',
        'angkatan',
        'jenis_kelamin',
        'alamat_asal'
    ];

    /**
     * Relasi ke Siswa (wajib)
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    // Accessor methods untuk $appends
    public function getNamaAttribute()
    {
        return $this->siswa?->nama;
    }

    public function getNisAttribute()
    {
        return $this->siswa?->nis;
    }

    public function getAngkatanAttribute()
    {
        return $this->siswa?->angkatan;
    }

    public function getJenisKelaminAttribute()
    {
        return $this->siswa?->jenis_kelamin;
    }

    public function getAlamatAsalAttribute()
    {
        return $this->siswa?->alamat;
    }
}
