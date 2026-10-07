<?php

namespace App\Http\Controllers;

use App\Models\Berita;
use App\Models\Artikel;
use App\Models\Prestasi;
use App\Models\Guru;
use App\Models\TenagaKependidikan;
use App\Models\Organisasi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    /** Format tanggal aman: bekerja baik untuk string maupun objek Carbon. */
    private function fmt($date): ?string
    {
        return $date ? Carbon::parse($date)->translatedFormat('d M Y') : null;
    }

    private function snippet(?string $html, int $limit = 120): string
    {
        return Str::limit(trim(strip_tags((string) $html)), $limit, '...');
    }

    public function index(Request $request): Response
    {
        $q = trim((string) $request->get('q', ''));

        if ($q === '' || mb_strlen($q) < 2) {
            return Inertia::render('landing/Search', [
                'query'   => $q,
                'results' => [],
                'total'   => 0,
            ]);
        }

        // Escape % dan _ supaya tidak dianggap wildcard oleh LIKE
        $like = '%' . addcslashes($q, '%_\\') . '%';

        // ── Berita (hanya yang sudah terbit, sama seperti halaman berita) ──
        $berita = Berita::where('status', 'publish')
            ->where('tanggal_publikasi', '<=', Carbon::now())
            ->where(function ($w) use ($like) {
                $w->where('judul', 'LIKE', $like)
                    ->orWhere('isi', 'LIKE', $like)
                    ->orWhere('kategori', 'LIKE', $like);
            })
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
                    : $this->snippet($b->isi),
                'url'        => "/informasi/berita/{$b->slug}",
                'date'       => $this->fmt($b->tanggal_publikasi),
            ]);

        // ── Artikel (hanya yang publish) ──────────────────────────
        $artikel = Artikel::where('status', 'publish')
            ->where(function ($w) use ($like) {
                $w->where('judul', 'LIKE', $like)
                    ->orWhere('isi', 'LIKE', $like)
                    ->orWhere('kategori', 'LIKE', $like)
                    ->orWhere('penulis', 'LIKE', $like);
            })
            ->orderBy('created_at', 'desc')
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
                    ?: $this->snippet($a->isi),
                'url'        => "/informasi/artikel/{$a->slug}",
                'date'       => $this->fmt($a->tanggal_publikasi ?? $a->created_at),
            ]);

        // ── Prestasi (nama lomba, penyelenggara, juara, dan nama siswa) ──
        $prestasi = Prestasi::with('siswa')
            ->where(function ($w) use ($like) {
                $w->where('nama_lomba', 'LIKE', $like)
                    ->orWhere('deskripsi', 'LIKE', $like)
                    ->orWhere('penyelenggara', 'LIKE', $like)
                    ->orWhere('tingkat', 'LIKE', $like)
                    ->orWhere('juara', 'LIKE', $like)
                    ->orWhereHas('siswa', fn ($s) => $s->where('nama', 'LIKE', $like));

                // Siswa input manual (aman kalau migration belum dijalankan)
                if (Schema::hasColumn('prestasi', 'siswa_manual')) {
                    $w->orWhere('siswa_manual', 'LIKE', $like);
                }
            })
            ->orderBy('tanggal', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($p) {
                $namaSiswa = $p->siswa->pluck('nama')
                    ->merge(collect($p->siswa_manual ?? [])->pluck('nama'))
                    ->filter()
                    ->take(3)
                    ->implode(', ');

                return [
                    'type'       => 'prestasi',
                    'type_label' => 'Prestasi',
                    'id'         => $p->id,
                    'title'      => $p->nama_lomba,
                    'excerpt'    => collect([
                        $namaSiswa     ? "Siswa: {$namaSiswa}"          : null,
                        $p->juara,                                       // sudah berisi kata "Juara"
                        $p->tingkat    ? 'Tingkat ' . ucfirst($p->tingkat) : null,
                        $p->penyelenggara,
                    ])->filter()->implode(' · '),
                    'url'        => '/prestasi',
                    'date'       => $this->fmt($p->tanggal),
                ];
            });

        // ── Guru ──────────────────────────────────────────────────
        // ❌ Dulu: ->orWhere('nip', ...) → error karena tabel guru TIDAK punya kolom nip.
        $guruKolom = array_values(array_filter(
            ['nama', 'nip', 'nuptk'],
            fn ($col) => Schema::hasColumn((new Guru)->getTable(), $col)
        ));

        $guru = Guru::with(['mataPelajaran'])
            ->where(function ($w) use ($guruKolom, $like) {
                foreach ($guruKolom as $col) {
                    $w->orWhere($col, 'LIKE', $like);
                }
            })
            ->limit(5)
            ->get()
            ->map(fn ($g) => [
                'type'       => 'guru',
                'type_label' => 'Tenaga Pendidik',
                'id'         => $g->id,
                'title'      => $g->nama,
                'excerpt'    => $g->mataPelajaran->isNotEmpty()
                    ? 'Mengajar: ' . $g->mataPelajaran->pluck('nama')->unique()->implode(', ')
                    : 'Tenaga pendidik',
                'url'        => '/profil/tenaga-pendidik',
                'date'       => null,
            ]);

        // ── Tenaga Kependidikan ───────────────────────────────────
        $tenaga = TenagaKependidikan::where(function ($w) use ($like) {
                $w->where('nama', 'LIKE', $like)
                    ->orWhere('jabatan', 'LIKE', $like);
            })
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
        $organisasi = Organisasi::where(function ($w) use ($like) {
                $w->where('nama', 'LIKE', $like)
                    ->orWhere('deskripsi', 'LIKE', $like)
                    ->orWhere('pembina', 'LIKE', $like)
                    ->orWhere('jenis', 'LIKE', $like);
            })
            ->orderBy('nama')
            ->limit(5)
            ->get()
            ->map(fn ($o) => [
                'type'       => 'organisasi',
                'type_label' => $o->jenis ?? 'Organisasi',
                'id'         => $o->id,
                'title'      => $o->nama,
                'excerpt'    => collect([
                    $o->pembina        ? "Pembina: {$o->pembina}"       : null,
                    $o->jadwal_latihan ? "Jadwal: {$o->jadwal_latihan}" : null,
                    !$o->pembina && !$o->jadwal_latihan && $o->deskripsi
                        ? $this->snippet($o->deskripsi, 100)
                        : null,
                ])->filter()->implode(' · ') ?: (string) $o->jenis,
                // Route "/profil/organisasi" tanpa slug tidak ada, jadi fallback ke beranda
                'url'        => $o->slug ? "/profil/organisasi/{$o->slug}" : '/',
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
