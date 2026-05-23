<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Alumni;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AlumniController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Alumni::with('siswa');

        // ── Search ────────────────────────────────────────────────
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('siswa', function ($sq) use ($search) {
                    $sq->where('nama', 'LIKE', "%{$search}%")
                       ->orWhere('nis', 'LIKE', "%{$search}%");
                })->orWhere('pekerjaan', 'LIKE', "%{$search}%");
            });
        }

        // ── Filter angkatan ───────────────────────────────────────
        if ($request->filled('angkatan')) {
            $query->whereHas('siswa', function ($q) use ($request) {
                $q->where('angkatan', $request->get('angkatan'));
            });
        }

        // ── Filter pekerjaan ──────────────────────────────────────
        if ($request->filled('pekerjaan')) {
            $query->where('pekerjaan', 'LIKE', '%' . $request->get('pekerjaan') . '%');
        }

        $alumni = $query->orderBy('created_at', 'desc')
                        ->paginate(10)
                        ->withQueryString();

        // ── Dropdown angkatan (hanya yang sudah punya alumni) ─────
        $angkatanList = DB::table('siswa')
            ->join('alumni', 'siswa.id', '=', 'alumni.siswa_id')
            ->select('siswa.angkatan')
            ->distinct()
            ->orderBy('siswa.angkatan', 'desc')
            ->pluck('siswa.angkatan')
            ->map(fn ($a) => ['value' => $a, 'label' => $a]);

        return Inertia::render('admin/alumni/Index', [
            'alumni'      => $alumni,
            'filters'     => $request->only(['search', 'angkatan', 'pekerjaan']),
            'angkatanList' => $angkatanList,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $siswaList = $this->buildSiswaList();

        return Inertia::render('admin/alumni/Create', [
            'siswaList' => $siswaList,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'siswa_id'        => [
                'required', 'integer', 'exists:siswa,id',
                Rule::unique('alumni', 'siswa_id'),
                function ($attribute, $value, $fail) {
                    $siswa = Siswa::find($value);
                    if ($siswa) {
                        $hasLulus = $siswa->tahunAjaranStatus()
                            ->where('siswa_tahun_ajaran.status', 'Lulus')
                            ->exists();
                        if (!$hasLulus) {
                            $fail('Hanya siswa dengan status "Lulus" yang dapat dijadikan alumni.');
                        }
                    }
                },
            ],
            'pekerjaan'       => 'required|string|max:255',
            'foto'            => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'alamat_sekarang' => 'nullable|string|max:500',
            'no_telepon'      => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'tahun_lulus'     => 'nullable|integer|min:1900|max:' . date('Y'),
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('img/alumni', 'public');
        }

        // Auto-set tahun_lulus dari tahun ajaran terakhir jika tidak diisi
        if (empty($validated['tahun_lulus'])) {
            $siswa  = Siswa::find($validated['siswa_id']);
            $latest = $siswa->tahunAjaranStatus()
                ->where('siswa_tahun_ajaran.status', 'Lulus')
                ->orderBy('siswa_tahun_ajaran.created_at', 'desc')
                ->first();

            if ($latest) {
                // Format tahun ajaran bisa "2023/2024" — ambil tahun kedua sebagai tahun lulus
                $parts = explode('/', $latest->tahun);
                $validated['tahun_lulus'] = (int) trim(end($parts));
            }
        }

        Alumni::create($validated);

        return redirect()->route('admin.alumni.index')
                         ->with('success', 'created');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): Response
    {
        $alumni = Alumni::with([
            'siswa',
            'siswa.tahunAjaranStatus' => fn ($q) => $q->where('siswa_tahun_ajaran.status', 'Lulus'),
        ])->findOrFail($id);

        return Inertia::render('admin/alumni/Show', [
            'alumni' => $alumni,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): Response
    {
        $alumni = Alumni::with('siswa')->findOrFail($id);

        $siswaList = $this->buildSiswaList($alumni->siswa_id);

        return Inertia::render('admin/alumni/Edit', [
            'alumni'    => $alumni,
            'siswaList' => $siswaList,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $alumni = Alumni::findOrFail($id);

        $validated = $request->validate([
            'siswa_id'        => [
                'required', 'integer', 'exists:siswa,id',
                Rule::unique('alumni', 'siswa_id')->ignore($alumni->id),
                function ($attribute, $value, $fail) {
                    $siswa = Siswa::find($value);
                    if ($siswa) {
                        $hasLulus = $siswa->tahunAjaranStatus()
                            ->where('siswa_tahun_ajaran.status', 'Lulus')
                            ->exists();
                        if (!$hasLulus) {
                            $fail('Hanya siswa dengan status "Lulus" yang dapat dijadikan alumni.');
                        }
                    }
                },
            ],
            'pekerjaan'       => 'required|string|max:255',
            'foto'            => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'alamat_sekarang' => 'nullable|string|max:500',
            'no_telepon'      => 'nullable|string|max:20',
            'email'           => 'nullable|email|max:255',
            'tahun_lulus'     => 'nullable|integer|min:1900|max:' . date('Y'),
        ]);

        if ($request->hasFile('foto')) {
            if ($alumni->foto) {
                Storage::disk('public')->delete($alumni->foto);
            }
            $validated['foto'] = $request->file('foto')->store('img/alumni', 'public');
        }

        $alumni->update($validated);

        return redirect()->route('admin.alumni.index')
                         ->with('success', 'updated');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $alumni = Alumni::findOrFail($id);

        if ($alumni->foto) {
            Storage::disk('public')->delete($alumni->foto);
        }

        $alumni->delete();

        return redirect()->route('admin.alumni.index')
                         ->with('success', 'deleted');
    }

    // ── Private helpers ───────────────────────────────────────────

    /**
     * Build siswa list untuk dropdown (create & edit).
     * $currentSiswaId: sertakan siswa yang sedang diedit meski sudah punya alumni.
     */
    private function buildSiswaList(?int $currentSiswaId = null)
    {
        return Siswa::where(function ($q) use ($currentSiswaId) {
                $q->whereDoesntHave('alumni');
                if ($currentSiswaId) {
                    $q->orWhere('id', $currentSiswaId);
                }
            })
            ->whereHas('tahunAjaranStatus', fn ($q) =>
                $q->where('siswa_tahun_ajaran.status', 'Lulus')
            )
            ->with(['tahunAjaranStatus' => fn ($q) =>
                $q->where('siswa_tahun_ajaran.status', 'Lulus')
                  ->orderBy('siswa_tahun_ajaran.created_at', 'desc')
            ])
            ->orderBy('nama')
            ->get()
            ->map(function ($siswa) {
                // Ambil tahun ajaran Lulus pertama (terbaru)
                $latest = $siswa->tahunAjaranStatus->first();

                // Hitung tahun lulus dari format "2023/2024" → ambil angka kedua
                $tahunLulusRaw  = $latest?->tahun;
                $tahunLulusInt  = null;
                if ($tahunLulusRaw) {
                    $parts         = explode('/', $tahunLulusRaw);
                    $tahunLulusInt = (int) trim(end($parts));
                }

                return [
                    'id'       => $siswa->id,
                    'nama'     => $siswa->nama,
                    'nis'      => $siswa->nis,
                    'angkatan' => $siswa->angkatan,
                    'label'    => "{$siswa->nama} ({$siswa->nis}) - Angkatan {$siswa->angkatan}",
                    'tahun_lulus' => $tahunLulusInt,
                    'tahun_ajaran_status' => $siswa->tahunAjaranStatus->map(fn ($ta) => [
                        'tahun_ajaran_id' => $ta->id,
                        'status'          => $ta->pivot->status,
                        'tahun_ajaran'    => ['id' => $ta->id, 'tahun' => $ta->tahun],
                    ]),
                ];
            });
    }
}
