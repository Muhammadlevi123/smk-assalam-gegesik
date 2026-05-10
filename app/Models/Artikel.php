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
        'status',
        'tanggal_publikasi',
    ];

    protected $casts = [
        /*
         * Pakai 'date' bukan 'datetime' agar tidak ada konversi timezone
         * yang menyebabkan tanggal 27 bergeser jadi 26.
         *
         * Dengan 'date': value disimpan dan dibaca sebagai YYYY-MM-DD murni.
         * Dengan 'datetime': Laravel konversi ke Carbon dengan timezone app,
         * bisa menyebabkan shift tanggal jika timezone server berbeda.
         */
        'tanggal_publikasi' => 'date:Y-m-d',
    ];

    public function getTanggalPublikasiFormattedAttribute(): ?string
    {
        return $this->tanggal_publikasi
            ? Carbon::parse($this->tanggal_publikasi)->translatedFormat('d F Y')
            : null;
    }
}
