<?php

namespace App\Console\Commands;

use App\Models\Berita;
use App\Models\Artikel;
use Illuminate\Console\Command;

class AutoPublishBerita extends Command
{
    protected $signature   = 'berita:auto-publish';
    protected $description = 'Auto-publish berita dan artikel draft yang tanggal publikasinya sudah tiba';

    public function handle(): void
    {
        // Publish Berita
        $jumlahBerita = Berita::batchAutoPublish();

        // Publish Artikel
        $jumlahArtikel = Artikel::batchAutoPublish();

        $total = $jumlahBerita + $jumlahArtikel;

        if ($total === 0) {
            $this->info('Tidak ada berita/artikel yang perlu dipublish.');
            return;
        }

        if ($jumlahBerita > 0) {
            $this->info("Berita: {$jumlahBerita} berhasil dipublish.");
        }

        if ($jumlahArtikel > 0) {
            $this->info("Artikel: {$jumlahArtikel} berhasil dipublish.");
        }

        $this->info("Total {$total} konten berhasil dipublish.");
    }
}
