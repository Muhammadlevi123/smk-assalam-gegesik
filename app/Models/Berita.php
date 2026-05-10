<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Traits\AutoPublishTrait;

class Berita extends Model
{
    use AutoPublishTrait;

    protected $table = 'berita';

    protected $fillable = [
        'judul',
        'slug',
        'isi',
        'kategori',
        'foto',
        'status',
        'tanggal_publikasi',
    ];

    protected $casts = [

        'tanggal_publikasi' => 'date:Y-m-d',
    ];

    /**
     * Accessor untuk format tanggal tampilan.
     */
    public function getTanggalPublikasiFormattedAttribute(): ?string
    {
        return $this->tanggal_publikasi
            ? Carbon::parse($this->tanggal_publikasi)->translatedFormat('d F Y')
            : null;
    }
}
