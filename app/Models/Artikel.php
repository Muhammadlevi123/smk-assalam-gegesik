<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Traits\AutoPublishTrait;

class Artikel extends Model
{
    use AutoPublishTrait;

    protected $table = 'artikel';

    protected $fillable = [
        'judul',
        'slug',
        'isi',
        'kategori',
        'penulis',
        'foto',
        'images',           // ← kolom baru
        'status',
        'tanggal_publikasi',
    ];

    protected $casts = [
        'tanggal_publikasi' => 'date:Y-m-d',
        'images'            => 'array',     // ← otomatis encode/decode JSON
    ];

    public function getTanggalPublikasiFormattedAttribute(): ?string
    {
        return $this->tanggal_publikasi
            ? Carbon::parse($this->tanggal_publikasi)->translatedFormat('d F Y')
            : null;
    }

    /**
     * Accessor: selalu return array kosong jika null.
     */
    public function getImagesAttribute($value): array
    {
        if (is_null($value)) return [];
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }
}
