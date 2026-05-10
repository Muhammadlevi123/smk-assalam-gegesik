<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class TenagaKependidikan extends Model
{
    protected $table = 'tenaga_kependidikan';

    protected $fillable = ['nama','jenis_kelamin','jabatan','alamat', 'foto'];

    public function tahunAjaran(): BelongsToMany
    {
        return $this->belongsToMany(TahunAjaran::class, 'tenaga_kependidikan_tahun_ajaran')
                ->withPivot('status')
                ->withTimestamps();
    }
}
