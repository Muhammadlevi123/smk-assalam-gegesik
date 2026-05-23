<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\Artikel;
use App\Models\Prestasi;
use App\Models\Guru;
use App\Models\TenagaKependidikan;
use App\Models\Organisasi;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function index(Request $request): Response
    {
        $q = trim($request->get('q', ''));

        if (!$q || strlen($q) < 2) {
            return Inertia::render('landing/Search', [
                'query'   => $q,
                'results' => [],
                'total'   => 0,
            ]);
        }

        // ── Berita ────────────────────────────────────────────────
        $berita = Berita::where('judul',    'LIKE', "%{$q}%")
            ->orWhere('isi',      'LIKE', "%{$q}%")
            ->orWhere('kategori', 'LIKE', "%{$q}%")
            ->orderBy('tanggal_publikasi', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($b) => [
                'type'       => 'berita',
                'type_label' => 'Berita',
                'id'         => $b->id,
                'title'      => $b->judul,
                'excerpt'    => $b->kategori
                    ? "Kategori: {$b->kategori}"
                    : str(strip_tags($b->isi ?? ''))->limit(120),
                'url'        => "/informasi/berita/{$b->slug}",
                'date'       => $b->tanggal_publikasi?->format('d M Y'),
            ]);

        // ── Artikel ───────────────────────────────────────────────
        $artikel = Artikel::where('judul',    'LIKE', "%{$q}%")
            ->orWhere('isi',      'LIKE', "%{$q}%")
            ->orWhere('kategori', 'LIKE', "%{$q}%")
            ->orWhere('penulis',  'LIKE', "%{$q}%")
            ->orderBy('tanggal_publikasi', 'desc')
            ->limit(5)
            ->get()
            ->map(fn ($a) => [
                'type'       => 'artikel',
                'type_label' => 'Artikel',
                'id'         => $a->id,
                'title'      => $a->judul,
                'excerpt'    => collect([
                    $a->penulis  ? "Penulis: {$a->penulis}"   : null,
                    $a->kategori ? "Kategori: {$a->kategori}" : null,
                ])->filter()->implode(' · ')
                    ?: str(strip_tags($a->isi ?? ''))->limit(120),
                'url'        => "/informasi/artikel/{$a->slug}",
                'date'       => $a->tanggal_publikasi?->format('d M Y'),
            ]);

        // ── Prestasi ──────────────────────────────────────────────
        $prestasi = Prestasi::where('nama_lomba',    'LIKE', "%{$q}%")
            ->orWhere('deskripsi',    'LIKE', "%{$q}%")
            ->orWhere('penyelenggara','LIKE', "%{$q}%")
            ->orWhere('tingkat',      'LIKE', "%{$q}%")
            ->orWhere('juara',        'LIKE', "%{$q}%")
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn ($p) => [
                'type'       => 'prestasi',
                'type_label' => 'Prestasi',
                'id'         => $p->id,
                'title'      => $p->nama_lomba,
                'excerpt'    => collect([
                    $p->juara        ? "Juara {$p->juara}"     : null,
                    $p->tingkat      ? "Tingkat {$p->tingkat}" : null,
                    $p->penyelenggara ?? null,
                ])->filter()->implode(' · '),
                'url'        => '/prestasi',
                'date'       => $p->tanggal?->format('d M Y'),
            ]);

        // ── Guru ──────────────────────────────────────────────────
        $guru = Guru::with(['mataPelajaran'])
            ->where('nama', 'LIKE', "%{$q}%")
            ->orWhere('nip',  'LIKE', "%{$q}%")
            ->limit(5)
            ->get()
            ->map(fn ($g) => [
                'type'       => 'guru',
                'type_label' => 'Tenaga Pendidik',
                'id'         => $g->id,
                'title'      => $g->nama,
                'excerpt'    => $g->mataPelajaran->isNotEmpty()
                    ? 'Mengajar: ' . $g->mataPelajaran->pluck('nama')->unique()->implode(', ')
                    : ($g->jenis_kelamin ?? '-'),
                'url'        => '/profil/tenaga-pendidik',
                'date'       => null,
            ]);

        // ── Tenaga Kependidikan ───────────────────────────────────
        $tenaga = TenagaKependidikan::where('nama',    'LIKE', "%{$q}%")
            ->orWhere('jabatan', 'LIKE', "%{$q}%")
            ->limit(5)
            ->get()
            ->map(fn ($t) => [
                'type'       => 'tenaga',
                'type_label' => 'Tenaga Kependidikan',
                'id'         => $t->id,
                'title'      => $t->nama,
                'excerpt'    => $t->jabatan ?? '-',
                'url'        => '/profil/tenaga-pendidik',
                'date'       => null,
            ]);

        // ── Organisasi / Ekskul ───────────────────────────────────
        $organisasi = Organisasi::where('nama',      'LIKE', "%{$q}%")
            ->orWhere('deskripsi',  'LIKE', "%{$q}%")
            ->orWhere('pembina',    'LIKE', "%{$q}%")
            ->orWhere('jenis',      'LIKE', "%{$q}%")
            ->orderBy('nama')
            ->limit(5)
            ->get()
            ->map(fn ($o) => [
                'type'       => 'organisasi',
                'type_label' => $o->jenis ?? 'Organisasi',
                'id'         => $o->id,
                'title'      => $o->nama,
                'excerpt'    => collect([
                    $o->pembina        ? "Pembina: {$o->pembina}"  : null,
                    $o->jadwal_latihan ? "Jadwal: {$o->jadwal_latihan}" : null,
                    !$o->pembina && !$o->jadwal_latihan && $o->deskripsi
                        ? str(strip_tags($o->deskripsi))->limit(100)
                        : null,
                ])->filter()->implode(' · ') ?: $o->jenis,
                'url'        => $o->slug ? "/profil/organisasi/{$o->slug}" : '/profil/organisasi',
                'date'       => null,
            ]);

        // ── Gabungkan semua ───────────────────────────────────────
        $results = collect()
            ->merge($berita)
            ->merge($artikel)
            ->merge($prestasi)
            ->merge($guru)
            ->merge($tenaga)
            ->merge($organisasi)
            ->values()
            ->toArray();

        return Inertia::render('landing/Search', [
            'query'   => $q,
            'results' => $results,
            'total'   => count($results),
        ]);
    }
}
