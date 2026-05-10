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
    ];

    protected $casts = [
        'tanggal' => 'date',
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
