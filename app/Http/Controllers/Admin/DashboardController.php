<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\TenagaKependidikan;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Alumni;
use App\Models\Artikel;
use App\Models\Berita;
use App\Models\TahunAjaran;
use App\Models\Organisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Ambil tahun ajaran yang dipilih atau default ke null (semua tahun ajaran)
        $selectedTahunAjaranId = $request->get('tahun_ajaran_id');
        $tahunAjaranAktif = null;

        if ($selectedTahunAjaranId && $selectedTahunAjaranId !== 'all') {
            $tahunAjaranAktif = TahunAjaran::find($selectedTahunAjaranId);
        }

        // Ambil tahun ajaran terbaru untuk referensi (jika diperlukan)
        $tahunAjaranTerbaru = TahunAjaran::orderByRaw('CAST(SUBSTRING_INDEX(tahun, "/", 1) AS UNSIGNED) DESC')->first();

        // Data Card Statistik Utama (tidak terfilter)
        $totalSiswa = Siswa::count();
        $totalGuru = Guru::count();
        $totalTenagaKependidikan = TenagaKependidikan::count();
        $totalKelas = Kelas::count();
        $totalMataPelajaran = MataPelajaran::count();
        $totalAlumni = Alumni::count();

        // Breakdown Status Siswa berdasarkan status terbaru masing-masing siswa
        $siswaStatusBreakdown = DB::table('siswa_tahun_ajaran as sta1')
            ->join(
                DB::raw('(SELECT siswa_id, MAX(tahun_ajaran_id) as latest_tahun_ajaran_id
                          FROM siswa_tahun_ajaran
                          GROUP BY siswa_id) as latest'),
                function($join) {
                    $join->on('sta1.siswa_id', '=', 'latest.siswa_id')
                         ->on('sta1.tahun_ajaran_id', '=', 'latest.latest_tahun_ajaran_id');
                }
            )
            ->select('sta1.status')
            ->selectRaw('COUNT(*) as jumlah')
            ->groupBy('sta1.status')
            ->get()
            ->keyBy('status')
            ->map(function($item) {
                return (int) $item->jumlah;
            });

        // Breakdown Status Guru berdasarkan status terbaru masing-masing guru
        $guruStatusBreakdown = DB::table('guru_tahun_ajaran as gta1')
            ->join(
                DB::raw('(SELECT guru_id, MAX(tahun_ajaran_id) as latest_tahun_ajaran_id
                          FROM guru_tahun_ajaran
                          GROUP BY guru_id) as latest'),
                function($join) {
                    $join->on('gta1.guru_id', '=', 'latest.guru_id')
                         ->on('gta1.tahun_ajaran_id', '=', 'latest.latest_tahun_ajaran_id');
                }
            )
            ->select('gta1.status')
            ->selectRaw('COUNT(*) as jumlah')
            ->groupBy('gta1.status')
            ->get()
            ->keyBy('status')
            ->map(function($item) {
                return (int) $item->jumlah;
            });

        // Siswa Aktif - berdasarkan status terbaru setiap siswa
        $siswaAktif = $siswaStatusBreakdown->get('Aktif', 0);

        // Guru Aktif - berdasarkan status terbaru setiap guru
        $guruAktif = $guruStatusBreakdown->get('Aktif', 0);

        // Override jika ada filter tahun ajaran tertentu
        if ($tahunAjaranAktif) {
            $siswaAktif = DB::table('siswa_tahun_ajaran')
                ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                ->where('status', 'Aktif')
                ->count();

            $guruAktif = DB::table('guru_tahun_ajaran')
                ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                ->where('status', 'Aktif')
                ->count();

            // Breakdown status untuk tahun tertentu
            $siswaStatusBreakdown = DB::table('siswa_tahun_ajaran')
                ->select('status')
                ->selectRaw('COUNT(*) as jumlah')
                ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                ->groupBy('status')
                ->get()
                ->keyBy('status')
                ->map(function($item) {
                    return (int) $item->jumlah;
                });

            $guruStatusBreakdown = DB::table('guru_tahun_ajaran')
                ->select('status')
                ->selectRaw('COUNT(*) as jumlah')
                ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                ->groupBy('status')
                ->get()
                ->keyBy('status')
                ->map(function($item) {
                    return (int) $item->jumlah;
                });
        }

        // Tenaga Kependidikan Aktif - berdasarkan status terbaru setiap tenaga kependidikan
        $tenagaKependidikanAktif = 0;
        if ($tahunAjaranAktif) {
            // Filter berdasarkan tahun ajaran tertentu
            $tenagaKependidikanAktif = DB::table('tenaga_kependidikan_tahun_ajaran')
                ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                ->where('status', 'Aktif')
                ->count();
        } else {
            // Ambil tenaga kependidikan yang status terbarunya adalah "Aktif"
            $tenagaKependidikanAktif = DB::table('tenaga_kependidikan_tahun_ajaran as tkta1')
                ->join(
                    DB::raw('(SELECT tenaga_kependidikan_id, MAX(tahun_ajaran_id) as latest_tahun_ajaran_id
                              FROM tenaga_kependidikan_tahun_ajaran
                              GROUP BY tenaga_kependidikan_id) as latest'),
                    function($join) {
                        $join->on('tkta1.tenaga_kependidikan_id', '=', 'latest.tenaga_kependidikan_id')
                             ->on('tkta1.tahun_ajaran_id', '=', 'latest.latest_tahun_ajaran_id');
                    }
                )
                ->where('tkta1.status', 'Aktif')
                ->count();
        }

        // Daftar semua tahun ajaran untuk dropdown filter
        $tahunAjaranList = TahunAjaran::orderByRaw('CAST(SUBSTRING_INDEX(tahun, "/", 1) AS UNSIGNED) DESC')
            ->get()
            ->map(function($tahun) {
                return [
                    'id' => $tahun->id,
                    'tahun' => $tahun->tahun,
                    'label' => $tahun->tahun
                ];
            });

        // Data untuk Chart 1: Distribusi Siswa per Tingkat (TERFILTER)
        $siswaPerTingkat = DB::table('kelas')
            ->join('siswa_kelas', 'kelas.id', '=', 'siswa_kelas.kelas_id')
            ->select('kelas.tingkat')
            ->selectRaw('COUNT(DISTINCT siswa_kelas.siswa_id) as jumlah')
            ->when($tahunAjaranAktif, function($query) use ($tahunAjaranAktif) {
                $query->where('siswa_kelas.tahun_ajaran_id', $tahunAjaranAktif->id);
            })
            ->groupBy('kelas.tingkat')
            ->orderBy('kelas.tingkat')
            ->get()
            ->map(function($item) {
                return [
                    'tingkat' => $item->tingkat,
                    'jumlah' => (int) $item->jumlah
                ];
            });

        // Data untuk Chart 2: Distribusi Siswa per Jurusan (TERFILTER)
        $siswaPerJurusan = DB::table('kelas')
            ->join('siswa_kelas', 'kelas.id', '=', 'siswa_kelas.kelas_id')
            ->select('kelas.jurusan')
            ->selectRaw('COUNT(DISTINCT siswa_kelas.siswa_id) as jumlah')
            ->when($tahunAjaranAktif, function($query) use ($tahunAjaranAktif) {
                $query->where('siswa_kelas.tahun_ajaran_id', $tahunAjaranAktif->id);
            })
            ->groupBy('kelas.jurusan')
            ->orderBy('kelas.jurusan')
            ->get()
            ->map(function($item) {
                return [
                    'jurusan' => $item->jurusan,
                    'jumlah' => (int) $item->jumlah
                ];
            });

        // Data untuk Chart 3: Distribusi Gender Siswa (TERFILTER atau SEMUA)
        $genderSiswa = collect();
        if ($tahunAjaranAktif) {
            // Filter berdasarkan tahun ajaran tertentu
            $genderSiswa = DB::table('siswa')
                ->join('siswa_tahun_ajaran', 'siswa.id', '=', 'siswa_tahun_ajaran.siswa_id')
                ->select('siswa.jenis_kelamin')
                ->selectRaw('COUNT(*) as jumlah')
                ->where('siswa_tahun_ajaran.tahun_ajaran_id', $tahunAjaranAktif->id)
                ->where('siswa_tahun_ajaran.status', 'Aktif')
                ->groupBy('siswa.jenis_kelamin')
                ->get()
                ->map(function($item) {
                    return [
                        'gender' => $item->jenis_kelamin,
                        'jumlah' => (int) $item->jumlah
                    ];
                });
        } else {
            // Semua tahun ajaran - ambil status terbaru setiap siswa
            $genderSiswa = DB::table('siswa')
                ->join(
                    DB::raw('(SELECT siswa_id, MAX(tahun_ajaran_id) as latest_tahun_ajaran_id
                              FROM siswa_tahun_ajaran
                              GROUP BY siswa_id) as latest_status'),
                    'siswa.id', '=', 'latest_status.siswa_id'
                )
                ->join('siswa_tahun_ajaran as sta', function($join) {
                    $join->on('siswa.id', '=', 'sta.siswa_id')
                         ->on('latest_status.latest_tahun_ajaran_id', '=', 'sta.tahun_ajaran_id');
                })
                ->select('siswa.jenis_kelamin')
                ->selectRaw('COUNT(*) as jumlah')
                ->groupBy('siswa.jenis_kelamin')
                ->get()
                ->map(function($item) {
                    return [
                        'gender' => $item->jenis_kelamin,
                        'jumlah' => (int) $item->jumlah
                    ];
                });
        }

        // Data untuk Chart 4: Status Siswa per Tahun Ajaran (TERFILTER atau SEMUA)
        $statusSiswa = collect();
        if ($tahunAjaranAktif) {
            // Filter berdasarkan tahun ajaran tertentu
            $statusSiswa = DB::table('siswa_tahun_ajaran')
                ->select('status')
                ->selectRaw('COUNT(*) as jumlah')
                ->where('tahun_ajaran_id', $tahunAjaranAktif->id)
                ->groupBy('status')
                ->get()
                ->map(function($item) {
                    return [
                        'status' => $item->status,
                        'jumlah' => (int) $item->jumlah
                    ];
                });
        } else {
            // Semua tahun ajaran - ambil status terbaru setiap siswa
            $statusSiswa = DB::table('siswa_tahun_ajaran as sta1')
                ->join(
                    DB::raw('(SELECT siswa_id, MAX(tahun_ajaran_id) as latest_tahun_ajaran_id
                              FROM siswa_tahun_ajaran
                              GROUP BY siswa_id) as latest'),
                    function($join) {
                        $join->on('sta1.siswa_id', '=', 'latest.siswa_id')
                             ->on('sta1.tahun_ajaran_id', '=', 'latest.latest_tahun_ajaran_id');
                    }
                )
                ->select('sta1.status')
                ->selectRaw('COUNT(*) as jumlah')
                ->groupBy('sta1.status')
                ->get()
                ->map(function($item) {
                    return [
                        'status' => $item->status,
                        'jumlah' => (int) $item->jumlah
                    ];
                });
        }

        // Data untuk Chart 5: Perkembangan Jumlah Siswa 5 Tahun Terakhir (TIDAK TERFILTER - selalu 5 tahun)
        $perkembanganSiswa = TahunAjaran::orderByRaw('CAST(SUBSTRING_INDEX(tahun, "/", 1) AS UNSIGNED) DESC')
            ->take(5)
            ->get()
            ->map(function($tahun) {
                $jumlahSiswa = DB::table('siswa_tahun_ajaran')
                    ->where('tahun_ajaran_id', $tahun->id)
                    ->where('status', 'Aktif')
                    ->count();

                return [
                    'tahun_ajaran' => $tahun->tahun,
                    'jumlah_siswa' => (int) $jumlahSiswa
                ];
            })
            ->reverse()
            ->values();

        // Data untuk Chart 6: Distribusi Alumni per Tahun Lulus (TIDAK TERFILTER - data historis)
        $alumniPerTahun = Alumni::select('tahun_lulus')
            ->selectRaw('COUNT(*) as jumlah')
            ->where('tahun_lulus', '>=', Carbon::now()->year - 4)
            ->groupBy('tahun_lulus')
            ->orderBy('tahun_lulus')
            ->get()
            ->map(function($item) {
                return [
                    'tahun' => (int) $item->tahun_lulus,
                    'jumlah' => (int) $item->jumlah
                ];
            });

        // Data untuk Chart 7: Distribusi Guru per Mata Pelajaran (TERFILTER atau SEMUA)
        $guruPerMapel = collect();
        if ($tahunAjaranAktif) {
            // Filter berdasarkan tahun ajaran tertentu
            $guruPerMapel = DB::table('mata_pelajaran')
                ->leftJoin('pengajaran', 'mata_pelajaran.id', '=', 'pengajaran.mata_pelajaran_id')
                ->leftJoin('guru_tahun_ajaran', function($join) use ($tahunAjaranAktif) {
                    $join->on('pengajaran.guru_id', '=', 'guru_tahun_ajaran.guru_id')
                         ->on('pengajaran.tahun_ajaran_id', '=', 'guru_tahun_ajaran.tahun_ajaran_id')
                         ->where('guru_tahun_ajaran.tahun_ajaran_id', $tahunAjaranAktif->id);
                })
                ->select('mata_pelajaran.nama as mata_pelajaran')
                ->selectRaw('COUNT(DISTINCT CASE WHEN guru_tahun_ajaran.status = "Aktif" THEN pengajaran.guru_id END) as jumlah_guru')
                ->where('pengajaran.tahun_ajaran_id', $tahunAjaranAktif->id)
                ->groupBy('mata_pelajaran.id', 'mata_pelajaran.nama')
                ->having('jumlah_guru', '>', 0)
                ->orderByRaw('COUNT(DISTINCT CASE WHEN guru_tahun_ajaran.status = "Aktif" THEN pengajaran.guru_id END) DESC')
                ->take(10)
                ->get()
                ->map(function($mapel) {
                    return [
                        'mata_pelajaran' => $mapel->mata_pelajaran,
                        'jumlah_guru' => (int) $mapel->jumlah_guru
                    ];
                });
        } else {
            // Semua tahun ajaran - ambil data terbaru atau agregasi
            $guruPerMapel = DB::table('mata_pelajaran')
                ->leftJoin('pengajaran', 'mata_pelajaran.id', '=', 'pengajaran.mata_pelajaran_id')
                ->leftJoin('guru_tahun_ajaran', function($join) {
                    $join->on('pengajaran.guru_id', '=', 'guru_tahun_ajaran.guru_id')
                         ->on('pengajaran.tahun_ajaran_id', '=', 'guru_tahun_ajaran.tahun_ajaran_id');
                })
                ->select('mata_pelajaran.nama as mata_pelajaran')
                ->selectRaw('COUNT(DISTINCT CASE WHEN guru_tahun_ajaran.status = "Aktif" THEN pengajaran.guru_id END) as jumlah_guru')
                ->groupBy('mata_pelajaran.id', 'mata_pelajaran.nama')
                ->having('jumlah_guru', '>', 0)
                ->orderByRaw('COUNT(DISTINCT CASE WHEN guru_tahun_ajaran.status = "Aktif" THEN pengajaran.guru_id END) DESC')
                ->take(10)
                ->get()
                ->map(function($mapel) {
                    return [
                        'mata_pelajaran' => $mapel->mata_pelajaran,
                        'jumlah_guru' => (int) $mapel->jumlah_guru
                    ];
                });
        }

        // Data untuk Chart 8: Artikel dan Berita per Bulan (TIDAK TERFILTER - data publikasi)
        $kontenPerBulan = collect();

        for ($i = 5; $i >= 0; $i--) {
            $bulan = Carbon::now()->subMonths($i);
            $namaBulan = $bulan->translatedFormat('M Y');

            $jumlahArtikel = Artikel::whereYear('tanggal_publikasi', $bulan->year)
                ->whereMonth('tanggal_publikasi', $bulan->month)
                ->where('status', 'publish')  // Ubah dari 'Terbit' ke 'publish'
                ->count();

            $jumlahBerita = Berita::whereYear('tanggal_publikasi', $bulan->year)
                ->whereMonth('tanggal_publikasi', $bulan->month)
                ->where('status', 'publish')  // Ubah dari 'Terbit' ke 'publish'
                ->count();

            $kontenPerBulan->push([
                'bulan' => $namaBulan,
                'artikel' => (int) $jumlahArtikel,
                'berita' => (int) $jumlahBerita,
                'total' => (int) ($jumlahArtikel + $jumlahBerita)
            ]);
        }

        // Data untuk Chart 9: Kelas per Tingkat dan Jurusan (TIDAK TERFILTER - struktur sekolah)
        $kelasPerTingkatJurusan = Kelas::select('tingkat', 'jurusan')
            ->selectRaw('COUNT(*) as jumlah')
            ->groupBy('tingkat', 'jurusan')
            ->orderBy('tingkat')
            ->orderBy('jurusan')
            ->get()
            ->map(function($item) {
                return [
                    'kategori' => $item->tingkat . ' - ' . $item->jurusan,
                    'tingkat' => $item->tingkat,
                    'jurusan' => $item->jurusan,
                    'jumlah' => (int) $item->jumlah
                ];
            });

        // Data Aktivitas Terbaru (TIDAK TERFILTER)
        $aktivitasTerbaru = collect([
            // Artikel terbaru
            ...Artikel::where('status', 'publish')  // Ubah dari 'Terbit' ke 'publish'
                ->orderBy('tanggal_publikasi', 'desc')
                ->take(3)
                ->get()
                ->map(function($artikel) {
                    return [
                        'type' => 'artikel',
                        'title' => $artikel->judul,
                        'date' => $artikel->tanggal_publikasi_formatted,
                        'kategori' => $artikel->kategori
                    ];
                }),

            // Berita terbaru
            ...Berita::where('status', 'publish')  // Ubah dari 'Terbit' ke 'publish'
                ->orderBy('tanggal_publikasi', 'desc')
                ->take(3)
                ->get()
                ->map(function($berita) {
                    return [
                        'type' => 'berita',
                        'title' => $berita->judul,
                        'date' => $berita->tanggal_publikasi_formatted,
                        'kategori' => $berita->kategori
                    ];
                })
        ])->sortByDesc('date')->take(5)->values();

        return Inertia::render('admin/Dashboard', [
            // Card Statistics
            'statistics' => [
                'total_siswa' => $totalSiswa,
                'siswa_aktif' => $siswaAktif,
                'siswa_lulus' => $siswaStatusBreakdown->get('Lulus', 0),
                'siswa_pindah' => $siswaStatusBreakdown->get('Pindah', 0),
                'siswa_nonaktif' => $siswaStatusBreakdown->get('Nonaktif', 0),
                'total_guru' => $totalGuru,
                'guru_aktif' => $guruAktif,
                'guru_nonaktif' => $guruStatusBreakdown->get('Nonaktif', 0),
                'total_tenaga_kependidikan' => $totalTenagaKependidikan,
                'tenaga_kependidikan_aktif' => $tenagaKependidikanAktif,
                'total_kelas' => $totalKelas,
                'total_mata_pelajaran' => $totalMataPelajaran,
                'total_alumni' => $totalAlumni,
                'total_organisasi' => Organisasi::count(),
            ],

            // Chart Data
            'chartData' => [
                'siswaPerTingkat' => $siswaPerTingkat,
                'siswaPerJurusan' => $siswaPerJurusan,
                'genderSiswa' => $genderSiswa,
                'statusSiswa' => $statusSiswa,
                'perkembanganSiswa' => $perkembanganSiswa,
                'alumniPerTahun' => $alumniPerTahun,
                'guruPerMapel' => $guruPerMapel,
                'kontenPerBulan' => $kontenPerBulan,
                'kelasPerTingkatJurusan' => $kelasPerTingkatJurusan,
            ],

            // Additional Data
            'tahunAjaranAktif' => $tahunAjaranAktif,
            'tahunAjaranTerbaru' => $tahunAjaranTerbaru,
            'tahunAjaranList' => $tahunAjaranList,
            'selectedTahunAjaran' => $selectedTahunAjaranId ?: 'all',
            'aktivitasTerbaru' => $aktivitasTerbaru,
            'isFilteredByYear' => $tahunAjaranAktif !== null,

            // Quick Stats untuk perbandingan
            'quickStats' => [
                'rasio_guru_siswa' => $guruAktif > 0 ? round($siswaAktif / $guruAktif, 2) : 0,
                'rata_rata_siswa_per_kelas' => $totalKelas > 0 ? round($siswaAktif / $totalKelas, 2) : 0,
                'persentase_alumni' => $totalSiswa + $totalAlumni > 0 ?
                    round(($totalAlumni / ($totalSiswa + $totalAlumni)) * 100, 2) : 0,
            ]
        ]);
    }

    /**
     * Get chart data for specific chart (untuk AJAX request)
     */
    public function getChartData(Request $request, $chartType)
    {
        $tahunAjaranId = $request->get('tahun_ajaran_id');

        switch ($chartType) {
            case 'siswa-per-tingkat':
                return response()->json($this->getSiswaPerTingkatData($tahunAjaranId));

            case 'siswa-per-jurusan':
                return response()->json($this->getSiswaPerJurusanData($tahunAjaranId));

            case 'gender-siswa':
                return response()->json($this->getGenderSiswaData($tahunAjaranId));

            case 'status-siswa':
                return response()->json($this->getStatusSiswaData($tahunAjaranId));

            case 'guru-per-mapel':
                return response()->json($this->getGuruPerMapelData($tahunAjaranId));

            default:
                return response()->json(['error' => 'Chart type not found'], 404);
        }
    }

    private function getSiswaPerTingkatData($tahunAjaranId = null)
    {
        if ($tahunAjaranId) {
            // Filter berdasarkan tahun ajaran tertentu
            return DB::table('kelas')
                ->join('siswa_kelas', 'kelas.id', '=', 'siswa_kelas.kelas_id')
                ->join('siswa_tahun_ajaran', function($join) use ($tahunAjaranId) {
                    $join->on('siswa_kelas.siswa_id', '=', 'siswa_tahun_ajaran.siswa_id')
                         ->where('siswa_tahun_ajaran.tahun_ajaran_id', $tahunAjaranId);
                })
                ->select('kelas.tingkat')
                ->selectRaw('COUNT(DISTINCT siswa_kelas.siswa_id) as jumlah')
                ->where('siswa_kelas.tahun_ajaran_id', $tahunAjaranId)
                ->groupBy('kelas.tingkat')
                ->orderBy('kelas.tingkat')
                ->get()
                ->map(function($item) {
                    return [
                        'tingkat' => $item->tingkat,
                        'jumlah' => (int) $item->jumlah
                    ];
                });
        } else {
            // Semua tahun ajaran - hanya siswa dengan status terbaru "Aktif"
            return DB::table('kelas')
                ->join('siswa_kelas', 'kelas.id', '=', 'siswa_kelas.kelas_id')
                ->join(
                    DB::raw('(SELECT siswa_id, MAX(tahun_ajaran_id) as latest_tahun_ajaran_id
                              FROM siswa_tahun_ajaran
                              GROUP BY siswa_id) as latest_status'),
                    'siswa_kelas.siswa_id', '=', 'latest_status.siswa_id'
                )
                ->join('siswa_tahun_ajaran as sta', function($join) {
                    $join->on('siswa_kelas.siswa_id', '=', 'sta.siswa_id')
                         ->on('latest_status.latest_tahun_ajaran_id', '=', 'sta.tahun_ajaran_id');
                })
                ->select('kelas.tingkat')
                ->selectRaw('COUNT(DISTINCT siswa_kelas.siswa_id) as jumlah')
                ->where('sta.status', 'Aktif')
                ->where('siswa_kelas.tahun_ajaran_id', DB::raw('latest_status.latest_tahun_ajaran_id'))
                ->groupBy('kelas.tingkat')
                ->orderBy('kelas.tingkat')
                ->get()
                ->map(function($item) {
                    return [
                        'tingkat' => $item->tingkat,
                        'jumlah' => (int) $item->jumlah
                    ];
                });
        }
    }

    private function getSiswaPerJurusanData($tahunAjaranId = null)
    {
        if ($tahunAjaranId) {
            // Filter berdasarkan tahun ajaran tertentu
            return DB::table('kelas')
                ->join('siswa_kelas', 'kelas.id', '=', 'siswa_kelas.kelas_id')
                ->join('siswa_tahun_ajaran', function($join) use ($tahunAjaranId) {
                    $join->on('siswa_kelas.siswa_id', '=', 'siswa_tahun_ajaran.siswa_id')
                         ->where('siswa_tahun_ajaran.tahun_ajaran_id', $tahunAjaranId);
                })
                ->select('kelas.jurusan')
                ->selectRaw('COUNT(DISTINCT siswa_kelas.siswa_id) as jumlah')
                ->where('siswa_kelas.tahun_ajaran_id', $tahunAjaranId)
                ->groupBy('kelas.jurusan')
                ->orderBy('kelas.jurusan')
                ->get()
                ->map(function($item) {
                    return [
                        'jurusan' => $item->jurusan,
                        'jumlah' => (int) $item->jumlah
                    ];
                });
        } else {
            // Semua tahun ajaran - hanya siswa dengan status terbaru "Aktif"
            return DB::table('kelas')
                ->join('siswa_kelas', 'kelas.id', '=', 'siswa_kelas.kelas_id')
                ->join(
                    DB::raw('(SELECT siswa_id, MAX(tahun_ajaran_id) as latest_tahun_ajaran_id
                              FROM siswa_tahun_ajaran
                              GROUP BY siswa_id) as latest_status'),
                    'siswa_kelas.siswa_id', '=', 'latest_status.siswa_id'
                )
                ->join('siswa_tahun_ajaran as sta', function($join) {
                    $join->on('siswa_kelas.siswa_id', '=', 'sta.siswa_id')
                         ->on('latest_status.latest_tahun_ajaran_id', '=', 'sta.tahun_ajaran_id');
                })
                ->select('kelas.jurusan')
                ->selectRaw('COUNT(DISTINCT siswa_kelas.siswa_id) as jumlah')
                ->where('sta.status', 'Aktif')
                ->where('siswa_kelas.tahun_ajaran_id', DB::raw('latest_status.latest_tahun_ajaran_id'))
                ->groupBy('kelas.jurusan')
                ->orderBy('kelas.jurusan')
                ->get()
                ->map(function($item) {
                    return [
                        'jurusan' => $item->jurusan,
                        'jumlah' => (int) $item->jumlah
                    ];
                });
        }
    }

    private function getGenderSiswaData($tahunAjaranId = null)
    {
        if ($tahunAjaranId) {
            // Filter berdasarkan tahun ajaran tertentu
            return DB::table('siswa')
                ->join('siswa_tahun_ajaran', 'siswa.id', '=', 'siswa_tahun_ajaran.siswa_id')
                ->select('siswa.jenis_kelamin')
                ->selectRaw('COUNT(*) as jumlah')
                ->where('siswa_tahun_ajaran.tahun_ajaran_id', $tahunAjaranId)
                ->groupBy('siswa.jenis_kelamin')
                ->get()
                ->map(function($item) {
                    return [
                        'gender' => $item->jenis_kelamin,
                        'jumlah' => (int) $item->jumlah
                    ];
                });
        } else {
            // Semua tahun ajaran - hanya siswa dengan status terbaru "Aktif"
            return DB::table('siswa')
                ->join(
                    DB::raw('(SELECT siswa_id, MAX(tahun_ajaran_id) as latest_tahun_ajaran_id
                              FROM siswa_tahun_ajaran
                              GROUP BY siswa_id) as latest_status'),
                    'siswa.id', '=', 'latest_status.siswa_id'
                )
                ->join('siswa_tahun_ajaran as sta', function($join) {
                    $join->on('siswa.id', '=', 'sta.siswa_id')
                         ->on('latest_status.latest_tahun_ajaran_id', '=', 'sta.tahun_ajaran_id');
                })
                ->select('siswa.jenis_kelamin')
                ->selectRaw('COUNT(*) as jumlah')
                ->where('sta.status', 'Aktif')
                ->groupBy('siswa.jenis_kelamin')
                ->get()
                ->map(function($item) {
                    return [
                        'gender' => $item->jenis_kelamin,
                        'jumlah' => (int) $item->jumlah
                    ];
                });
        }
    }

    private function getStatusSiswaData($tahunAjaranId = null)
    {
        if ($tahunAjaranId) {
            // Filter berdasarkan tahun ajaran tertentu
            return DB::table('siswa_tahun_ajaran')
                ->select('status')
                ->selectRaw('COUNT(*) as jumlah')
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->groupBy('status')
                ->get()
                ->map(function($item) {
                    return [
                        'status' => $item->status,
                        'jumlah' => (int) $item->jumlah
                    ];
                });
        } else {
            // Semua tahun ajaran - ambil status terbaru setiap siswa
            return DB::table('siswa_tahun_ajaran as sta1')
                ->join(
                    DB::raw('(SELECT siswa_id, MAX(tahun_ajaran_id) as latest_tahun_ajaran_id
                              FROM siswa_tahun_ajaran
                              GROUP BY siswa_id) as latest'),
                    function($join) {
                        $join->on('sta1.siswa_id', '=', 'latest.siswa_id')
                             ->on('sta1.tahun_ajaran_id', '=', 'latest.latest_tahun_ajaran_id');
                    }
                )
                ->select('sta1.status')
                ->selectRaw('COUNT(*) as jumlah')
                ->groupBy('sta1.status')
                ->get()
                ->map(function($item) {
                    return [
                        'status' => $item->status,
                        'jumlah' => (int) $item->jumlah
                    ];
                });
        }
    }

    private function getGuruPerMapelData($tahunAjaranId = null)
    {
        if ($tahunAjaranId) {
            // Filter berdasarkan tahun ajaran tertentu
            return DB::table('mata_pelajaran')
                ->leftJoin('pengajaran', 'mata_pelajaran.id', '=', 'pengajaran.mata_pelajaran_id')
                ->leftJoin('guru_tahun_ajaran', function($join) use ($tahunAjaranId) {
                    $join->on('pengajaran.guru_id', '=', 'guru_tahun_ajaran.guru_id')
                         ->on('pengajaran.tahun_ajaran_id', '=', 'guru_tahun_ajaran.tahun_ajaran_id')
                         ->where('guru_tahun_ajaran.tahun_ajaran_id', $tahunAjaranId);
                })
                ->select('mata_pelajaran.nama as mata_pelajaran')
                ->selectRaw('COUNT(DISTINCT pengajaran.guru_id) as jumlah_guru')
                ->where('pengajaran.tahun_ajaran_id', $tahunAjaranId)
                ->groupBy('mata_pelajaran.id', 'mata_pelajaran.nama')
                ->having('jumlah_guru', '>', 0)
                ->orderByRaw('COUNT(DISTINCT pengajaran.guru_id) DESC')
                ->take(10)
                ->get()
                ->map(function($mapel) {
                    return [
                        'mata_pelajaran' => $mapel->mata_pelajaran,
                        'jumlah_guru' => (int) $mapel->jumlah_guru
                    ];
                });
        } else {
            // Semua tahun ajaran - hanya guru dengan status terbaru "Aktif"
            return DB::table('mata_pelajaran')
                ->leftJoin('pengajaran', 'mata_pelajaran.id', '=', 'pengajaran.mata_pelajaran_id')
                ->leftJoin(
                    DB::raw('(SELECT guru_id, MAX(tahun_ajaran_id) as latest_tahun_ajaran_id
                              FROM guru_tahun_ajaran
                              GROUP BY guru_id) as latest_guru_status'),
                    'pengajaran.guru_id', '=', 'latest_guru_status.guru_id'
                )
                ->leftJoin('guru_tahun_ajaran as gta', function($join) {
                    $join->on('pengajaran.guru_id', '=', 'gta.guru_id')
                         ->on('latest_guru_status.latest_tahun_ajaran_id', '=', 'gta.tahun_ajaran_id');
                })
                ->select('mata_pelajaran.nama as mata_pelajaran')
                ->selectRaw('COUNT(DISTINCT CASE WHEN gta.status = "Aktif" THEN pengajaran.guru_id END) as jumlah_guru')
                ->groupBy('mata_pelajaran.id', 'mata_pelajaran.nama')
                ->having('jumlah_guru', '>', 0)
                ->orderByRaw('COUNT(DISTINCT CASE WHEN gta.status = "Aktif" THEN pengajaran.guru_id END) DESC')
                ->take(10)
                ->get()
                ->map(function($mapel) {
                    return [
                        'mata_pelajaran' => $mapel->mata_pelajaran,
                        'jumlah_guru' => (int) $mapel->jumlah_guru
                    ];
                });
        }
    }
}
