<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\TahunAjaran;
use App\Models\MataPelajaran;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    // ── Helper: transform satu Eloquent Guru → array camelCase ──────────────
    private function transformGuru(Guru $g): array
    {
        return [
            'id'            => $g->id,
            'nama'          => $g->nama,
            'jenis_kelamin' => $g->jenis_kelamin,
            'alamat'        => $g->alamat,
            'foto'          => $g->foto,
            'created_at'    => $g->created_at,
            'updated_at'    => $g->updated_at,

            'tahunAjaran' => $g->tahunAjaran->map(fn ($t) => [
                'id'    => $t->id,
                'tahun' => $t->tahun,
                'pivot' => [
                    'guru_id'         => $t->pivot->guru_id,
                    'tahun_ajaran_id' => $t->pivot->tahun_ajaran_id,
                    'status'          => $t->pivot->status,
                ],
            ])->values()->toArray(),

            'mataPelajaran' => $g->mataPelajaran->map(fn ($m) => [
                'id'    => $m->id,
                'nama'  => $m->nama,
                'pivot' => [
                    'guru_id'           => $m->pivot->guru_id,
                    'mata_pelajaran_id' => $m->pivot->mata_pelajaran_id,
                    'tahun_ajaran_id'   => $m->pivot->tahun_ajaran_id,
                ],
            ])->values()->toArray(),

            'kelasAsWali' => $g->kelasAsWali->map(fn ($k) => [
                'id'         => $k->id,
                'nama_kelas' => $k->nama_kelas,
                'jurusan'    => $k->jurusan,
                'tingkat'    => $k->tingkat,
                'pivot'      => [
                    'guru_id'         => $k->pivot->guru_id,
                    'kelas_id'        => $k->pivot->kelas_id,
                    'tahun_ajaran_id' => $k->pivot->tahun_ajaran_id,
                ],
            ])->values()->toArray(),
        ];
    }

    // ── index ────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Guru::with(['mataPelajaran', 'kelasAsWali', 'tahunAjaran']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('nama', 'LIKE', "%{$search}%");
        }

        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->get('jenis_kelamin'));
        }

        if ($request->filled('status')) {
            $query->whereExists(function ($subquery) use ($request) {
                $subquery->select(DB::raw(1))
                    ->from('guru_tahun_ajaran as gta')
                    ->join('tahun_ajaran as ta', 'gta.tahun_ajaran_id', '=', 'ta.id')
                    ->whereColumn('gta.guru_id', 'guru.id')
                    ->where('gta.status', $request->get('status'))
                    ->whereRaw('ta.id = (
                        SELECT tahun_ajaran_id FROM guru_tahun_ajaran
                        JOIN tahun_ajaran ON guru_tahun_ajaran.tahun_ajaran_id = tahun_ajaran.id
                        WHERE guru_tahun_ajaran.guru_id = guru.id
                        ORDER BY CAST(SUBSTRING_INDEX(tahun_ajaran.tahun, "/", 1) AS UNSIGNED) DESC
                        LIMIT 1
                    )');
            });
        }

        if ($request->filled('tahun_ajaran')) {
            $query->whereHas('tahunAjaran', fn ($q) => $q->where('tahun', $request->get('tahun_ajaran')));
        }

        if ($request->filled('mata_pelajaran')) {
            $query->whereHas('mataPelajaran', fn ($q) => $q->where('nama', $request->get('mata_pelajaran')));
        }

        $paginator = $query->orderBy('nama')->paginate(10)->withQueryString();

        $guru = [
            'data'         => $paginator->getCollection()->map(fn ($g) => $this->transformGuru($g))->all(),
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'per_page'     => $paginator->perPage(),
            'total'        => $paginator->total(),
            'links'        => $paginator->linkCollection()->toArray(),
        ];

        return Inertia::render('admin/guru/Index', [
            'guru'              => $guru,
            'filters'           => $request->only(['search', 'jenis_kelamin', 'status', 'tahun_ajaran', 'mata_pelajaran']),
            'tahunAjaranList'   => TahunAjaran::orderBy('tahun')->get()->map(fn ($t) => ['id' => $t->id, 'tahun' => $t->tahun]),
            'mataPelajaranList' => MataPelajaran::orderBy('nama')->get()->map(fn ($m) => ['id' => $m->id, 'nama' => $m->nama]),
        ]);
    }

    // ── create ───────────────────────────────────────────────────────────────
    public function create(): Response
    {
        $tahunAjaran = TahunAjaran::orderBy('tahun')->get();

        $kelas = Kelas::orderBy('nama_kelas')->get()->map(function ($k) {
            return [
                'id'                         => $k->id,
                'nama_kelas'                 => $k->nama_kelas,
                'existing_wali_tahun_ajaran' => DB::table('wali_kelas')
                    ->where('kelas_id', $k->id)
                    ->pluck('tahun_ajaran_id')
                    ->map(fn ($id) => (int) $id)
                    ->toArray(),
            ];
        });

        $existingMataPelajaran = MataPelajaran::orderBy('nama')
            ->get()
            ->map(fn ($m) => ['nama' => $m->nama])
            ->toArray();

        return Inertia::render('admin/guru/Create', [
            'tahunAjaran'           => $tahunAjaran,
            'kelas'                 => $kelas,
            'existingMataPelajaran' => $existingMataPelajaran,
            'previous_url'          => url()->previous(route('admin.guru.index')),
        ]);
    }

    // ── store ────────────────────────────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama'                                  => 'required|string|max:255',
            'jenis_kelamin'                         => 'required|in:Laki-laki,Perempuan',
            'alamat'                                => 'nullable|string|max:500',
            'foto'                                  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status_tahun_ajaran'                   => 'required|array|min:1',
            'status_tahun_ajaran.*.tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'status_tahun_ajaran.*.status'          => 'required|in:Aktif,Nonaktif',
            'pengajaran'                            => 'nullable|array',
            'pengajaran.*.nama_mata_pelajaran'      => 'required_with:pengajaran|string|max:255',
            'pengajaran.*.tahun_ajaran_id'          => 'required_with:pengajaran|exists:tahun_ajaran,id',
            'wali_kelas'                            => 'nullable|array',
            'wali_kelas.*.kelas_id'                 => 'required|exists:kelas,id',
            'wali_kelas.*.tahun_ajaran_id'          => 'required|exists:tahun_ajaran,id',
            'previous_url'                          => 'nullable|string',
        ]);

        if (!empty($validated['status_tahun_ajaran'])) {
            $ids = array_column($validated['status_tahun_ajaran'], 'tahun_ajaran_id');
            if (count($ids) !== count(array_unique($ids))) {
                return redirect()->back()->withInput()
                    ->withErrors(['status_tahun_ajaran' => 'Ada tahun ajaran yang duplikat dalam pengaturan status.']);
            }
        }

        if (!empty($validated['wali_kelas'])) {
            $combos = [];
            foreach ($validated['wali_kelas'] as $i => $wk) {
                $combo = $wk['kelas_id'] . '-' . $wk['tahun_ajaran_id'];
                if (in_array($combo, $combos)) {
                    return redirect()->back()->withInput()
                        ->withErrors(["wali_kelas.{$i}.kelas_id" => 'Kombinasi kelas dan tahun ajaran sudah ada.']);
                }
                $combos[] = $combo;

                if (DB::table('wali_kelas')->where('kelas_id', $wk['kelas_id'])->where('tahun_ajaran_id', $wk['tahun_ajaran_id'])->exists()) {
                    $k  = Kelas::find($wk['kelas_id']);
                    $ta = TahunAjaran::find($wk['tahun_ajaran_id']);
                    return redirect()->back()->withInput()
                        ->withErrors(["wali_kelas.{$i}.kelas_id" => "Kelas {$k->nama_kelas} sudah memiliki wali kelas pada tahun ajaran {$ta->tahun}"]);
                }
            }
        }

        $fotoPath = $request->hasFile('foto')
            ? $request->file('foto')->store('img/guru', 'public')
            : null;

        DB::transaction(function () use ($validated, $fotoPath) {
            $guru = Guru::create([
                'nama'          => $validated['nama'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'alamat'        => $validated['alamat'],
                'foto'          => $fotoPath,
            ]);

            foreach ($validated['status_tahun_ajaran'] as $s) {
                $guru->tahunAjaran()->attach($s['tahun_ajaran_id'], [
                    'status' => $s['status'], 'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            foreach ($validated['pengajaran'] ?? [] as $p) {
                $mapel = MataPelajaran::firstOrCreate([
                    'nama' => trim($p['nama_mata_pelajaran']),
                ]);
                $guru->mataPelajaran()->attach($mapel->id, [
                    'tahun_ajaran_id' => $p['tahun_ajaran_id'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            foreach ($validated['wali_kelas'] ?? [] as $wk) {
                if (!DB::table('wali_kelas')->where('kelas_id', $wk['kelas_id'])->where('tahun_ajaran_id', $wk['tahun_ajaran_id'])->exists()) {
                    $guru->kelasAsWali()->attach($wk['kelas_id'], [
                        'tahun_ajaran_id' => $wk['tahun_ajaran_id'],
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }
        });

        return redirect($validated['previous_url'] ?? route('admin.guru.index'))
            ->with('success', 'created');
    }

    // ── show ─────────────────────────────────────────────────────────────────
    public function show(string $id): Response
    {
        $g = Guru::with([
            'tahunAjaran'   => fn ($q) => $q->orderBy('tahun'),
            'mataPelajaran' => fn ($q) => $q->orderBy('nama'),
            'kelasAsWali'   => fn ($q) => $q->orderBy('nama_kelas'),
        ])->findOrFail($id);

        return Inertia::render('admin/guru/Show', [
            'guru' => $this->transformGuru($g),
        ]);
    }

    // ── edit ─────────────────────────────────────────────────────────────────
    public function edit(string $id): Response
    {
        $guru = Guru::with([
            'tahunAjaran'   => fn ($q) => $q->orderBy('tahun'),
            'mataPelajaran' => fn ($q) => $q->orderBy('nama'),
            'kelasAsWali'   => fn ($q) => $q->orderBy('nama_kelas'),
        ])->findOrFail($id);

        $guru->status_tahun_ajaran = $guru->tahunAjaran->map(fn ($t) => [
            'tahun_ajaran_id' => $t->id,
            'status'          => $t->pivot->status,
        ]);

        $guru->pengajaran = $guru->mataPelajaran->map(fn ($m) => [
            'nama_mata_pelajaran' => $m->nama,
            'tahun_ajaran_id'     => $m->pivot->tahun_ajaran_id,
        ]);

        $guru->wali_kelas = $guru->kelasAsWali->map(fn ($k) => [
            'kelas_id'        => $k->id,
            'tahun_ajaran_id' => $k->pivot->tahun_ajaran_id,
        ]);

        $kelas = Kelas::orderBy('nama_kelas')->get()->map(function ($k) use ($id) {
            return [
                'id'                         => $k->id,
                'nama_kelas'                 => $k->nama_kelas,
                'existing_wali_tahun_ajaran' => DB::table('wali_kelas')
                    ->where('kelas_id', $k->id)
                    ->where('guru_id', '!=', $id)
                    ->pluck('tahun_ajaran_id')
                    ->map(fn ($i) => (int) $i)
                    ->toArray(),
            ];
        });

        $existingMataPelajaran = MataPelajaran::orderBy('nama')
            ->get()
            ->map(fn ($m) => ['nama' => $m->nama])
            ->toArray();

        return Inertia::render('admin/guru/Edit', [
            'guru'                  => $guru,
            'tahunAjaran'           => TahunAjaran::orderBy('tahun')->get(),
            'kelas'                 => $kelas,
            'existingMataPelajaran' => $existingMataPelajaran,
            'previous_url'          => url()->previous(route('admin.guru.index')),
        ]);
    }

    // ── update ───────────────────────────────────────────────────────────────
    public function update(Request $request, string $id): RedirectResponse
    {
        $guru = Guru::findOrFail($id);

        $validated = $request->validate([
            'nama'                                  => 'required|string|max:255',
            'jenis_kelamin'                         => 'required|in:Laki-laki,Perempuan',
            'alamat'                                => 'nullable|string|max:500',
            'foto'                                  => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status_tahun_ajaran'                   => 'required|array|min:1',
            'status_tahun_ajaran.*.tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'status_tahun_ajaran.*.status'          => 'required|in:Aktif,Nonaktif',
            'pengajaran'                            => 'nullable|array',
            'pengajaran.*.nama_mata_pelajaran'      => 'required_with:pengajaran|string|max:255',
            'pengajaran.*.tahun_ajaran_id'          => 'required_with:pengajaran|exists:tahun_ajaran,id',
            'wali_kelas'                            => 'nullable|array',
            'wali_kelas.*.kelas_id'                 => 'required|exists:kelas,id',
            'wali_kelas.*.tahun_ajaran_id'          => 'required|exists:tahun_ajaran,id',
            'previous_url'                          => 'nullable|string',
        ]);

        if (!empty($validated['status_tahun_ajaran'])) {
            $ids = array_column($validated['status_tahun_ajaran'], 'tahun_ajaran_id');
            if (count($ids) !== count(array_unique($ids))) {
                return redirect()->back()->withInput()
                    ->withErrors(['status_tahun_ajaran' => 'Ada tahun ajaran yang duplikat dalam pengaturan status.']);
            }
        }

        if (!empty($validated['wali_kelas'])) {
            $combos = [];
            foreach ($validated['wali_kelas'] as $i => $wk) {
                $combo = $wk['kelas_id'] . '-' . $wk['tahun_ajaran_id'];
                if (in_array($combo, $combos)) {
                    return redirect()->back()->withInput()
                        ->withErrors(["wali_kelas.{$i}.kelas_id" => 'Kombinasi kelas dan tahun ajaran sudah ada.']);
                }
                $combos[] = $combo;

                if (DB::table('wali_kelas')->where('kelas_id', $wk['kelas_id'])->where('tahun_ajaran_id', $wk['tahun_ajaran_id'])->where('guru_id', '!=', $id)->exists()) {
                    $k  = Kelas::find($wk['kelas_id']);
                    $ta = TahunAjaran::find($wk['tahun_ajaran_id']);
                    return redirect()->back()->withInput()
                        ->withErrors(["wali_kelas.{$i}.kelas_id" => "Kelas {$k->nama_kelas} sudah memiliki wali kelas pada tahun ajaran {$ta->tahun}"]);
                }
            }
        }

        $fotoPath = $guru->foto;
        if ($request->hasFile('foto')) {
            if ($guru->foto) Storage::disk('public')->delete($guru->foto);
            $fotoPath = $request->file('foto')->store('img/guru', 'public');
        } elseif ($request->input('hapus_foto') == '1') {
            if ($guru->foto) Storage::disk('public')->delete($guru->foto);
            $fotoPath = null;
        }

        DB::transaction(function () use ($guru, $validated, $fotoPath) {
            $guru->update([
                'nama'          => $validated['nama'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'alamat'        => $validated['alamat'],
                'foto'          => $fotoPath,
            ]);

            $syncData = [];
            foreach ($validated['status_tahun_ajaran'] as $s) {
                $syncData[$s['tahun_ajaran_id']] = [
                    'status'     => $s['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $guru->tahunAjaran()->sync($syncData);

            $guru->mataPelajaran()->detach();
            foreach ($validated['pengajaran'] ?? [] as $p) {
                $mapel = MataPelajaran::firstOrCreate([
                    'nama' => trim($p['nama_mata_pelajaran']),
                ]);
                $guru->mataPelajaran()->attach($mapel->id, [
                    'tahun_ajaran_id' => $p['tahun_ajaran_id'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            $guru->kelasAsWali()->detach();
            foreach ($validated['wali_kelas'] ?? [] as $wk) {
                if (!DB::table('wali_kelas')->where('kelas_id', $wk['kelas_id'])->where('tahun_ajaran_id', $wk['tahun_ajaran_id'])->where('guru_id', '!=', $guru->id)->exists()) {
                    $guru->kelasAsWali()->attach($wk['kelas_id'], [
                        'tahun_ajaran_id' => $wk['tahun_ajaran_id'],
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);
                }
            }
        });

        return redirect($validated['previous_url'] ?? route('admin.guru.index'))
            ->with('success', 'updated');
    }

    // ── destroy ──────────────────────────────────────────────────────────────
    public function destroy(string $id): RedirectResponse
    {
        $guru = Guru::findOrFail($id);

        DB::transaction(function () use ($guru) {
            if ($guru->foto) Storage::disk('public')->delete($guru->foto);
            $guru->tahunAjaran()->detach();
            $guru->mataPelajaran()->detach();
            $guru->kelasAsWali()->detach();
            $guru->delete();
        });

        return redirect()->route('admin.guru.index')->with('success', 'deleted');
    }
}
