<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Prestasi extends Model
{
    protected $table = 'prestasi';

    protected $fillable = [
        'nama_lomba',
        'tingkat',
        'juara',
        'penyelenggara',
        'tanggal',
        'foto',
        'deskripsi',
        'siswa_manual',
    ];

    protected $casts = [
        'tanggal'      => 'date',
        'siswa_manual' => 'array',
    ];

    /**
     * Relasi many-to-many ke Siswa melalui pivot prestasi_siswa
     */
    public function siswa(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'prestasi_siswa')
                    ->withTimestamps();
    }
}
