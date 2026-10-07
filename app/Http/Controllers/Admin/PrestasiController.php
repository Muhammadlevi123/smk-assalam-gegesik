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
    // ─────────────────────────────────────────────────────────────
    // HELPER
    // ─────────────────────────────────────────────────────────────

    /**
     * Bersihkan input siswa manual: buang baris tanpa nama, rapikan spasi.
     */
    private function cleanManual(?array $manual): array
    {
        return collect($manual ?? [])
            ->filter(fn ($p) => filled($p['nama'] ?? null))
            ->map(fn ($p) => [
                'nama'     => trim($p['nama']),
                'angkatan' => filled($p['angkatan'] ?? null) ? trim($p['angkatan']) : null,
            ])
            ->values()
            ->all();
    }

    /**
     * Ubah siswa_manual menjadi bentuk yang sama dengan siswa_prestasi,
     * supaya Index.vue dan Show.vue bisa menampilkannya.
     */
    private function manualAsSiswaList(Prestasi $prestasi): array
    {
        return collect($prestasi->siswa_manual ?? [])
            ->map(fn ($p) => [
                'siswa_id'   => null,
                'siswa_nama' => $p['nama'] ?? '',
                'siswa_nis'  => null,
                'angkatan'   => filled($p['angkatan'] ?? null) ? $p['angkatan'] : null,
                'foto'       => null,
                'manual'     => true,
            ])
            ->values()
            ->all();
    }

    private function tingkatOptions(): array
    {
        return [
            ['value' => 'kabupaten',     'label' => 'Kabupaten'],
            ['value' => 'provinsi',      'label' => 'Provinsi'],
            ['value' => 'nasional',      'label' => 'Nasional'],
            ['value' => 'internasional', 'label' => 'Internasional'],
        ];
    }

    private function juaraOptions(): array
    {
        return [
            ['value' => 'Juara 1',         'label' => 'Juara 1'],
            ['value' => 'Juara 2',         'label' => 'Juara 2'],
            ['value' => 'Juara 3',         'label' => 'Juara 3'],
            ['value' => 'Juara Harapan 1', 'label' => 'Juara Harapan 1'],
            ['value' => 'Juara Harapan 2', 'label' => 'Juara Harapan 2'],
            ['value' => 'Juara Harapan 3', 'label' => 'Juara Harapan 3'],
            ['value' => 'Peserta',         'label' => 'Peserta'],
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────

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

        // Angkatan: cari di siswa relasi ATAU di siswa manual
        if ($request->filled('angkatan')) {
            $angkatan = (string) $request->get('angkatan');
            $query->where(function ($q) use ($angkatan) {
                $q->whereHas('siswa', fn ($s) => $s->where('angkatan', $angkatan))
                    ->orWhereJsonContains('siswa_manual', ['angkatan' => $angkatan]);
            });
        }

        // Nama siswa: cari di siswa relasi ATAU di siswa manual
        if ($request->filled('siswa')) {
            $nama = $request->get('siswa');
            $query->where(function ($q) use ($nama) {
                $q->whereHas('siswa', fn ($s) => $s->where('nama', 'LIKE', "%{$nama}%"))
                    ->orWhere('siswa_manual', 'LIKE', "%{$nama}%");
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
                    'manual'     => false,
                ];

                if (!in_array($siswa->angkatan, $angkatanTerkait)) {
                    $angkatanTerkait[] = $siswa->angkatan;
                }
            }

            // Gabungkan siswa input manual
            foreach ($this->manualAsSiswaList($prestasi) as $manual) {
                $siswaPrestasi[] = $manual;

                if ($manual['angkatan'] !== null && !in_array($manual['angkatan'], $angkatanTerkait)) {
                    $angkatanTerkait[] = $manual['angkatan'];
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

    // ─────────────────────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────────────────────

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

        return Inertia::render('admin/prestasi/Create', [
            'siswa'          => $siswa,
            'angkatanList'   => $angkatanList,
            'tingkatOptions' => $this->tingkatOptions(),
            'juaraOptions'   => $this->juaraOptions(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lomba'              => 'required|string|max:255',
            'tingkat'                 => 'required|in:kabupaten,provinsi,nasional,internasional',
            'juara'                   => 'required|string|max:50',
            'penyelenggara'           => 'nullable|string|max:255',
            'tanggal'                 => 'required|date',
            'deskripsi'               => 'nullable|string|max:5000',
            'foto'                    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'siswa_prestasi'          => 'nullable|array',
            'siswa_prestasi.*'        => 'required|exists:siswa,id',
            'siswa_manual'            => 'nullable|array',
            'siswa_manual.*.nama'     => 'required|string|max:255',
            'siswa_manual.*.angkatan' => 'nullable|string|max:20',
        ]);

        $siswaIds    = $validated['siswa_prestasi'] ?? [];
        $siswaManual = $this->cleanManual($validated['siswa_manual'] ?? []);

        if (empty($siswaIds) && empty($siswaManual)) {
            return back()->withInput()->withErrors([
                'siswa_prestasi' => 'Pilih minimal satu siswa dari daftar atau isi siswa secara manual.',
            ]);
        }

        if (count($siswaIds) !== count(array_unique($siswaIds))) {
            return back()->withInput()->withErrors([
                'siswa_prestasi' => 'Ada siswa yang dipilih lebih dari sekali.',
            ]);
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('img/prestasi', 'public');
        }

        DB::transaction(function () use ($validated, $fotoPath, $siswaIds, $siswaManual) {
            $prestasi = Prestasi::create([
                'nama_lomba'    => $validated['nama_lomba'],
                'tingkat'       => $validated['tingkat'],
                'juara'         => $validated['juara'],
                'penyelenggara' => $validated['penyelenggara'] ?? null,
                'tanggal'       => $validated['tanggal'],
                'deskripsi'     => $validated['deskripsi'] ?? null,
                'foto'          => $fotoPath,
                'siswa_manual'  => $siswaManual ?: null,
            ]);

            foreach ($siswaIds as $siswaId) {
                $prestasi->siswa()->attach($siswaId, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('admin.prestasi.index')->with('success', 'created');
    }

    // ─────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────

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

                $denganKelulusan = $sorted->first(
                    fn ($ta) => !is_null($ta->pivot->kelulusan) && $ta->pivot->kelulusan !== ''
                );

                if ($denganKelulusan) {
                    $statusTerkini = $denganKelulusan->pivot->kelulusan;
                } else {
                    $statusTerkini = $sorted->first()->pivot->status;
                }
            }
            // ── End logika status ──────────────────────────────────────

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
                'manual'        => false,
            ];

            if (!in_array($siswa->angkatan, $angkatanTerkait)) {
                $angkatanTerkait[] = $siswa->angkatan;
            }
        }

        // Gabungkan siswa input manual
        foreach ($this->manualAsSiswaList($prestasi) as $manual) {
            $siswaPrestasi[] = array_merge($manual, [
                'jenis_kelamin' => null,
                'alamat'        => null,
                'status'        => null,
                'kelas_detail'  => [],
            ]);

            if ($manual['angkatan'] !== null && !in_array($manual['angkatan'], $angkatanTerkait)) {
                $angkatanTerkait[] = $manual['angkatan'];
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

    // ─────────────────────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────────────────────

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

        // ID siswa dari database yang sudah terpilih
        $prestasi->siswa_prestasi = $prestasi->siswa->pluck('id')->toArray();
        // 'siswa_manual' otomatis ikut terkirim (sudah di-cast array di model)

        $angkatanList = Siswa::select('angkatan')->distinct()->whereNotNull('angkatan')
            ->orderBy('angkatan', 'desc')->get()
            ->map(fn ($item) => ['value' => $item->angkatan, 'label' => $item->angkatan]);

        return Inertia::render('admin/prestasi/Edit', [
            'prestasi'       => $prestasi,
            'siswa'          => $siswa,
            'angkatanList'   => $angkatanList,
            'tingkatOptions' => $this->tingkatOptions(),
            'juaraOptions'   => $this->juaraOptions(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────

    public function update(Request $request, string $id): RedirectResponse
    {
        $prestasi = Prestasi::findOrFail($id);

        $validated = $request->validate([
            'nama_lomba'              => 'required|string|max:255',
            'tingkat'                 => 'required|in:kabupaten,provinsi,nasional,internasional',
            'juara'                   => 'required|string|max:50',
            'penyelenggara'           => 'nullable|string|max:255',
            'tanggal'                 => 'required|date',
            'deskripsi'               => 'nullable|string|max:5000',
            'foto'                    => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'remove_foto'             => 'nullable|boolean',
            'siswa_prestasi'          => 'nullable|array',
            'siswa_prestasi.*'        => 'required|exists:siswa,id',
            'siswa_manual'            => 'nullable|array',
            'siswa_manual.*.nama'     => 'required|string|max:255',
            'siswa_manual.*.angkatan' => 'nullable|string|max:20',
        ]);

        $siswaIds    = $validated['siswa_prestasi'] ?? [];
        $siswaManual = $this->cleanManual($validated['siswa_manual'] ?? []);

        if (empty($siswaIds) && empty($siswaManual)) {
            return back()->withInput()->withErrors([
                'siswa_prestasi' => 'Pilih minimal satu siswa dari daftar atau isi siswa secara manual.',
            ]);
        }

        if (count($siswaIds) !== count(array_unique($siswaIds))) {
            return back()->withInput()->withErrors([
                'siswa_prestasi' => 'Ada siswa yang dipilih lebih dari sekali.',
            ]);
        }

        // Foto: ganti, hapus, atau biarkan
        $fotoPath = $prestasi->foto;
        if ($request->hasFile('foto')) {
            if ($prestasi->foto) {
                Storage::disk('public')->delete($prestasi->foto);
            }
            $fotoPath = $request->file('foto')->store('img/prestasi', 'public');
        } elseif ($request->boolean('remove_foto') && $prestasi->foto) {
            Storage::disk('public')->delete($prestasi->foto);
            $fotoPath = null;
        }

        DB::transaction(function () use ($prestasi, $validated, $fotoPath, $siswaIds, $siswaManual) {
            $prestasi->update([
                'nama_lomba'    => $validated['nama_lomba'],
                'tingkat'       => $validated['tingkat'],
                'juara'         => $validated['juara'],
                'penyelenggara' => $validated['penyelenggara'] ?? null,
                'tanggal'       => $validated['tanggal'],
                'deskripsi'     => $validated['deskripsi'] ?? null,
                'foto'          => $fotoPath,
                'siswa_manual'  => $siswaManual ?: null,
            ]);

            $prestasi->siswa()->sync($siswaIds);
        });

        return redirect()->route('admin.prestasi.index')
            ->with('success', 'updated');
    }

    // ─────────────────────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────────────────────

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
