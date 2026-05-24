<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\TenagaKependidikan;
use App\Models\Berita;
use App\Models\Organisasi;
use App\Models\Prestasi;
use App\Models\TahunAjaran;
use App\Models\Artikel;
use App\Models\KalenderAkademik;
use App\Models\ContactMessage;
use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class LandingPageController extends Controller
{
    // =====================================================
    // HELPER: ambil data guru + tenaga kependidikan aktif
    // =====================================================
    private function getStaffData(): array
    {
        $tahunAjaranTerbaru = TahunAjaran::getAktif()
            ?? TahunAjaran::orderBy('created_at', 'desc')->first();

        $guru               = collect();
        $tenagaKependidikan = collect();

        if ($tahunAjaranTerbaru) {
            $guruData = Guru::whereHas('tahunAjaran', function ($query) use ($tahunAjaranTerbaru) {
                $query->where('tahun_ajaran.id', $tahunAjaranTerbaru->id)
                      ->where('guru_tahun_ajaran.status', 'Aktif');
            })->get();

            $guru = $guruData->map(function ($item) use ($tahunAjaranTerbaru) {
                $mataPelajaran = null;
                try {
                    $mataPelajaran = $item->mataPelajaran()
                        ->wherePivot('tahun_ajaran_id', $tahunAjaranTerbaru->id)
                        ->first();
                } catch (\Exception $e) {
                    Log::warning('Gagal ambil mata pelajaran guru id=' . $item->id . ': ' . $e->getMessage());
                }
                return [
                    'id'             => $item->id,
                    'nama'           => $item->nama,
                    'foto'           => $item->foto ? "/storage/{$item->foto}" : null,
                    'nip'            => $item->nip ?? null,
                    'mata_pelajaran' => $mataPelajaran ? $mataPelajaran->nama : null,
                ];
            });

            $tenagaKependidikan = TenagaKependidikan::whereHas('tahunAjaran', function ($query) use ($tahunAjaranTerbaru) {
                $query->where('tahun_ajaran.id', $tahunAjaranTerbaru->id)
                      ->where('tenaga_kependidikan_tahun_ajaran.status', 'Aktif');
            })
            ->select('id', 'nama', 'jabatan', 'foto')
            ->get()
            ->map(function ($item) {
                return [
                    'id'      => $item->id,
                    'nama'    => $item->nama,
                    'jabatan' => $item->jabatan,
                    'foto'    => $item->foto ? "/storage/{$item->foto}" : null,
                ];
            });
        }

        return [
            'guru'                => $guru,
            'tenaga_kependidikan' => $tenagaKependidikan,
            'tahun_ajaran'        => $tahunAjaranTerbaru?->tahun ?? null,
        ];
    }

    // =====================================================
    // INDEX — Landing Page Utama
    // =====================================================
    public function index()
    {
        $today = Carbon::today();

        // Prioritas: sedang berjalan → akan datang → kosong
        $tahunAjaranTerbaru = TahunAjaran::whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->first();

        if (!$tahunAjaranTerbaru) {
            $tahunAjaranTerbaru = TahunAjaran::whereDate('tanggal_mulai', '>', $today)
                ->orderBy('tanggal_mulai', 'asc')
                ->first();
        }

        $guru = collect();
        if ($tahunAjaranTerbaru) {
            $guruData = Guru::whereHas('tahunAjaran', function ($query) use ($tahunAjaranTerbaru) {
                $query->where('tahun_ajaran.id', $tahunAjaranTerbaru->id)
                      ->where('guru_tahun_ajaran.status', 'Aktif');
            })->get();

            $guru = $guruData->map(function ($item) use ($tahunAjaranTerbaru) {
                $mataPelajaran = null;
                try {
                    $mataPelajaran = $item->mataPelajaran()
                        ->wherePivot('tahun_ajaran_id', $tahunAjaranTerbaru->id)
                        ->first();
                } catch (\Exception $e) {
                    Log::warning('Gagal ambil mata pelajaran guru id=' . $item->id . ': ' . $e->getMessage());
                }
                return [
                    'id'             => $item->id,
                    'nama'           => $item->nama,
                    'foto'           => $item->foto,
                    'mata_pelajaran' => $mataPelajaran ? $mataPelajaran->nama : null,
                ];
            });
        }

        $tenagaKependidikan = collect();
        if ($tahunAjaranTerbaru) {
            $tenagaKependidikan = TenagaKependidikan::whereHas('tahunAjaran', function ($query) use ($tahunAjaranTerbaru) {
                $query->where('tahun_ajaran.id', $tahunAjaranTerbaru->id)
                      ->where('tenaga_kependidikan_tahun_ajaran.status', 'Aktif');
            })
            ->select('id', 'nama', 'jabatan', 'foto')
            ->get()
            ->map(fn($item) => [
                'id'      => $item->id,
                'nama'    => $item->nama,
                'jabatan' => $item->jabatan,
                'foto'    => $item->foto,
            ]);
        }

        $berita = Berita::where('status', 'publish')
            ->where('tanggal_publikasi', '<=', Carbon::now())
            ->orderBy('tanggal_publikasi', 'desc')
            ->take(6)
            ->get()
            ->map(function ($item) {
                $tanggalPublikasi = Carbon::parse($item->tanggal_publikasi);
                return [
                    'id'          => $item->id,
                    'title'       => $item->judul,
                    'date'        => $tanggalPublikasi->format('Y-m-d'),
                    'displayDate' => $tanggalPublikasi->translatedFormat('d M Y'),
                    'description' => \Str::limit(strip_tags($item->isi), 150, '...'),
                    'image'       => $item->foto ? "/storage/{$item->foto}" : '/storage/img/news/default-news.jpg',
                    'category'    => $item->kategori ?: 'Berita',
                    'slug'        => $item->slug,
                ];
            });

        $ekstrakurikuler = Organisasi::where('jenis', 'Ekstrakurikuler')
            ->select('id', 'slug', 'nama', 'jenis', 'deskripsi', 'logo')
            ->orderBy('nama', 'asc')
            ->get()
            ->map(function ($item, $index) {
                $colors = [
                    ['from' => 'blue-600',    'to' => 'blue-800',    'text' => 'blue-600'],
                    ['from' => 'red-600',     'to' => 'red-800',     'text' => 'red-600'],
                    ['from' => 'amber-600',   'to' => 'amber-800',   'text' => 'amber-600'],
                    ['from' => 'purple-600',  'to' => 'purple-800',  'text' => 'purple-600'],
                    ['from' => 'orange-600',  'to' => 'orange-800',  'text' => 'orange-600'],
                    ['from' => 'teal-600',    'to' => 'teal-800',    'text' => 'teal-600'],
                    ['from' => 'green-600',   'to' => 'green-800',   'text' => 'green-600'],
                    ['from' => 'indigo-600',  'to' => 'indigo-800',  'text' => 'indigo-600'],
                    ['from' => 'emerald-600', 'to' => 'emerald-800', 'text' => 'emerald-600'],
                    ['from' => 'rose-600',    'to' => 'rose-800',    'text' => 'rose-600'],
                ];
                return [
                    'id'        => $item->id,
                    'slug'      => $item->slug,
                    'nama'      => $item->nama,
                    'jenis'     => $item->jenis,
                    'deskripsi' => $item->deskripsi,
                    'logo'      => $item->logo ? "/storage/{$item->logo}" : null,
                    'color'     => $colors[$index % count($colors)],
                ];
            });

        $prestasi = Prestasi::with('siswa')
            ->orderBy('tanggal', 'desc')
            ->take(10)
            ->get()
            ->map(fn($item) => [
                'id'                => $item->id,
                'nama_lomba'        => $item->nama_lomba,
                'tingkat'           => $item->tingkat,
                'juara'             => $item->juara,
                'penyelenggara'     => $item->penyelenggara,
                'tanggal'           => $item->tanggal,
                'tanggal_formatted' => Carbon::parse($item->tanggal)->translatedFormat('d F Y'),
                'siswa'             => $item->siswa->map(fn($s) => [
                    'id'   => $s->id,
                    'nama' => $s->nama,
                    'nisn' => $s->nisn ?? null,
                ]),
                'nama_siswa' => $item->siswa->pluck('nama')->join(', '),
            ]);

        $prestasiStats = [
            'internasional' => Prestasi::whereRaw('LOWER(tingkat) = ?', ['internasional'])->count(),
            'nasional'      => Prestasi::whereRaw('LOWER(tingkat) = ?', ['nasional'])->count(),
            'provinsi'      => Prestasi::whereRaw('LOWER(tingkat) = ?', ['provinsi'])->count(),
            'kabupaten'     => Prestasi::whereRaw('LOWER(tingkat) IN (?)', ['kabupaten'])->count()
                             + Prestasi::whereRaw('LOWER(tingkat) = ?', ['kota'])->count(),
        ];

        $statistik = [
            'total_guru'                => $guru->count(),
            'total_tenaga_kependidikan' => $tenagaKependidikan->count(),
            'total_berita'              => Berita::where('status', 'publish')->count(),
            'total_ekstrakurikuler'     => $ekstrakurikuler->count(),
            'total_prestasi'            => Prestasi::count(),
            'tahun_ajaran_terbaru'      => $tahunAjaranTerbaru?->tahun ?? 'Tidak ada tahun ajaran',
            'prestasi_stats'            => $prestasiStats,
        ];

        return Inertia::render('Dashboard', [
            'guru'                => $guru,
            'tenaga_kependidikan' => $tenagaKependidikan,
            'berita'              => $berita,
            'ekstrakurikuler'     => $ekstrakurikuler,
            'prestasi'            => $prestasi,
            'statistik'           => $statistik,
        ]);
    }

    // =====================================================
    // SEJARAH
    // =====================================================
    public function sejarah()
    {
        return Inertia::render('landing/Sejarah', [
            'meta' => ['title' => 'Sejarah Sekolah — SMK Assalam Gegesik'],
        ]);
    }

    // =====================================================
    // VISI MISI
    // =====================================================
    public function visiMisi()
    {
        return Inertia::render('landing/VisiMisi', [
            'meta' => ['title' => 'Visi & Misi — SMK Assalam Gegesik'],
        ]);
    }

    // =====================================================
    // TENAGA PENDIDIK & KEPENDIDIKAN
    // Prioritas: berjalan → akan datang → terbaru
    // =====================================================
    public function tenagaPendidik()
    {
        $today = Carbon::today();

        // 1. Cari yang sedang berjalan
        $tahunAjaran = TahunAjaran::whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->first();

        // 2. Jika tidak ada yang berjalan, cari yang akan datang (terdekat)
        if (!$tahunAjaran) {
            $tahunAjaran = TahunAjaran::whereDate('tanggal_mulai', '>', $today)
                ->orderBy('tanggal_mulai', 'asc')
                ->first();
        }

        // Tentukan status untuk ditampilkan di frontend
        $statusTahunAjaran = 'tidak ada';
        if ($tahunAjaran) {
            $mulai   = Carbon::parse($tahunAjaran->tanggal_mulai)->startOfDay();
            $selesai = Carbon::parse($tahunAjaran->tanggal_selesai)->endOfDay();
            if ($today->between($mulai, $selesai)) {
                $statusTahunAjaran = 'berjalan';
            } elseif ($today->lt($mulai)) {
                $statusTahunAjaran = 'akan-datang';
            } else {
                $statusTahunAjaran = 'selesai';
            }
        }

        $guru               = collect();
        $tenagaKependidikan = collect();

        if ($tahunAjaran) {
            $tenagaKependidikan = TenagaKependidikan::whereHas('tahunAjaran', function ($q) use ($tahunAjaran) {
                $q->where('tahun_ajaran.id', $tahunAjaran->id)
                  ->where('tenaga_kependidikan_tahun_ajaran.status', 'Aktif');
            })
            ->orderBy('nama')
            ->get()
            ->map(fn($item) => [
                'id'      => $item->id,
                'nama'    => $item->nama,
                'jabatan' => $item->jabatan ?? '-',
                'foto'    => $item->foto ? "/storage/{$item->foto}" : null,
            ]);

            $guruData = Guru::whereHas('tahunAjaran', function ($q) use ($tahunAjaran) {
                $q->where('tahun_ajaran.id', $tahunAjaran->id)
                  ->where('guru_tahun_ajaran.status', 'Aktif');
            })
            ->with(['mataPelajaran' => function ($q) use ($tahunAjaran) {
                $q->wherePivot('tahun_ajaran_id', $tahunAjaran->id);
            }])
            ->orderBy('nama')
            ->get();

            $guru = $guruData->map(fn($item) => [
                'id'             => $item->id,
                'nama'           => $item->nama,
                'nip'            => $item->nip ?? null,
                'foto'           => $item->foto ? "/storage/{$item->foto}" : null,
                'mata_pelajaran' => $item->mataPelajaran->first()?->nama ?? '-',
            ]);
        }

        return Inertia::render('landing/TenagaPendidik', [
            'tenaga_kependidikan'  => $tenagaKependidikan,
            'guru'                 => $guru,
            'tahun_ajaran'         => $tahunAjaran?->tahun ?? '-',
            'status_tahun_ajaran'  => $statusTahunAjaran,
        ]);
    }

    // =====================================================
    // STRUKTUR ORGANISASI
    // =====================================================
    public function strukturOrganisasi()
    {
        $organisasi = Organisasi::orderBy('jenis')
            ->orderBy('nama')
            ->get()
            ->map(fn($item) => [
                'id'        => $item->id,
                'nama'      => $item->nama,
                'jenis'     => $item->jenis,
                'deskripsi' => $item->deskripsi,
                'logo'      => $item->logo ? "/storage/{$item->logo}" : null,
            ])
            ->groupBy('jenis');

        return Inertia::render('landing/StrukturOrganisasi', [
            'organisasi' => $organisasi,
            'meta'       => ['title' => 'Struktur Organisasi — SMK Assalam Gegesik'],
        ]);
    }

    // =====================================================
    // PRESTASI
    // =====================================================
    public function prestasi(Request $request)
    {
        $tingkatFilter = $request->get('tingkat');

        $query = Prestasi::with('siswa')->orderBy('tanggal', 'desc');

        if ($tingkatFilter) {
            $query->whereRaw('LOWER(tingkat) = ?', [strtolower($tingkatFilter)]);
        }

        $prestasi = $query->get()->map(fn($item) => [
            'id'                => $item->id,
            'nama_lomba'        => $item->nama_lomba,
            'tingkat'           => $item->tingkat,
            'juara'             => $item->juara,
            'penyelenggara'     => $item->penyelenggara,
            'tanggal'           => $item->tanggal,
            'tanggal_formatted' => Carbon::parse($item->tanggal)->translatedFormat('d F Y'),
            'foto'              => $item->foto ?? null,
            'nama_siswa'        => $item->siswa->pluck('nama')->join(', '),
            'siswa'             => $item->siswa->map(fn($s) => [
                'id'   => $s->id,
                'nama' => $s->nama,
            ]),
        ]);

        $stats = [
            'internasional' => Prestasi::whereRaw('LOWER(tingkat) = ?', ['internasional'])->count(),
            'nasional'      => Prestasi::whereRaw('LOWER(tingkat) = ?', ['nasional'])->count(),
            'provinsi'      => Prestasi::whereRaw('LOWER(tingkat) = ?', ['provinsi'])->count(),
            'kabupaten'     => Prestasi::whereRaw('LOWER(tingkat) IN (?)', ['kabupaten'])->count()
                             + Prestasi::whereRaw('LOWER(tingkat) = ?', ['kota'])->count(),
            'total'         => Prestasi::count(),
        ];

        return Inertia::render('landing/Prestasi', [
            'prestasi'     => $prestasi,
            'stats'        => $stats,
            'aktif_filter' => $tingkatFilter,
            'meta'         => ['title' => 'Prestasi Sekolah — SMK Assalam Gegesik'],
        ]);
    }

    // =====================================================
    // BERITA — List
    // =====================================================
    public function berita(Request $request)
    {
        $kategori = $request->get('kategori');
        $search   = $request->get('q');
        $perPage  = 12;

        $terbaru = Berita::where('status', 'publish')
            ->where('tanggal_publikasi', '<=', Carbon::now())
            ->orderBy('tanggal_publikasi', 'desc')
            ->take(5)
            ->get()
            ->map(fn($item) => [
                'id'          => $item->id,
                'title'       => $item->judul,
                'slug'        => $item->slug,
                'displayDate' => Carbon::parse($item->tanggal_publikasi)->translatedFormat('d F Y'),
                'description' => \Str::limit(strip_tags($item->isi), 180, '...'),
                'image'       => $item->foto ? "/storage/{$item->foto}" : '/storage/img/news/default-news.jpg',
                'category'    => $item->kategori ?: 'Berita',
            ]);

        $popularIds = $terbaru->pluck('id')->toArray();
        $popular = Berita::where('status', 'publish')
            ->where('tanggal_publikasi', '<=', Carbon::now())
            ->whereNotIn('id', $popularIds)
            ->orderBy('tanggal_publikasi', 'desc')
            ->take(5)
            ->get()
            ->map(fn($item) => [
                'id'          => $item->id,
                'title'       => $item->judul,
                'slug'        => $item->slug,
                'displayDate' => Carbon::parse($item->tanggal_publikasi)->translatedFormat('d F Y'),
                'description' => \Str::limit(strip_tags($item->isi), 180, '...'),
                'image'       => $item->foto ? "/storage/{$item->foto}" : '/storage/img/news/default-news.jpg',
                'category'    => $item->kategori ?: 'Berita',
            ]);

        $query = Berita::where('status', 'publish')
            ->where('tanggal_publikasi', '<=', Carbon::now())
            ->orderBy('tanggal_publikasi', 'desc');

        if ($kategori) {
            $query->where('kategori', $kategori);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('isi', 'like', "%{$search}%");
            });
        }

        $semuaBerita = $query->paginate($perPage)->through(fn($item) => [
            'id'          => $item->id,
            'title'       => $item->judul,
            'slug'        => $item->slug,
            'displayDate' => Carbon::parse($item->tanggal_publikasi)->translatedFormat('d F Y'),
            'description' => \Str::limit(strip_tags($item->isi), 180, '...'),
            'image'       => $item->foto ? "/storage/{$item->foto}" : '/storage/img/news/default-news.jpg',
            'category'    => $item->kategori ?: 'Berita',
        ]);

        $kategoriList = Berita::where('status', 'publish')
            ->whereNotNull('kategori')
            ->distinct()
            ->pluck('kategori');

        return Inertia::render('landing/Berita', [
            'terbaru'        => $terbaru,
            'popular'        => $popular,
            'semua_berita'   => $semuaBerita,
            'kategori_list'  => $kategoriList,
            'aktif_kategori' => $kategori,
            'search'         => $search,
        ]);
    }

    // =====================================================
    // BERITA DETAIL ← PERUBAHAN ADA DI SINI
    // Tambah 'images' ke data yang dikirim ke frontend
    // =====================================================
    public function beritaDetail(string $slug)
    {
        $berita = Berita::where('slug', $slug)
            ->where('status', 'publish')
            ->where('tanggal_publikasi', '<=', Carbon::now())
            ->firstOrFail();

        $terkait = Berita::where('status', 'publish')
            ->where('id', '!=', $berita->id)
            ->where('kategori', $berita->kategori)
            ->where('tanggal_publikasi', '<=', Carbon::now())
            ->orderBy('tanggal_publikasi', 'desc')
            ->take(3)
            ->get()
            ->map(fn($item) => [
                'id'          => $item->id,
                'title'       => $item->judul,
                'slug'        => $item->slug,
                'displayDate' => Carbon::parse($item->tanggal_publikasi)->translatedFormat('d F Y'),
                'image'       => $item->foto ? "/storage/{$item->foto}" : '/storage/img/news/default-news.jpg',
                'category'    => $item->kategori ?: 'Berita',
            ]);

        // ── images: tambah prefix /storage/ ke tiap path ──────────
        $images = collect($berita->images ?? [])
            ->map(fn($path) => "/storage/{$path}")
            ->values()
            ->toArray();

        return Inertia::render('landing/BeritaDetail', [
            'berita' => [
                'id'          => $berita->id,
                'title'       => $berita->judul,
                'slug'        => $berita->slug,
                'isi'         => $berita->isi,
                'image'       => $berita->foto ? "/storage/{$berita->foto}" : '/storage/img/news/default-news.jpg',
                'images'      => $images,   // ← tambahan
                'displayDate' => Carbon::parse($berita->tanggal_publikasi)->translatedFormat('d F Y'),
                'category'    => $berita->kategori ?: 'Berita',
            ],
            'terkait' => $terkait,
        ]);
    }

    // =====================================================
    // ARTIKEL — List
    // =====================================================
    public function artikel(Request $request)
    {
        $search   = $request->get('q');
        $kategori = $request->get('kategori');

        $isFiltered = $kategori || $search;
        $perPage    = $isFiltered ? 12 : 13;

        $query = Artikel::where('status', 'publish')
            ->orderBy('created_at', 'desc');

        if ($kategori) {
            $query->where('kategori', $kategori);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('isi', 'like', "%{$search}%");
            });
        }

        $artikel = $query->paginate($perPage)->through(fn($item) => [
            'id'          => $item->id,
            'title'       => $item->judul,
            'slug'        => $item->slug,
            'penulis'     => $item->penulis ?? 'Tim Redaksi',
            'kategori'    => $item->kategori ?: 'Artikel',
            'displayDate' => $item->tanggal_publikasi
                ? Carbon::parse($item->tanggal_publikasi)->translatedFormat('d F Y')
                : Carbon::parse($item->created_at)->translatedFormat('d F Y'),
            'description' => \Str::limit(strip_tags($item->isi), 180, '...'),
            'image'       => $item->foto ? "/storage/{$item->foto}" : '/storage/img/news/default-news.jpg',
        ]);

        $kategoriList = Artikel::where('status', 'publish')
            ->whereNotNull('kategori')
            ->distinct()
            ->pluck('kategori');

        return Inertia::render('landing/Artikel', [
            'artikel'        => $artikel,
            'search'         => $search,
            'kategori_list'  => $kategoriList,
            'aktif_kategori' => $request->get('kategori'),
        ]);
    }

    // =====================================================
    // ARTIKEL DETAIL
    // =====================================================
    public function artikelDetail(string $slug)
    {
        $artikel = Artikel::where('slug', $slug)
            ->where('status', 'publish')
            ->firstOrFail();

        $terkait = Artikel::where('status', 'publish')
            ->where('id', '!=', $artikel->id)
            ->when($artikel->kategori, fn($q) => $q->where('kategori', $artikel->kategori))
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(fn($item) => [
                'id'          => $item->id,
                'title'       => $item->judul,
                'slug'        => $item->slug,
                'penulis'     => $item->penulis ?? 'Tim Redaksi',
                'kategori'    => $item->kategori ?: 'Artikel',
                'displayDate' => $item->tanggal_publikasi
                    ? Carbon::parse($item->tanggal_publikasi)->translatedFormat('d F Y')
                    : Carbon::parse($item->created_at)->translatedFormat('d F Y'),
                'image'       => $item->foto ? "/storage/{$item->foto}" : '/storage/img/news/default-news.jpg',
            ]);

        // images: tambah prefix /storage/ ke tiap path
        $images = collect($artikel->images ?? [])
            ->map(fn($path) => "/storage/{$path}")
            ->values()
            ->toArray();

        return Inertia::render('landing/ArtikelDetail', [
            'artikel' => [
                'id'          => $artikel->id,
                'title'       => $artikel->judul,
                'slug'        => $artikel->slug,
                'isi'         => $artikel->isi,
                'penulis'     => $artikel->penulis ?? 'Tim Redaksi',
                'kategori'    => $artikel->kategori ?: 'Artikel',
                'image'       => $artikel->foto ? "/storage/{$artikel->foto}" : '/storage/img/news/default-news.jpg',
                'images'      => $images,   // ← foto tambahan
                'displayDate' => $artikel->tanggal_publikasi
                    ? Carbon::parse($artikel->tanggal_publikasi)->translatedFormat('d F Y')
                    : Carbon::parse($artikel->created_at)->translatedFormat('d F Y'),
            ],
            'terkait' => $terkait,
        ]);
    }

    // =====================================================
    // KALENDER AKADEMIK
    // =====================================================
    public function kalenderAkademik(Request $request)
    {
        Carbon::setLocale('id');
        $tahunAjaranId = $request->get('tahun_ajaran_id');

        $tahunAjaranAktif   = TahunAjaran::getAktif()
            ?? TahunAjaran::orderBy('created_at', 'desc')->first();

        $tahunAjaranDipilih = $tahunAjaranId
            ? TahunAjaran::find($tahunAjaranId)
            : $tahunAjaranAktif;

        $kalender = collect();
        if ($tahunAjaranDipilih) {
            $kalender = KalenderAkademik::where('tahun_ajaran_id', $tahunAjaranDipilih->id)
                ->orderBy('tanggal_mulai')
                ->get()
                ->map(fn($item) => [
                    'id'              => $item->id,
                    'judul'           => $item->judul,
                    'tanggal_mulai'   => $item->tanggal_mulai?->format('Y-m-d'),
                    'tanggal_selesai' => $item->tanggal_selesai?->format('Y-m-d'),
                    'tanggal_display' => $item->tanggal_mulai?->translatedFormat('d F Y')
                        . ($item->tanggal_selesai && $item->tanggal_selesai != $item->tanggal_mulai
                            ? ' — ' . $item->tanggal_selesai->translatedFormat('d F Y')
                            : ''),
                    'bulan'           => $item->tanggal_mulai?->translatedFormat('F Y'),
                    'include_weekend' => $item->include_weekend ?? false,
                ]);
        }

        $semuaTahunAjaran = TahunAjaran::orderBy('created_at', 'desc')
            ->get()
            ->map(fn($t) => ['id' => $t->id, 'tahun' => $t->tahun]);

        return Inertia::render('landing/KalenderAkademik', [
            'kalender'           => $kalender,
            'tahun_ajaran_aktif' => $tahunAjaranDipilih?->tahun ?? '-',
            'tahun_ajaran_id'    => $tahunAjaranDipilih?->id,
            'semua_tahun_ajaran' => $semuaTahunAjaran,
        ]);
    }

    // =====================================================
    // STORE CONTACT MESSAGE
    // =====================================================
    public function storeContactMessage(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name'    => 'required|string|max:255',
                'email'   => 'required|email|max:255',
                'phone'   => 'nullable|string|max:20',
                'message' => 'required|string|min:10|max:2000',
            ], [
                'name.required'    => 'Nama wajib diisi.',
                'name.max'         => 'Nama maksimal 255 karakter.',
                'email.required'   => 'Email wajib diisi.',
                'email.email'      => 'Format email tidak valid.',
                'email.max'        => 'Email maksimal 255 karakter.',
                'phone.max'        => 'Nomor telepon maksimal 20 karakter.',
                'message.required' => 'Pesan wajib diisi.',
                'message.min'      => 'Pesan minimal 10 karakter.',
                'message.max'      => 'Pesan maksimal 2000 karakter.',
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator->errors());
            }

            $contactMessage = ContactMessage::create([
                'nama'          => $request->name,
                'email'         => $request->email,
                'nomor_telepon' => $request->phone,
                'pesan'         => $request->message,
            ]);

            Log::info('Contact message received', [
                'id'    => $contactMessage->id,
                'email' => $contactMessage->email,
                'nama'  => $contactMessage->nama,
            ]);

            return back();

        } catch (\Exception $e) {
            Log::error('Error storing contact message', ['error' => $e->getMessage()]);
            return back()->withErrors([
                'general' => 'Maaf, terjadi kesalahan saat mengirim pesan. Silakan coba lagi.',
            ]);
        }
    }

    // =====================================================
    // ORGANISASI DETAIL — Publik
    // =====================================================
    public function organisasiDetail(string $slug)
    {
        $organisasi = \App\Models\Organisasi::where('slug', $slug)->firstOrFail();

        // Parse jadwal: "Senin 08.00–10.00; Rabu 13.00–Selesai" → array
        $jadwalLines = [];
        if ($organisasi->jadwal_latihan) {
            foreach (explode('; ', $organisasi->jadwal_latihan) as $part) {
                if (preg_match('/^(\w+)\s+(.+)$/', $part, $m)) {
                    $jadwalLines[] = ['hari' => $m[1], 'jam' => $m[2]];
                }
            }
        }

        // Organisasi lain (jenis sama, bukan yang ini) sebagai rekomendasi
        $lainnya = \App\Models\Organisasi::where('id', '!=', $organisasi->id)
            ->where('jenis', $organisasi->jenis)
            ->orderBy('nama')
            ->take(4)
            ->get()
            ->map(fn($item) => [
                'id'    => $item->id,
                'slug'  => $item->slug,
                'nama'  => $item->nama,
                'jenis' => $item->jenis,
                'logo'  => $item->logo ? "/storage/{$item->logo}" : null,
            ]);

        return \Inertia\Inertia::render('landing/OrganisasiDetail', [
            'organisasi' => [
                'id'             => $organisasi->id,
                'slug'           => $organisasi->slug,
                'nama'           => $organisasi->nama,
                'jenis'          => $organisasi->jenis,
                'deskripsi'      => $organisasi->deskripsi,
                'pembina'        => $organisasi->pembina,
                'jadwal_latihan' => $organisasi->jadwal_latihan,
                'jadwal_lines'   => $jadwalLines,
                'logo'           => $organisasi->logo ? "/storage/{$organisasi->logo}" : null,
            ],
            'lainnya' => $lainnya,
        ]);
    }

    // =====================================================
    // API ENDPOINTS
    // =====================================================
    public function getBerita(Request $request)
    {
        $perPage = $request->get('per_page', 9);
        $berita  = Berita::where('status', 'publish')
            ->where('tanggal_publikasi', '<=', Carbon::now())
            ->orderBy('tanggal_publikasi', 'desc')
            ->paginate($perPage);
        return response()->json($berita);
    }

    public function getPrestasi(Request $request)
    {
        $perPage  = $request->get('per_page', 10);
        $prestasi = Prestasi::with('siswa')->orderBy('tanggal', 'desc')->paginate($perPage);
        return response()->json($prestasi);
    }

    public function search(Request $request)
    {
        $query = $request->get('q', '');

        if (empty($query)) {
            return Inertia::render('SearchResults', ['query' => $query, 'results' => [], 'total' => 0]);
        }

        $berita = Berita::where('status', 'publish')
            ->where(function ($q) use ($query) {
                $q->where('judul', 'like', "%{$query}%")
                  ->orWhere('isi', 'like', "%{$query}%")
                  ->orWhere('kategori', 'like', "%{$query}%");
            })
            ->orderBy('tanggal_publikasi', 'desc')
            ->take(10)->get();

        $prestasi = Prestasi::where(function ($q) use ($query) {
            $q->where('nama_lomba', 'like', "%{$query}%")
              ->orWhere('tingkat', 'like', "%{$query}%")
              ->orWhere('penyelenggara', 'like', "%{$query}%");
        })->orderBy('tanggal', 'desc')->take(10)->get();

        return Inertia::render('SearchResults', [
            'query'   => $query,
            'results' => ['berita' => $berita, 'prestasi' => $prestasi],
            'total'   => $berita->count() + $prestasi->count(),
        ]);
    }
}
