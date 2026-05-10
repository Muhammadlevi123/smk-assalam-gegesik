<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajaran';

    protected $fillable = ['nama'];

    public function guru()
    {
        return $this->belongsToMany(Guru::class, 'pengajaran')
                    ->withPivot('tahun_ajaran_id')
                    ->withTimestamps();
    }
}
