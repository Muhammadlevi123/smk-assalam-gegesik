<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\Guru;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class MataPelajaranController extends Controller
{
    public function index(Request $request)
    {
        $query = MataPelajaran::with(['guru']);

        if ($request->filled('search')) {
            $s = $request->get('search');
            $query->where('nama', 'LIKE', "%{$s}%");
        }

        if ($request->filled('tahun_ajaran')) {
            $taId = TahunAjaran::where('tahun', $request->get('tahun_ajaran'))->first()?->id;
            if ($taId) {
                $query->whereHas('guru', fn ($q) => $q->where('pengajaran.tahun_ajaran_id', $taId));
            }
        }

        if ($request->filled('guru')) {
            $query->whereHas('guru', fn ($q) => $q->where('nama', 'LIKE', "%{$request->get('guru')}%"));
        }

        $mataPelajaran = $query->orderBy('nama')->paginate(10)->appends($request->query());

        $mataPelajaran->getCollection()->transform(function ($mapel) {
            $pengajar = [];
            foreach ($mapel->guru as $guru) {
                $tahunAjaran = TahunAjaran::find($guru->pivot->tahun_ajaran_id);
                if (!$tahunAjaran) continue;
                $pengajar[] = [
                    'guru_id'         => $guru->id,
                    'guru_nama'       => $guru->nama,
                    'guru_foto'       => $guru->foto,
                    'tahun_ajaran'    => $tahunAjaran->tahun,
                    'tahun_ajaran_id' => $tahunAjaran->id,
                    'tahun_sort'      => (int) explode('/', $tahunAjaran->tahun)[0],
                ];
            }
            usort($pengajar, fn ($a, $b) => $b['tahun_sort'] - $a['tahun_sort']);
            $mapel->pengajar        = $pengajar;
            $mapel->jumlah_pengajar = count($pengajar);
            return $mapel;
        });

        return Inertia::render('admin/mata-pelajaran/Index', [
            'mataPelajaran'   => $mataPelajaran,
            'filters'         => $request->only(['search', 'tahun_ajaran', 'guru']),
            'tahunAjaranList' => TahunAjaran::orderBy('tahun')->get()->map(fn ($t) => ['id' => $t->id, 'tahun' => $t->tahun]),
        ]);
    }

    public function create()
    {
        $tahunAjaran = TahunAjaran::orderBy('tahun')->get();

        $guru = Guru::with(['tahunAjaran' => fn ($q) => $q->select('tahun_ajaran.id', 'tahun_ajaran.tahun')])
            ->orderBy('nama')->get()
            ->map(function ($g) {
                $g->tahun_ajaran_tersedia = $g->tahunAjaran->pluck('id')->map(fn ($id) => (int) $id)->toArray();
                $g->existing_pengajaran_tahun_ajaran = DB::table('pengajaran')
                    ->where('guru_id', $g->id)
                    ->pluck('tahun_ajaran_id')
                    ->map(fn ($id) => (int) $id)
                    ->toArray();
                unset($g->tahunAjaran);
                return $g;
            });

        // ✅ Daftar nama mata pelajaran yang sudah ada — untuk combobox
        $existingNamaList = MataPelajaran::orderBy('nama')
            ->get()
            ->map(fn ($m) => ['value' => $m->nama, 'label' => $m->nama]);

        return Inertia::render('admin/mata-pelajaran/Create', [
            'tahunAjaran'      => $tahunAjaran,
            'guru'             => $guru,
            'existingNamaList' => $existingNamaList, // ✅ tambahan
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama'                         => 'required|string|max:255',
            'pengajaran'                   => 'nullable|array',
            'pengajaran.*.guru_id'         => 'required|exists:guru,id',
            'pengajaran.*.tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
        ]);

        if (!empty($validated['pengajaran'])) {
            $combinations = [];
            foreach ($validated['pengajaran'] as $index => $data) {
                $key = $data['guru_id'] . '-' . $data['tahun_ajaran_id'];
                if (in_array($key, $combinations)) {
                    return back()->withInput()
                        ->withErrors(["pengajaran.{$index}" => 'Ada kombinasi guru dan tahun ajaran yang duplikat.']);
                }
                $combinations[] = $key;
            }
        }

        DB::transaction(function () use ($validated) {
            $mapel = MataPelajaran::create(['nama' => $validated['nama']]);
            foreach ($validated['pengajaran'] ?? [] as $data) {
                $mapel->guru()->attach($data['guru_id'], [
                    'tahun_ajaran_id' => $data['tahun_ajaran_id'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        });

        return redirect()->route('admin.mata-pelajaran.index')->with('success', 'created');
    }

    public function show(string $id)
    {
        $mapel = MataPelajaran::with(['guru' => fn ($q) => $q->orderBy('nama')])->findOrFail($id);

        $pengajar = [];
        foreach ($mapel->guru as $guru) {
            $tahunAjaran = TahunAjaran::find($guru->pivot->tahun_ajaran_id);
            if (!$tahunAjaran) continue;
            $pengajar[] = [
                'guru_id'         => $guru->id,
                'guru_nama'       => $guru->nama,
                'guru_foto'       => $guru->foto,
                'tahun_ajaran'    => $tahunAjaran->tahun,
                'tahun_ajaran_id' => $tahunAjaran->id,
                'created_at'      => $guru->pivot->created_at,
                'updated_at'      => $guru->pivot->updated_at,
            ];
        }

        usort($pengajar, fn ($a, $b) => strcmp($b['tahun_ajaran'], $a['tahun_ajaran']));
        $mapel->pengajar        = $pengajar;
        $mapel->jumlah_pengajar = count($pengajar);

        return Inertia::render('admin/mata-pelajaran/Show', ['mataPelajaran' => $mapel]);
    }

    public function edit(string $id)
    {
        $mapel = MataPelajaran::with(['guru' => fn ($q) => $q->orderBy('nama')])->findOrFail($id);
        $tahunAjaran = TahunAjaran::orderBy('tahun')->get();

        $guru = Guru::with(['tahunAjaran' => fn ($q) => $q->select('tahun_ajaran.id', 'tahun_ajaran.tahun')])
            ->orderBy('nama')->get()
            ->map(function ($g) use ($id) {
                $g->tahun_ajaran_tersedia = $g->tahunAjaran->pluck('id')->map(fn ($v) => (int) $v)->toArray();
                $g->existing_pengajaran_tahun_ajaran = DB::table('pengajaran')
                    ->where('guru_id', $g->id)
                    ->where('mata_pelajaran_id', '!=', $id)
                    ->pluck('tahun_ajaran_id')
                    ->map(fn ($v) => (int) $v)
                    ->toArray();
                unset($g->tahunAjaran);
                return $g;
            });

        $mapel->pengajaran = $mapel->guru->map(fn ($g) => [
            'guru_id'         => (string) $g->id,
            'tahun_ajaran_id' => (string) $g->pivot->tahun_ajaran_id,
        ])->toArray();

        if (empty($mapel->pengajaran)) {
            $mapel->pengajaran = [['guru_id' => '', 'tahun_ajaran_id' => '']];
        }

        // ✅ Daftar nama mata pelajaran yang sudah ada (kecuali diri sendiri)
        $existingNamaList = MataPelajaran::where('id', '!=', $id)
            ->orderBy('nama')
            ->get()
            ->map(fn ($m) => ['value' => $m->nama, 'label' => $m->nama]);

        return Inertia::render('admin/mata-pelajaran/Edit', [
            'mataPelajaran'    => $mapel,
            'tahunAjaran'      => $tahunAjaran,
            'guru'             => $guru,
            'existingNamaList' => $existingNamaList, // ✅ tambahan
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $mapel = MataPelajaran::findOrFail($id);

        $validated = $request->validate([
            'nama'                         => 'required|string|max:255',
            'pengajaran'                   => 'nullable|array',
            'pengajaran.*.guru_id'         => 'required|exists:guru,id',
            'pengajaran.*.tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
        ]);

        if (!empty($validated['pengajaran'])) {
            $combinations = [];
            foreach ($validated['pengajaran'] as $index => $data) {
                $key = $data['guru_id'] . '-' . $data['tahun_ajaran_id'];
                if (in_array($key, $combinations)) {
                    return back()->withInput()
                        ->withErrors(["pengajaran.{$index}" => 'Ada kombinasi guru dan tahun ajaran yang duplikat.']);
                }
                $combinations[] = $key;
            }
        }

        DB::transaction(function () use ($mapel, $validated) {
            $mapel->update(['nama' => $validated['nama']]);
            $mapel->guru()->detach();
            foreach ($validated['pengajaran'] ?? [] as $data) {
                $mapel->guru()->attach($data['guru_id'], [
                    'tahun_ajaran_id' => $data['tahun_ajaran_id'],
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]);
            }
        });

        return redirect()->route('admin.mata-pelajaran.index')->with('success', 'updated');
    }

    public function destroy(string $id): RedirectResponse
    {
        $mapel            = MataPelajaran::findOrFail($id);
        $jumlahPengajaran = $mapel->guru()->count();

        if ($jumlahPengajaran > 0) {
            return back()->withErrors([
                'delete_error' => "Mata pelajaran {$mapel->nama} tidak dapat dihapus karena masih memiliki {$jumlahPengajaran} pengajaran aktif. Silakan hapus semua pengajaran terlebih dahulu.",
            ]);
        }

        try {
            DB::transaction(function () use ($mapel) {
                $mapel->guru()->detach();
                $mapel->delete();
            });
            return redirect()->route('admin.mata-pelajaran.index')->with('success', 'deleted');
        } catch (\Exception $e) {
            \Log::error('Error deleting mata pelajaran: ' . $e->getMessage());
            return back()->withErrors([
                'delete_error' => "Terjadi kesalahan saat menghapus mata pelajaran {$mapel->nama}. Silakan coba lagi.",
            ]);
        }
    }
}
