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
        'images',       // ← kolom baru: JSON array path foto tambahan
        'status',
        'tanggal_publikasi',
    ];

    protected $casts = [
        'tanggal_publikasi' => 'date:Y-m-d',
        'images'            => 'array',   // ← otomatis encode/decode JSON
    ];

    /**
     * Accessor format tanggal tampilan.
     */
    public function getTanggalPublikasiFormattedAttribute(): ?string
    {
        return $this->tanggal_publikasi
            ? Carbon::parse($this->tanggal_publikasi)->translatedFormat('d F Y')
            : null;
    }

    /**
     * Accessor: kembalikan images sebagai array kosong jika null.
     * Aman dipakai di frontend tanpa cek null.
     */
    public function getImagesAttribute($value): array
    {
        if (is_null($value)) return [];
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

     public function show(string $id): Response
    {
        $berita = Berita::findOrFail($id);

        $berita->tanggal_formatted = $berita->tanggal_publikasi
            ? Carbon::parse($berita->tanggal_publikasi)->translatedFormat('d F Y')
            : null;

        // $berita->images sudah otomatis array [] via accessor model
        // tidak perlu decode manual di sini

        return Inertia::render('admin/berita/Show', [
            'berita' => $berita,
        ]);
    }
}
