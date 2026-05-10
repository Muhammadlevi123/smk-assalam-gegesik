<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prestasi;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PrestasiController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Prestasi::with(['siswa']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_lomba', 'LIKE', "%{$search}%")
                  ->orWhere('penyelenggara', 'LIKE', "%{$search}%")
                  ->orWhere('juara', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('tingkat')) {
            $query->where('tingkat', $request->get('tingkat'));
        }

        if ($request->filled('juara')) {
            $query->where('juara', $request->get('juara'));
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal', $request->get('tahun'));
        }

        if ($request->filled('angkatan')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('angkatan', $request->get('angkatan'));
            });
        }

        if ($request->filled('siswa')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('nama', 'LIKE', "%{$request->get('siswa')}%");
            });
        }

        $prestasiRaw = $query->orderBy('tanggal', 'desc')
                             ->paginate(10)
                             ->appends($request->query());

        $prestasiRaw->getCollection()->transform(function ($prestasi) {
            $siswaPrestasi   = [];
            $angkatanTerkait = [];

            foreach ($prestasi->siswa as $siswa) {
                $siswaPrestasi[] = [
                    'siswa_id'   => $siswa->id,
                    'siswa_nama' => $siswa->nama,
                    'siswa_nis'  => $siswa->nis,
                    'angkatan'   => $siswa->angkatan,
                    'foto'       => $siswa->foto,
                ];

                if (!in_array($siswa->angkatan, $angkatanTerkait)) {
                    $angkatanTerkait[] = $siswa->angkatan;
                }
            }

            $prestasi->siswa_prestasi    = $siswaPrestasi;
            $prestasi->jumlah_siswa      = count($siswaPrestasi);
            $prestasi->angkatan_terkait  = $angkatanTerkait;
            $prestasi->tanggal_formatted = Carbon::parse($prestasi->tanggal)->translatedFormat('d M Y');
            $prestasi->tahun             = Carbon::parse($prestasi->tanggal)->year;

            return $prestasi;
        });

        $tingkatList = Prestasi::select('tingkat')
            ->distinct()->whereNotNull('tingkat')->where('tingkat', '!=', '')->orderBy('tingkat')
            ->get()->map(fn ($item) => ['value' => $item->tingkat, 'label' => ucfirst($item->tingkat)]);

        $juaraList = Prestasi::select('juara')
            ->distinct()->whereNotNull('juara')->where('juara', '!=', '')->orderBy('juara')
            ->get()->map(fn ($item) => ['value' => $item->juara, 'label' => $item->juara]);

        $tahunList = Prestasi::selectRaw('YEAR(tanggal) as tahun')
            ->distinct()->whereNotNull('tanggal')->orderBy('tahun', 'desc')
            ->get()->map(fn ($item) => ['value' => $item->tahun, 'label' => $item->tahun]);

        $angkatanList = Siswa::select('angkatan')
            ->distinct()->whereNotNull('angkatan')->orderBy('angkatan', 'desc')
            ->get()->map(fn ($item) => ['value' => $item->angkatan, 'label' => $item->angkatan]);

        return Inertia::render('admin/prestasi/Index', [
            'prestasi'     => $prestasiRaw,
            'filters'      => $request->only(['search', 'tingkat', 'juara', 'tahun', 'angkatan', 'siswa']),
            'tingkatList'  => $tingkatList,
            'juaraList'    => $juaraList,
            'tahunList'    => $tahunList,
            'angkatanList' => $angkatanList,
        ]);
    }

    public function create(): Response
    {
        $siswa = Siswa::orderBy('angkatan', 'desc')->orderBy('nama')->get();

        $siswa->transform(function ($s) {
            $s->existing_prestasi = DB::table('prestasi_siswa')
                ->where('siswa_id', $s->id)
                ->pluck('prestasi_id')
                ->map(fn ($id) => (int) $id)
                ->toArray();
            return $s;
        });

        $angkatanList = Siswa::select('angkatan')->distinct()->whereNotNull('angkatan')
            ->orderBy('angkatan', 'desc')->get()
            ->map(fn ($item) => ['value' => $item->angkatan, 'label' => $item->angkatan]);

        $tingkatOptions = [
            ['value' => 'kabupaten',     'label' => 'Kabupaten'],
            ['value' => 'provinsi',      'label' => 'Provinsi'],
            ['value' => 'nasional',      'label' => 'Nasional'],
            ['value' => 'internasional', 'label' => 'Internasional'],
        ];

        $juaraOptions = [
            ['value' => 'Juara 1',         'label' => 'Juara 1'],
            ['value' => 'Juara 2',         'label' => 'Juara 2'],
            ['value' => 'Juara 3',         'label' => 'Juara 3'],
            ['value' => 'Juara Harapan 1', 'label' => 'Juara Harapan 1'],
            ['value' => 'Juara Harapan 2', 'label' => 'Juara Harapan 2'],
            ['value' => 'Juara Harapan 3', 'label' => 'Juara Harapan 3'],
        ];

        return Inertia::render('admin/prestasi/Create', [
            'siswa'          => $siswa,
            'angkatanList'   => $angkatanList,
            'tingkatOptions' => $tingkatOptions,
            'juaraOptions'   => $juaraOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lomba'       => 'required|string|max:255',
            'tingkat'          => 'required|in:kabupaten,provinsi,nasional,internasional',
            'juara'            => 'required|string|max:50',
            'penyelenggara'    => 'nullable|string|max:255',
            'tanggal'          => 'required|date',
            'deskripsi'        => 'nullable|string|max:5000',
            'foto'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'siswa_prestasi'   => 'required|array|min:1',
            'siswa_prestasi.*' => 'required|exists:siswa,id',
        ]);

        $siswaIds = $validated['siswa_prestasi'];
        if (count($siswaIds) !== count(array_unique($siswaIds))) {
            return back()->withInput()
                         ->withErrors(['siswa_prestasi' => 'Ada siswa yang dipilih lebih dari sekali.']);
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('img/prestasi', 'public');
        }

        DB::transaction(function () use ($validated, $fotoPath) {
            $prestasi = Prestasi::create([
                'nama_lomba'    => $validated['nama_lomba'],
                'tingkat'       => $validated['tingkat'],
                'juara'         => $validated['juara'],
                'penyelenggara' => $validated['penyelenggara'] ?? null,
                'tanggal'       => $validated['tanggal'],
                'deskripsi'     => $validated['deskripsi'] ?? null,
                'foto'          => $fotoPath,
            ]);

            foreach ($validated['siswa_prestasi'] as $siswaId) {
                $prestasi->siswa()->attach($siswaId, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('admin.prestasi.index')
                         ->with('success', 'created');
    }

    public function show(string $id): Response
    {
        $prestasi = Prestasi::with([
            'siswa' => fn ($q) => $q->with([
                'kelas',
                'tahunAjaranStatus' => fn ($q) => $q->withPivot('status', 'kelulusan'),
            ])->orderBy('angkatan', 'desc')->orderBy('nama'),
        ])->findOrFail($id);

        $siswaPrestasi   = [];
        $angkatanTerkait = [];

        foreach ($prestasi->siswa as $siswa) {
            // ── Logika status sama dengan Index siswa ──────────────────
            // Prioritas 1: jika ada kelulusan terisi → tampilkan kelulusan
            // Prioritas 2: jika semua null → tampilkan status tahun terbaru
            $statusTerkini = 'Aktif';

            if ($siswa->tahunAjaranStatus && $siswa->tahunAjaranStatus->count() > 0) {
                $sorted = $siswa->tahunAjaranStatus
                    ->sortByDesc(fn ($ta) => (int) explode('/', $ta->tahun)[0]);

                $denganKelulusan = $sorted->first(fn ($ta) =>
                    !is_null($ta->pivot->kelulusan) && $ta->pivot->kelulusan !== ''
                );

                if ($denganKelulusan) {
                    $statusTerkini = $denganKelulusan->pivot->kelulusan;
                } else {
                    $statusTerkini = $sorted->first()->pivot->status;
                }
            }
            // ── End logika status ──────────────────────────────────────

            // Susun data kelas dengan tahun ajaran
            $kelasDetail = $siswa->kelas->map(function ($kelas) use ($siswa) {
                $tahunAjaranId  = $kelas->pivot->tahun_ajaran_id;
                $tahunAjaranObj = $siswa->tahunAjaranStatus->firstWhere('id', $tahunAjaranId);

                return [
                    'nama_kelas'   => $kelas->nama_kelas,
                    'jurusan'      => $kelas->jurusan,
                    'tingkat'      => $kelas->tingkat,
                    'tahun_ajaran' => $tahunAjaranObj?->tahun ?? '-',
                    'status'       => $tahunAjaranObj?->pivot->status ?? 'Aktif',
                ];
            })->sortByDesc(fn ($k) => (int) explode('/', $k['tahun_ajaran'])[0])->values();

            $siswaPrestasi[] = [
                'siswa_id'      => $siswa->id,
                'siswa_nama'    => $siswa->nama,
                'siswa_nis'     => $siswa->nis,
                'angkatan'      => $siswa->angkatan,
                'jenis_kelamin' => $siswa->jenis_kelamin,
                'alamat'        => $siswa->alamat,
                'foto'          => $siswa->foto,
                'status'        => $statusTerkini,
                'kelas_detail'  => $kelasDetail,
            ];

            if (!in_array($siswa->angkatan, $angkatanTerkait)) {
                $angkatanTerkait[] = $siswa->angkatan;
            }
        }

        $prestasi->siswa_prestasi    = $siswaPrestasi;
        $prestasi->jumlah_siswa      = count($siswaPrestasi);
        $prestasi->angkatan_terkait  = $angkatanTerkait;
        $prestasi->tanggal_formatted = Carbon::parse($prestasi->tanggal)->translatedFormat('d M Y');
        $prestasi->tahun             = Carbon::parse($prestasi->tanggal)->year;

        return Inertia::render('admin/prestasi/Show', [
            'prestasi' => $prestasi,
        ]);
    }

    public function edit(string $id): Response
    {
        $prestasi = Prestasi::with([
            'siswa' => fn ($q) => $q->orderBy('angkatan', 'desc')->orderBy('nama'),
        ])->findOrFail($id);

        $siswa = Siswa::orderBy('angkatan', 'desc')->orderBy('nama')->get();

        $siswa->transform(function ($siswaItem) use ($id) {
            $siswaItem->existing_prestasi = DB::table('prestasi_siswa')
                ->where('siswa_id', $siswaItem->id)
                ->where('prestasi_id', '!=', $id)
                ->pluck('prestasi_id')
                ->map(fn ($pid) => (int) $pid)
                ->toArray();
            return $siswaItem;
        });

        $prestasi->siswa_prestasi = $prestasi->siswa->pluck('id')->toArray();

        $angkatanList = Siswa::select('angkatan')->distinct()->whereNotNull('angkatan')
            ->orderBy('angkatan', 'desc')->get()
            ->map(fn ($item) => ['value' => $item->angkatan, 'label' => $item->angkatan]);

        $tingkatOptions = [
            ['value' => 'kabupaten',     'label' => 'Kabupaten'],
            ['value' => 'provinsi',      'label' => 'Provinsi'],
            ['value' => 'nasional',      'label' => 'Nasional'],
            ['value' => 'internasional', 'label' => 'Internasional'],
        ];

        $juaraOptions = [
            ['value' => 'Juara 1',         'label' => 'Juara 1'],
            ['value' => 'Juara 2',         'label' => 'Juara 2'],
            ['value' => 'Juara 3',         'label' => 'Juara 3'],
            ['value' => 'Juara Harapan 1', 'label' => 'Juara Harapan 1'],
            ['value' => 'Juara Harapan 2', 'label' => 'Juara Harapan 2'],
            ['value' => 'Juara Harapan 3', 'label' => 'Juara Harapan 3'],
        ];

        return Inertia::render('admin/prestasi/Edit', [
            'prestasi'       => $prestasi,
            'siswa'          => $siswa,
            'angkatanList'   => $angkatanList,
            'tingkatOptions' => $tingkatOptions,
            'juaraOptions'   => $juaraOptions,
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $prestasi = Prestasi::findOrFail($id);

        $validated = $request->validate([
            'nama_lomba'       => 'required|string|max:255',
            'tingkat'          => 'required|in:kabupaten,provinsi,nasional,internasional',
            'juara'            => 'required|string|max:50',
            'penyelenggara'    => 'nullable|string|max:255',
            'tanggal'          => 'required|date',
            'deskripsi'        => 'nullable|string|max:5000',
            'foto'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'siswa_prestasi'   => 'required|array|min:1',
            'siswa_prestasi.*' => 'required|exists:siswa,id',
        ]);

        $siswaIds = $validated['siswa_prestasi'];
        if (count($siswaIds) !== count(array_unique($siswaIds))) {
            return back()->withInput()
                         ->withErrors(['siswa_prestasi' => 'Ada siswa yang dipilih lebih dari sekali.']);
        }

        $fotoPath = $prestasi->foto;
        if ($request->hasFile('foto')) {
            if ($prestasi->foto) {
                Storage::disk('public')->delete($prestasi->foto);
            }
            $fotoPath = $request->file('foto')->store('img/prestasi', 'public');
        }

        DB::transaction(function () use ($prestasi, $validated, $fotoPath) {
            $prestasi->update([
                'nama_lomba'    => $validated['nama_lomba'],
                'tingkat'       => $validated['tingkat'],
                'juara'         => $validated['juara'],
                'penyelenggara' => $validated['penyelenggara'] ?? null,
                'tanggal'       => $validated['tanggal'],
                'deskripsi'     => $validated['deskripsi'] ?? null,
                'foto'          => $fotoPath,
            ]);

            $prestasi->siswa()->sync($validated['siswa_prestasi']);
        });

        return redirect()->route('admin.prestasi.index')
                         ->with('success', 'updated');
    }

    public function destroy(string $id): RedirectResponse
    {
        $prestasi = Prestasi::findOrFail($id);

        try {
            DB::transaction(function () use ($prestasi) {
                if ($prestasi->foto) {
                    Storage::disk('public')->delete($prestasi->foto);
                }
                $prestasi->siswa()->detach();
                $prestasi->delete();
            });

            return redirect()->route('admin.prestasi.index')
                             ->with('success', 'deleted');

        } catch (\Exception $e) {
            \Log::error('Error deleting prestasi: ' . $e->getMessage());

            return back()->withErrors([
                'delete_error' => 'Terjadi kesalahan saat menghapus prestasi. Silakan coba lagi.',
            ]);
        }
    }
}
