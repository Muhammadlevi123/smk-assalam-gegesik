<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\Guru;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $query = Kelas::with([
            'waliKelas' => fn ($q) => $q->select('guru.id', 'guru.nama'),
        ])->withCount('siswa');

        if ($request->filled('search')) {
            $s = $request->get('search');
            $query->where(fn ($q) => $q
                ->where('nama_kelas', 'LIKE', "%{$s}%")
                ->orWhere('jurusan',   'LIKE', "%{$s}%")
                ->orWhere('tingkat',   'LIKE', "%{$s}%"));
        }

        if ($request->filled('jurusan'))     $query->where('jurusan', $request->get('jurusan'));
        if ($request->filled('tingkat'))     $query->where('tingkat', $request->get('tingkat'));

        if ($request->filled('tahun_ajaran')) {
            $taId = TahunAjaran::where('tahun', $request->get('tahun_ajaran'))->first()?->id;
            if ($taId) {
                $query->whereHas('waliKelas', fn ($q) => $q->where('wali_kelas.tahun_ajaran_id', $taId));
            }
        }

        if ($request->filled('wali_kelas')) {
            $query->whereHas('waliKelas', fn ($q) => $q->where('nama', 'LIKE', "%{$request->get('wali_kelas')}%"));
        }

        $kelas = $query->orderBy('nama_kelas')->paginate(10)->appends($request->query());

        $kelas->getCollection()->transform(function ($item) {
            $item->wali_kelas_terkini = DB::table('guru')
                ->join('wali_kelas', 'guru.id', '=', 'wali_kelas.guru_id')
                ->join('tahun_ajaran', 'wali_kelas.tahun_ajaran_id', '=', 'tahun_ajaran.id')
                ->where('wali_kelas.kelas_id', $item->id)
                ->select('guru.nama', 'tahun_ajaran.tahun', 'wali_kelas.tahun_ajaran_id')
                ->orderByRaw('CAST(SUBSTRING_INDEX(tahun_ajaran.tahun, "/", 1) AS UNSIGNED) DESC')
                ->first();

            $item->jumlah_siswa = $item->siswa_count;

            $fromWali = DB::table('wali_kelas')
                ->join('tahun_ajaran', 'wali_kelas.tahun_ajaran_id', '=', 'tahun_ajaran.id')
                ->where('wali_kelas.kelas_id', $item->id)->pluck('tahun_ajaran.tahun')->unique();

            $fromSiswa = DB::table('siswa_kelas')
                ->join('tahun_ajaran', 'siswa_kelas.tahun_ajaran_id', '=', 'tahun_ajaran.id')
                ->where('siswa_kelas.kelas_id', $item->id)->pluck('tahun_ajaran.tahun')->unique();

            $item->tahun_ajaran_terkait = $fromWali->merge($fromSiswa)->unique()->sort()->values();
            $item->total_wali_history   = $fromWali->count();

            return $item;
        });

        return Inertia::render('admin/kelas/Index', [
            'kelas'           => $kelas,
            'filters'         => $request->only(['search', 'jurusan', 'tingkat', 'tahun_ajaran', 'wali_kelas']),
            'jurusanList'     => Kelas::select('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan')->map(fn ($j) => ['value' => $j, 'label' => $j]),
            'tingkatList'     => Kelas::select('tingkat')->distinct()->orderBy('tingkat')->pluck('tingkat')->map(fn ($t) => ['value' => $t, 'label' => $t]),
            'tahunAjaranList' => TahunAjaran::orderBy('tahun')->get()->map(fn ($t) => ['id' => $t->id, 'tahun' => $t->tahun]),
        ]);
    }

    public function create()
    {
        $tahunAjaran = TahunAjaran::orderBy('tahun')->get();

        $guru = Guru::with(['tahunAjaran' => fn ($q) => $q->select('tahun_ajaran.id', 'tahun_ajaran.tahun')])
            ->orderBy('nama')->get()
            ->map(function ($g) {
                $g->tahun_ajaran_tersedia     = $g->tahunAjaran->pluck('id')->map(fn ($id) => (int) $id)->toArray();
                $g->existing_wali_tahun_ajaran = DB::table('wali_kelas')->where('guru_id', $g->id)
                    ->pluck('tahun_ajaran_id')->map(fn ($id) => (int) $id)->toArray();
                unset($g->tahunAjaran);
                return $g;
            });

        $siswa = Siswa::with(['tahunAjaranStatus' => fn ($q) => $q->select('tahun_ajaran.id', 'tahun_ajaran.tahun')])
            ->orderBy('nama')->get()
            ->map(function ($s) {
                $s->tahun_ajaran_aktif = $s->tahunAjaranStatus
                    ->filter(fn ($ta) => $ta->pivot->status === 'Aktif')
                    ->pluck('id')->map(fn ($id) => (int) $id)->toArray();

                $s->existing_kelas_tahun = DB::table('siswa_kelas')->where('siswa_id', $s->id)
                    ->get(['kelas_id', 'tahun_ajaran_id'])
                    ->map(fn ($r) => ['kelas_id' => (int) $r->kelas_id, 'tahun_ajaran_id' => (int) $r->tahun_ajaran_id])
                    ->toArray();

                unset($s->tahunAjaranStatus);
                return $s;
            });

        return Inertia::render('admin/kelas/Create', [
            'tahunAjaran' => $tahunAjaran,
            'guru'        => $guru,
            'siswa'       => $siswa,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_kelas'                          => 'required|string|max:255',
            'jurusan'                             => 'required|string|max:255',
            'tingkat'                             => 'required|string|max:255',
            'wali_kelas'                          => 'nullable|array',
            'wali_kelas.*.guru_id'                => 'required_with:wali_kelas|exists:guru,id',
            'wali_kelas.*.tahun_ajaran_id'        => 'required_with:wali_kelas|exists:tahun_ajaran,id',
            'siswa_kelas'                         => 'nullable|array',
            'siswa_kelas.*.siswa_id'              => 'required_with:siswa_kelas|exists:siswa,id',
            'siswa_kelas.*.tahun_ajaran_id'       => 'required_with:siswa_kelas|exists:tahun_ajaran,id',
        ]);

        if (Kelas::where('nama_kelas', $validated['nama_kelas'])->exists()) {
            return back()->withErrors(['nama_kelas' => 'Nama kelas sudah ada.'])->withInput();
        }

        foreach ($validated['wali_kelas'] ?? [] as $d) {
            if (DB::table('wali_kelas')->where('guru_id', $d['guru_id'])->where('tahun_ajaran_id', $d['tahun_ajaran_id'])->exists()) {
                $g  = Guru::find($d['guru_id']);
                $ta = TahunAjaran::find($d['tahun_ajaran_id']);
                return back()->withErrors(['wali_kelas' => "Guru {$g->nama} sudah menjadi wali kelas lain pada tahun ajaran {$ta->tahun}"])->withInput();
            }
        }

        foreach ($validated['siswa_kelas'] ?? [] as $d) {
            if (DB::table('siswa_kelas')->where('siswa_id', $d['siswa_id'])->where('tahun_ajaran_id', $d['tahun_ajaran_id'])->exists()) {
                $s  = Siswa::find($d['siswa_id']);
                $ta = TahunAjaran::find($d['tahun_ajaran_id']);
                return back()->withErrors(['siswa_kelas' => "Siswa {$s->nama} sudah terdaftar di kelas lain pada tahun ajaran {$ta->tahun}"])->withInput();
            }
        }

        DB::transaction(function () use ($validated) {
            $kelas = Kelas::create([
                'nama_kelas' => $validated['nama_kelas'],
                'jurusan'    => $validated['jurusan'],
                'tingkat'    => $validated['tingkat'],
            ]);

            foreach ($validated['wali_kelas'] ?? [] as $d) {
                $kelas->waliKelas()->attach($d['guru_id'], [
                    'tahun_ajaran_id' => $d['tahun_ajaran_id'],
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            foreach ($validated['siswa_kelas'] ?? [] as $d) {
                $kelas->siswa()->attach($d['siswa_id'], [
                    'tahun_ajaran_id' => $d['tahun_ajaran_id'],
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('admin.kelas.index')->with('success', 'created');
    }

    public function show(string $id)
    {
        $kelas = Kelas::with([
            'waliKelas' => fn ($q) => $q->select('guru.id', 'guru.nama')->orderBy('guru.nama'),
            'siswa'     => fn ($q) => $q->select('siswa.id', 'siswa.nis', 'siswa.nama', 'siswa.jenis_kelamin', 'siswa.foto')->orderBy('siswa.nama'),
        ])->findOrFail($id);

        $kelas->wali_kelas_detail = $kelas->waliKelas->map(fn ($w) => [
            'id' => $w->id, 'nama' => $w->nama,
            'tahun_ajaran_id' => $w->pivot->tahun_ajaran_id,
            'tahun_ajaran'    => TahunAjaran::find($w->pivot->tahun_ajaran_id)?->tahun ?? '-',
        ]);

        $kelas->siswa_detail = $kelas->siswa->map(function ($s) {
            $st = DB::table('siswa_tahun_ajaran')
                ->join('tahun_ajaran', 'siswa_tahun_ajaran.tahun_ajaran_id', '=', 'tahun_ajaran.id')
                ->where('siswa_tahun_ajaran.siswa_id', $s->id)
                ->select('siswa_tahun_ajaran.status', 'tahun_ajaran.tahun')
                ->orderByRaw('CAST(SUBSTRING_INDEX(tahun_ajaran.tahun, "/", 1) AS UNSIGNED) DESC')
                ->first();
            return [
                'id' => $s->id, 'nis' => $s->nis, 'nama' => $s->nama,
                'jenis_kelamin' => $s->jenis_kelamin, 'foto' => $s->foto,
                'tahun_ajaran_id' => $s->pivot->tahun_ajaran_id,
                'status_terkini'  => $st?->status ?? 'Aktif',
                'tahun_ajaran'    => TahunAjaran::find($s->pivot->tahun_ajaran_id)?->tahun ?? '-',
            ];
        });

        $kelas->statistik = [
            'total_siswa'      => $kelas->siswa->count(),
            'total_wali_kelas' => $kelas->waliKelas->count(),
            'siswa_aktif'      => $kelas->siswa_detail->where('status_terkini', 'Aktif')->count(),
            'siswa_nonaktif'   => $kelas->siswa_detail->where('status_terkini', 'Nonaktif')->count(),
        ];

        $fromWali  = DB::table('wali_kelas')->join('tahun_ajaran', 'wali_kelas.tahun_ajaran_id', '=', 'tahun_ajaran.id')->where('wali_kelas.kelas_id', $id)->select('tahun_ajaran.id', 'tahun_ajaran.tahun')->get();
        $fromSiswa = DB::table('siswa_kelas')->join('tahun_ajaran', 'siswa_kelas.tahun_ajaran_id', '=', 'tahun_ajaran.id')->where('siswa_kelas.kelas_id', $id)->select('tahun_ajaran.id', 'tahun_ajaran.tahun')->distinct()->get();
        $kelas->tahun_ajaran_terkait = $fromWali->merge($fromSiswa)->unique('id')->sortBy('tahun')->values();

        return Inertia::render('admin/kelas/Show', ['kelas' => $kelas]);
    }

    public function edit(string $id)
    {
        $kelas = Kelas::with([
            'waliKelas' => fn ($q) => $q->select('guru.id', 'guru.nama')->orderBy('guru.nama'),
            'siswa'     => fn ($q) => $q->select('siswa.id', 'siswa.nis', 'siswa.nama', 'siswa.jenis_kelamin', 'siswa.foto')->orderBy('siswa.nama'),
        ])->findOrFail($id);

        $tahunAjaran = TahunAjaran::orderBy('tahun')->get();

        $guru = Guru::with(['tahunAjaran' => fn ($q) => $q->select('tahun_ajaran.id', 'tahun_ajaran.tahun')])
            ->orderBy('nama')->get()
            ->map(function ($g) use ($id) {
                $g->tahun_ajaran_tersedia     = $g->tahunAjaran->pluck('id')->map(fn ($v) => (int) $v)->toArray();
                $g->existing_wali_tahun_ajaran = DB::table('wali_kelas')
                    ->where('guru_id', $g->id)->where('kelas_id', '!=', $id)
                    ->pluck('tahun_ajaran_id')->map(fn ($v) => (int) $v)->toArray();
                unset($g->tahunAjaran);
                return $g;
            });

        $siswa = Siswa::with(['tahunAjaranStatus' => fn ($q) => $q->select('tahun_ajaran.id', 'tahun_ajaran.tahun')])
            ->orderBy('nama')->get()
            ->map(function ($s) use ($id) {
                $s->tahun_ajaran_aktif = $s->tahunAjaranStatus
                    ->filter(fn ($ta) => $ta->pivot->status === 'Aktif')
                    ->pluck('id')->map(fn ($v) => (int) $v)->toArray();

                $s->existing_kelas_tahun = DB::table('siswa_kelas')
                    ->where('siswa_id', $s->id)->where('kelas_id', '!=', $id)
                    ->get(['kelas_id', 'tahun_ajaran_id'])
                    ->map(fn ($r) => ['kelas_id' => (int) $r->kelas_id, 'tahun_ajaran_id' => (int) $r->tahun_ajaran_id])
                    ->toArray();

                unset($s->tahunAjaranStatus);
                return $s;
            });

        $kelas->wali_kelas_form = $kelas->waliKelas->map(fn ($w) => [
            'guru_id' => (string) $w->id,
            'tahun_ajaran_id' => (string) $w->pivot->tahun_ajaran_id,
        ])->toArray();

        if (empty($kelas->wali_kelas_form)) {
            $kelas->wali_kelas_form = [['guru_id' => '', 'tahun_ajaran_id' => '']];
        }

        $kelas->siswa_kelas_form = $kelas->siswa->map(fn ($s) => [
            'siswa_id' => (string) $s->id,
            'tahun_ajaran_id' => (string) $s->pivot->tahun_ajaran_id,
        ])->toArray();

        if (empty($kelas->siswa_kelas_form)) {
            $kelas->siswa_kelas_form = [['siswa_id' => '', 'tahun_ajaran_id' => '']];
        }

        $jurusanList = Kelas::select('jurusan')->distinct()->orderBy('jurusan')->pluck('jurusan')
            ->map(fn ($j) => ['nama' => $j]);

        return Inertia::render('admin/kelas/Edit', [
            'kelas'       => $kelas,
            'tahunAjaran' => $tahunAjaran,
            'guru'        => $guru,
            'siswa'       => $siswa,
            'jurusanList' => $jurusanList,
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $kelas = Kelas::findOrFail($id);

        $validated = $request->validate([
            'nama_kelas'                          => ['required', 'string', 'max:255', Rule::unique('kelas')->ignore($kelas->id)],
            'jurusan'                             => 'required|string|max:255',
            'tingkat'                             => 'required|string|max:255',
            'wali_kelas'                          => 'nullable|array',
            'wali_kelas.*.guru_id'                => 'required_with:wali_kelas|exists:guru,id',
            'wali_kelas.*.tahun_ajaran_id'        => 'required_with:wali_kelas|exists:tahun_ajaran,id',
            'siswa_kelas'                         => 'nullable|array',
            'siswa_kelas.*.siswa_id'              => 'required_with:siswa_kelas|exists:siswa,id',
            'siswa_kelas.*.tahun_ajaran_id'       => 'required_with:siswa_kelas|exists:tahun_ajaran,id',
        ]);

        foreach ($validated['wali_kelas'] ?? [] as $d) {
            if (DB::table('wali_kelas')->where('guru_id', $d['guru_id'])->where('tahun_ajaran_id', $d['tahun_ajaran_id'])->where('kelas_id', '!=', $kelas->id)->exists()) {
                $g  = Guru::find($d['guru_id']);
                $ta = TahunAjaran::find($d['tahun_ajaran_id']);
                return back()->withErrors(['wali_kelas' => "Guru {$g->nama} sudah menjadi wali kelas lain pada tahun ajaran {$ta->tahun}"])->withInput();
            }
        }

        foreach ($validated['siswa_kelas'] ?? [] as $d) {
            if (DB::table('siswa_kelas')->where('siswa_id', $d['siswa_id'])->where('tahun_ajaran_id', $d['tahun_ajaran_id'])->where('kelas_id', '!=', $kelas->id)->exists()) {
                $s  = Siswa::find($d['siswa_id']);
                $ta = TahunAjaran::find($d['tahun_ajaran_id']);
                return back()->withErrors(['siswa_kelas' => "Siswa {$s->nama} sudah terdaftar di kelas lain pada tahun ajaran {$ta->tahun}"])->withInput();
            }
        }

        DB::transaction(function () use ($kelas, $validated) {
            $kelas->update([
                'nama_kelas' => $validated['nama_kelas'],
                'jurusan'    => $validated['jurusan'],
                'tingkat'    => $validated['tingkat'],
            ]);

            $kelas->waliKelas()->detach();
            foreach ($validated['wali_kelas'] ?? [] as $d) {
                $kelas->waliKelas()->attach($d['guru_id'], [
                    'tahun_ajaran_id' => $d['tahun_ajaran_id'],
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }

            $kelas->siswa()->detach();
            foreach ($validated['siswa_kelas'] ?? [] as $d) {
                $kelas->siswa()->attach($d['siswa_id'], [
                    'tahun_ajaran_id' => $d['tahun_ajaran_id'],
                    'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        });

        return redirect()->route('admin.kelas.index')->with('success', 'updated');
    }

    public function destroy(string $id): RedirectResponse
    {
        $kelas       = Kelas::findOrFail($id);
        $jumlahSiswa = $kelas->siswa()->count();

        if ($jumlahSiswa > 0) {
            return back()->withErrors([
                'delete_error' => "Kelas {$kelas->nama_kelas} tidak dapat dihapus karena masih memiliki {$jumlahSiswa} siswa. Silakan pindahkan atau hapus semua siswa terlebih dahulu.",
            ]);
        }

        try {
            DB::transaction(function () use ($kelas) {
                $kelas->waliKelas()->detach();
                $kelas->siswa()->detach();
                $kelas->delete();
            });
            return redirect()->route('admin.kelas.index')->with('success', 'deleted');
        } catch (\Exception $e) {
            \Log::error('Error deleting kelas: ' . $e->getMessage());
            return back()->withErrors(['delete_error' => "Terjadi kesalahan saat menghapus kelas {$kelas->nama_kelas}. Silakan coba lagi."]);
        }
    }

    public function getWaliKelasHistory(string $id)
    {
        Kelas::findOrFail($id);
        $history = DB::table('wali_kelas')
            ->join('guru', 'wali_kelas.guru_id', '=', 'guru.id')
            ->join('tahun_ajaran', 'wali_kelas.tahun_ajaran_id', '=', 'tahun_ajaran.id')
            ->where('wali_kelas.kelas_id', $id)
            ->select('guru.id as guru_id', 'guru.nama as guru_nama', 'tahun_ajaran.id as tahun_ajaran_id', 'tahun_ajaran.tahun as tahun_ajaran', 'wali_kelas.created_at', 'wali_kelas.updated_at')
            ->orderBy('tahun_ajaran.tahun', 'desc')->get();

        return response()->json(['success' => true, 'data' => $history]);
    }
}
