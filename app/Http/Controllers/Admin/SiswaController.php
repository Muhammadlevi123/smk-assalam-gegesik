<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class SiswaController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Siswa::with([
            'kelas',
            'tahunAjaranStatus' => function ($query) {
                $query->withPivot('status', 'kelulusan')
                      ->orderBy('tahun_ajaran.tahun');
            },
        ]);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('nis', 'LIKE', "%{$search}%")
                  ->orWhere('angkatan', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('kelas')) {
            $query->whereHas('kelas', function ($q) use ($request) {
                $q->where('nama_kelas', $request->get('kelas'));
            });
        }

        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->get('jenis_kelamin'));
        }

        if ($request->filled('status')) {
            $query->whereExists(function ($subquery) use ($request) {
                $subquery->select(DB::raw(1))
                    ->from('siswa_tahun_ajaran as sta')
                    ->join('tahun_ajaran as ta', 'sta.tahun_ajaran_id', '=', 'ta.id')
                    ->whereColumn('sta.siswa_id', 'siswa.id')
                    ->where('sta.status', $request->get('status'))
                    ->whereRaw('ta.id = (
                        SELECT tahun_ajaran_id
                        FROM siswa_tahun_ajaran
                        JOIN tahun_ajaran ON siswa_tahun_ajaran.tahun_ajaran_id = tahun_ajaran.id
                        WHERE siswa_tahun_ajaran.siswa_id = siswa.id
                        ORDER BY CAST(SUBSTRING_INDEX(tahun_ajaran.tahun, "/", 1) AS UNSIGNED) DESC
                        LIMIT 1
                    )');
            });
        }

        if ($request->filled('kelulusan')) {
            $kelulusan = $request->get('kelulusan');
            if ($kelulusan === 'belum') {
                $query->whereDoesntHave('tahunAjaranStatus', function ($sub) {
                    $sub->whereNotNull('siswa_tahun_ajaran.kelulusan');
                });
            } elseif ($kelulusan === 'Lulus') {
                $query->whereHas('tahunAjaranStatus', function ($sub) {
                    $sub->where('siswa_tahun_ajaran.kelulusan', 'Lulus');
                });
            } elseif ($kelulusan === 'Tidak Lulus') {
                $query->whereHas('tahunAjaranStatus', function ($sub) {
                    $sub->where('siswa_tahun_ajaran.kelulusan', 'Tidak Lulus');
                });
            }
        }

        if ($request->filled('tahun_ajaran')) {
            $query->whereHas('tahunAjaranStatus', function ($q) use ($request) {
                $q->where('tahun', $request->get('tahun_ajaran'));
            });
        }

        if ($request->filled('angkatan')) {
            $query->where('angkatan', $request->get('angkatan'));
        }

        $siswa = $query->orderBy('nama')
                       ->paginate(10)
                       ->withQueryString();

        $kelasList = Kelas::orderBy('nama_kelas')->get()->map(fn ($k) => [
            'id'   => $k->id,
            'nama' => $k->nama_kelas,
        ]);

        $tahunAjaranList = TahunAjaran::orderBy('tahun')->get()->map(fn ($t) => [
            'id'    => $t->id,
            'tahun' => $t->tahun,
        ]);

        $angkatanList = Siswa::select('angkatan')
            ->distinct()
            ->orderBy('angkatan')
            ->pluck('angkatan')
            ->map(fn ($a) => ['value' => $a, 'label' => $a]);

        $siswaData = $siswa->through(function ($s) {
            return [
                'id'                => $s->id,
                'nis'               => $s->nis,
                'nama'              => $s->nama,
                'jenis_kelamin'     => $s->jenis_kelamin,
                'alamat'            => $s->alamat,
                'angkatan'          => $s->angkatan,
                'foto'              => $s->foto,
                'created_at'        => $s->created_at,
                'updated_at'        => $s->updated_at,
                'kelas'             => $s->kelas,
                'tahunAjaranStatus' => $s->tahunAjaranStatus->map(fn ($ta) => [
                    'id'    => $ta->id,
                    'tahun' => $ta->tahun,
                    'pivot' => [
                        'siswa_id'        => $ta->pivot->siswa_id,
                        'tahun_ajaran_id' => $ta->pivot->tahun_ajaran_id,
                        'status'          => $ta->pivot->status,
                        'kelulusan'       => $ta->pivot->kelulusan,
                    ],
                ]),
            ];
        });

        return Inertia::render('admin/siswa/Index', [
            'siswa'           => $siswaData,
            'filters'         => $request->only([
                'search', 'kelas', 'jenis_kelamin', 'status', 'kelulusan', 'tahun_ajaran', 'angkatan',
            ]),
            'kelasList'       => $kelasList,
            'tahunAjaranList' => $tahunAjaranList,
            'angkatanList'    => $angkatanList,
        ]);
    }

    public function create(): Response
    {
        $kelas       = Kelas::orderBy('nama_kelas')->get();
        $tahunAjaran = TahunAjaran::orderBy('tahun')->get();

        $kelasData = $kelas->map(fn ($k) => [
            'id'         => $k->id,
            'nama_kelas' => $k->nama_kelas,
            'jurusan'    => $k->jurusan,
            'tingkat'    => $k->tingkat,
        ]);

        return Inertia::render('admin/siswa/Create', [
            'kelas'       => $kelasData,
            'tahunAjaran' => $tahunAjaran,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nis'                                       => 'required|string|unique:siswa,nis',
            'nama'                                      => 'required|string|max:255',
            'jenis_kelamin'                             => 'required|in:Laki-laki,Perempuan',
            'alamat'                                    => 'nullable|string|max:500',
            'angkatan'                                  => 'required|string|max:255',
            'foto'                                      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'kelas_tahun_ajaran'                        => 'required|array|min:1',
            'kelas_tahun_ajaran.*.kelas_id'             => 'required|exists:kelas,id',
            'kelas_tahun_ajaran.*.tahun_ajaran_id'      => 'required|exists:tahun_ajaran,id',
            'status_tahun_ajaran'                       => 'required|array|min:1',
            'status_tahun_ajaran.*.tahun_ajaran_id'     => 'required|exists:tahun_ajaran,id',
            'status_tahun_ajaran.*.status'              => 'required|in:Aktif,Nonaktif,Pindah',
            'status_tahun_ajaran.*.kelulusan'           => 'nullable|in:Lulus,Tidak Lulus',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('img/siswa', 'public');
        }

        $siswaData = [
            'nis'           => $validated['nis'],
            'nama'          => $validated['nama'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'alamat'        => $validated['alamat'],
            'angkatan'      => $validated['angkatan'],
            'foto'          => $validated['foto'] ?? null,
        ];

        DB::transaction(function () use ($siswaData, $validated) {
            $siswa = Siswa::create($siswaData);

            foreach ($validated['kelas_tahun_ajaran'] as $combo) {
                $siswa->kelas()->attach($combo['kelas_id'], [
                    'tahun_ajaran_id' => $combo['tahun_ajaran_id'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            foreach ($validated['status_tahun_ajaran'] as $statusData) {
                $siswa->tahunAjaranStatus()->syncWithoutDetaching([
                    $statusData['tahun_ajaran_id'] => [
                        'status'     => $statusData['status'],
                        'kelulusan'  => $statusData['kelulusan'] ?? null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }
        });

        return redirect()->route('admin.siswa.index')
                         ->with('success', 'created');
    }

    public function show(string $id): Response
{
    $siswa = Siswa::with([
        'kelas' => function ($query) {
            $query->select('kelas.id', 'kelas.nama_kelas', 'kelas.jurusan', 'kelas.tingkat')
                  ->orderBy('kelas.nama_kelas');
        },
        'tahunAjaranStatus' => function ($query) {
            $query->select('tahun_ajaran.id', 'tahun_ajaran.tahun')
                  ->withPivot('status', 'kelulusan')
                  ->orderBy('tahun_ajaran.tahun');
        },
    ])->findOrFail($id);

    $siswa->kelas_detail = $siswa->kelas->map(function ($kelas) use ($siswa) {
        $tahunAjaranId = $kelas->pivot->tahun_ajaran_id;
        $tahunAjaran   = TahunAjaran::find($tahunAjaranId);
        $status        = $siswa->tahunAjaranStatus->where('id', $tahunAjaranId)->first();

        return [
            'id'              => $kelas->id,
            'nama_kelas'      => $kelas->nama_kelas,
            'jurusan'         => $kelas->jurusan,
            'tingkat'         => $kelas->tingkat,
            'tahun_ajaran_id' => $tahunAjaranId,
            'tahun_ajaran'    => $tahunAjaran ? $tahunAjaran->tahun : '-',
            'status'          => $status ? $status->pivot->status : 'Aktif',
            'kelulusan'       => $status ? $status->pivot->kelulusan : null,
        ];
    });

    // Tambahkan juga status-only rows (tahun ajaran yang tidak punya kelas)
    $siswa->status_detail = $siswa->tahunAjaranStatus->map(fn ($ta) => [
        'tahun_ajaran_id' => $ta->id,
        'tahun'           => $ta->tahun,
        'status'          => $ta->pivot->status,
        'kelulusan'       => $ta->pivot->kelulusan,
    ]);

    return Inertia::render('admin/siswa/Show', [
        'siswa' => $siswa,
    ]);
}

    public function edit(string $id): Response
    {
        $siswa = Siswa::with([
            'kelas' => function ($query) {
                $query->select('kelas.id', 'kelas.nama_kelas', 'kelas.jurusan', 'kelas.tingkat')
                      ->orderBy('kelas.nama_kelas');
            },
            'tahunAjaranStatus' => function ($query) {
                $query->select('tahun_ajaran.id', 'tahun_ajaran.tahun')
                      ->withPivot('status', 'kelulusan')
                      ->orderBy('tahun_ajaran.tahun');
            },
        ])->findOrFail($id);

        $kelasData = Kelas::orderBy('nama_kelas')->get()->map(fn ($k) => [
            'id'         => $k->id,
            'nama_kelas' => $k->nama_kelas,
            'jurusan'    => $k->jurusan,
            'tingkat'    => $k->tingkat,
        ]);

        $siswa->kelas_form_data = $siswa->kelas->map(fn ($k) => [
            'kelas_id'        => $k->id,
            'tahun_ajaran_id' => $k->pivot->tahun_ajaran_id,
        ])->toArray();

        $siswa->status_form_data = $siswa->tahunAjaranStatus->map(fn ($ta) => [
            'tahun_ajaran_id' => $ta->id,
            'status'          => $ta->pivot->status,
            'kelulusan'       => $ta->pivot->kelulusan,
        ])->toArray();

        if (empty($siswa->kelas_form_data)) {
            $siswa->kelas_form_data = [['kelas_id' => '', 'tahun_ajaran_id' => '']];
        }

        if (empty($siswa->status_form_data)) {
            $siswa->status_form_data = [['tahun_ajaran_id' => '', 'status' => 'Aktif', 'kelulusan' => null]];
        }

        return Inertia::render('admin/siswa/Edit', [
            'siswa'       => $siswa,
            'kelas'       => $kelasData,
            'tahunAjaran' => TahunAjaran::orderBy('tahun')->get(),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $siswa = Siswa::findOrFail($id);

        $validated = $request->validate([
            'nis'                                       => ['required', 'string', Rule::unique('siswa')->ignore($siswa->id)],
            'nama'                                      => 'required|string|max:255',
            'jenis_kelamin'                             => 'required|in:Laki-laki,Perempuan',
            'alamat'                                    => 'nullable|string|max:500',
            'angkatan'                                  => 'required|string|max:255',
            'foto'                                      => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'kelas_tahun_ajaran'                        => 'required|array|min:1',
            'kelas_tahun_ajaran.*.kelas_id'             => 'required|exists:kelas,id',
            'kelas_tahun_ajaran.*.tahun_ajaran_id'      => 'required|exists:tahun_ajaran,id',
            'status_tahun_ajaran'                       => 'required|array|min:1',
            'status_tahun_ajaran.*.tahun_ajaran_id'     => 'required|exists:tahun_ajaran,id',
            'status_tahun_ajaran.*.status'              => 'required|in:Aktif,Nonaktif,Pindah',
            'status_tahun_ajaran.*.kelulusan'           => 'nullable|in:Lulus,Tidak Lulus',
        ]);

        $fotoPath = $siswa->foto;
        if ($request->hasFile('foto')) {
            if ($siswa->foto) {
                Storage::disk('public')->delete($siswa->foto);
            }
            $fotoPath = $request->file('foto')->store('img/siswa', 'public');
        }

        $siswaData = [
            'nis'           => $validated['nis'],
            'nama'          => $validated['nama'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'alamat'        => $validated['alamat'],
            'angkatan'      => $validated['angkatan'],
            'foto'          => $fotoPath,
        ];

        DB::transaction(function () use ($siswa, $siswaData, $validated) {
            $siswa->update($siswaData);
            $siswa->kelas()->detach();

            foreach ($validated['kelas_tahun_ajaran'] as $combo) {
                $siswa->kelas()->attach($combo['kelas_id'], [
                    'tahun_ajaran_id' => $combo['tahun_ajaran_id'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }

            $tahunAjaranStatusData = [];
            foreach ($validated['status_tahun_ajaran'] as $statusData) {
                $tahunAjaranStatusData[$statusData['tahun_ajaran_id']] = [
                    'status'     => $statusData['status'],
                    'kelulusan'  => $statusData['kelulusan'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $siswa->tahunAjaranStatus()->sync($tahunAjaranStatusData);
        });

        return redirect()->route('admin.siswa.index')
                         ->with('success', 'updated');
    }

    public function destroy(string $id): RedirectResponse
    {
        $siswa = Siswa::findOrFail($id);

        DB::transaction(function () use ($siswa) {
            if ($siswa->foto) {
                Storage::disk('public')->delete($siswa->foto);
            }

            $siswa->kelas()->detach();
            $siswa->tahunAjaranStatus()->detach();
            $siswa->delete();
        });

        return redirect()->route('admin.siswa.index')
                         ->with('success', 'deleted');
    }

    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'status'          => 'required|in:Aktif,Nonaktif,Pindah',
            'kelulusan'       => 'nullable|in:Lulus,Tidak Lulus',
        ]);

        $siswa = Siswa::findOrFail($id);

        $siswa->tahunAjaranStatus()->updateExistingPivot($validated['tahun_ajaran_id'], [
            'status'     => $validated['status'],
            'kelulusan'  => $validated['kelulusan'] ?? null,
            'updated_at' => now(),
        ]);

        return redirect()->back()
                         ->with('success', 'updated');
    }
}
