<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenagaKependidikan;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class TenagaKependidikanController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TenagaKependidikan::with(['tahunAjaran']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('jabatan', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('jenis_kelamin')) {
            $query->where('jenis_kelamin', $request->get('jenis_kelamin'));
        }

        if ($request->filled('jabatan')) {
            $query->where('jabatan', 'LIKE', '%' . $request->get('jabatan') . '%');
        }

        if ($request->filled('status')) {
            $query->whereExists(function ($subquery) use ($request) {
                $subquery->select(DB::raw(1))
                    ->from('tenaga_kependidikan_tahun_ajaran as tkta')
                    ->join('tahun_ajaran as ta', 'tkta.tahun_ajaran_id', '=', 'ta.id')
                    ->whereColumn('tkta.tenaga_kependidikan_id', 'tenaga_kependidikan.id')
                    ->where('tkta.status', $request->get('status'))
                    ->whereRaw('ta.id = (
                        SELECT tahun_ajaran_id
                        FROM tenaga_kependidikan_tahun_ajaran
                        JOIN tahun_ajaran ON tenaga_kependidikan_tahun_ajaran.tahun_ajaran_id = tahun_ajaran.id
                        WHERE tenaga_kependidikan_tahun_ajaran.tenaga_kependidikan_id = tenaga_kependidikan.id
                        ORDER BY CAST(SUBSTRING_INDEX(tahun_ajaran.tahun, "/", 1) AS UNSIGNED) DESC
                        LIMIT 1
                    )');
            });
        }

        if ($request->filled('tahun_ajaran')) {
            $query->whereHas('tahunAjaran', function ($q) use ($request) {
                $q->where('tahun', $request->get('tahun_ajaran'));
            });
        }

        $tenagaKependidikan = $query->orderBy('nama')->paginate(10)->withQueryString();

        $jabatanList = TenagaKependidikan::select('jabatan')
            ->distinct()->whereNotNull('jabatan')->where('jabatan', '!=', '')->orderBy('jabatan')
            ->pluck('jabatan')->map(fn ($j) => ['value' => $j, 'label' => $j]);

        return Inertia::render('admin/tenaga-kependidikan/Index', [
            'tenagaKependidikan' => $tenagaKependidikan,
            'filters'            => $request->only(['search', 'jabatan', 'jenis_kelamin', 'status', 'tahun_ajaran']),
            'tahunAjaranList'    => TahunAjaran::orderBy('tahun')->get()->map(fn ($t) => ['id' => $t->id, 'tahun' => $t->tahun]),
            'jabatanList'        => $jabatanList,
        ]);
    }

    public function create(): Response
    {
        // ✅ Kirim jabatanList untuk combobox
        $jabatanList = TenagaKependidikan::select('jabatan')
            ->distinct()->whereNotNull('jabatan')->where('jabatan', '!=', '')->orderBy('jabatan')
            ->pluck('jabatan')->map(fn ($j) => ['value' => $j, 'label' => $j]);

        return Inertia::render('admin/tenaga-kependidikan/Create', [
            'tahunAjaran' => TahunAjaran::orderBy('tahun')->get(),
            'jabatanList' => $jabatanList,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama'                                   => 'required|string|max:255',
            'jenis_kelamin'                          => 'required|in:Laki-laki,Perempuan',
            'jabatan'                                => 'required|string|max:255',
            'alamat'                                 => 'nullable|string|max:500',
            'foto'                                   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status_tahun_ajaran'                    => 'required|array|min:1',
            'status_tahun_ajaran.*.tahun_ajaran_id'  => 'required|exists:tahun_ajaran,id',
            'status_tahun_ajaran.*.status'           => 'required|in:Aktif,Nonaktif',
        ]);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('img/tenaga-kependidikan', 'public');
        }

        DB::transaction(function () use ($validated) {
            $tenaga = TenagaKependidikan::create([
                'nama'          => $validated['nama'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'jabatan'       => $validated['jabatan'],
                'alamat'        => $validated['alamat'] ?? null,
                'foto'          => $validated['foto'] ?? null,
            ]);

            foreach ($validated['status_tahun_ajaran'] as $statusData) {
                $tenaga->tahunAjaran()->syncWithoutDetaching([
                    $statusData['tahun_ajaran_id'] => [
                        'status'     => $statusData['status'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }
        });

        return redirect()->route('admin.tenaga-kependidikan.index')->with('success', 'created');
    }

    public function show(string $id): Response
    {
        $tenaga = TenagaKependidikan::findOrFail($id);

        $statusTahunAjaran = DB::table('tenaga_kependidikan_tahun_ajaran as tkta')
            ->join('tahun_ajaran as ta', 'tkta.tahun_ajaran_id', '=', 'ta.id')
            ->where('tkta.tenaga_kependidikan_id', $tenaga->id)
            ->select('ta.id', 'ta.tahun', 'tkta.status')
            ->orderBy('ta.tahun')->get()
            ->map(fn ($row) => ['id' => $row->id, 'tahun' => $row->tahun, 'status' => $row->status])
            ->toArray();

        return Inertia::render('admin/tenaga-kependidikan/Show', [
            'tenagaKependidikan' => array_merge($tenaga->toArray(), [
                'status_tahun_ajaran' => $statusTahunAjaran,
            ]),
        ]);
    }

    public function edit(string $id): Response
    {
        $tenaga      = TenagaKependidikan::with(['tahunAjaran'])->findOrFail($id);
        $tahunAjaran = TahunAjaran::orderBy('tahun')->get();

        $tenaga->status_form_data = $tenaga->tahunAjaran->map(fn ($ta) => [
            'tahun_ajaran_id' => $ta->id,
            'status'          => $ta->pivot->status,
        ])->toArray();

        if (empty($tenaga->status_form_data)) {
            $tenaga->status_form_data = [['tahun_ajaran_id' => '', 'status' => 'Aktif']];
        }

        // ✅ Kirim jabatanList untuk combobox
        $jabatanList = TenagaKependidikan::select('jabatan')
            ->distinct()->whereNotNull('jabatan')->where('jabatan', '!=', '')->orderBy('jabatan')
            ->pluck('jabatan')->map(fn ($j) => ['value' => $j, 'label' => $j]);

        return Inertia::render('admin/tenaga-kependidikan/Edit', [
            'tenagaKependidikan' => $tenaga,
            'tahunAjaran'        => $tahunAjaran,
            'jabatanList'        => $jabatanList,
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $tenaga = TenagaKependidikan::findOrFail($id);

        $validated = $request->validate([
            'nama'                                   => 'required|string|max:255',
            'jenis_kelamin'                          => 'required|in:Laki-laki,Perempuan',
            'jabatan'                                => 'required|string|max:255',
            'alamat'                                 => 'nullable|string|max:500',
            'foto'                                   => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status_tahun_ajaran'                    => 'required|array|min:1',
            'status_tahun_ajaran.*.tahun_ajaran_id'  => 'required|exists:tahun_ajaran,id',
            'status_tahun_ajaran.*.status'           => 'required|in:Aktif,Nonaktif',
        ]);

        $tahunAjaranIds = array_column($validated['status_tahun_ajaran'], 'tahun_ajaran_id');
        if (count($tahunAjaranIds) !== count(array_unique($tahunAjaranIds))) {
            return redirect()->back()->withInput()
                ->withErrors(['status_tahun_ajaran' => 'Ada tahun ajaran yang duplikat dalam pengaturan status.']);
        }

        $fotoPath = $tenaga->foto;
        if ($request->hasFile('foto')) {
            if ($tenaga->foto) Storage::disk('public')->delete($tenaga->foto);
            $fotoPath = $request->file('foto')->store('img/tenaga-kependidikan', 'public');
        } elseif ($request->input('hapus_foto') == '1') {
            if ($tenaga->foto) Storage::disk('public')->delete($tenaga->foto);
            $fotoPath = null;
        }

        DB::transaction(function () use ($tenaga, $validated, $fotoPath) {
            $tenaga->update([
                'nama'          => $validated['nama'],
                'jenis_kelamin' => $validated['jenis_kelamin'],
                'jabatan'       => $validated['jabatan'],
                'alamat'        => $validated['alamat'] ?? null,
                'foto'          => $fotoPath,
            ]);

            $syncData = [];
            foreach ($validated['status_tahun_ajaran'] as $statusData) {
                $syncData[$statusData['tahun_ajaran_id']] = [
                    'status'     => $statusData['status'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $tenaga->tahunAjaran()->sync($syncData);
        });

        return redirect()->route('admin.tenaga-kependidikan.index')->with('success', 'updated');
    }

    public function destroy(string $id): RedirectResponse
    {
        $tenaga = TenagaKependidikan::findOrFail($id);

        DB::transaction(function () use ($tenaga) {
            if ($tenaga->foto) Storage::disk('public')->delete($tenaga->foto);
            $tenaga->tahunAjaran()->detach();
            $tenaga->delete();
        });

        return redirect()->route('admin.tenaga-kependidikan.index')->with('success', 'deleted');
    }

    public function updateStatus(Request $request, string $id): RedirectResponse
    {
        $validated = $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'status'          => 'required|in:Aktif,Nonaktif',
        ]);

        $tenaga = TenagaKependidikan::findOrFail($id);
        $tenaga->tahunAjaran()->updateExistingPivot($validated['tahun_ajaran_id'], [
            'status'     => $validated['status'],
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'updated');
    }
}
