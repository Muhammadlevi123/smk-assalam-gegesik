<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class TahunAjaranController extends Controller
{
    public function index(Request $request): Response
    {
        $query = TahunAjaran::withCount([
            'guru',
            'siswaStatus',
            'siswaKelas',
            'pengajaran',
            'tenagaKependidikan',
            'waliKelas',
            'kalenderAkademik'
        ]);

        if ($request->filled('search')) {
            $query->where('tahun', 'like', "%{$request->search}%");
        }

        $tahunAjaran = $query->latest('tanggal_mulai')
                             ->paginate(10)
                             ->withQueryString();

        return Inertia::render('admin/tahun-ajaran/Index', [
            'tahunAjaran' => $tahunAjaran,
            'filters'     => $request->only(['search']),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('admin/tahun-ajaran/Create', [
            'tahunBerikutnya' => TahunAjaran::generateTahunBerikutnya(),
            'previous_url'    => url()->previous(route('admin.tahun-ajaran.index')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tahun' => [
                'required',
                'string',
                'max:20',
                'regex:/^\d{4}\/\d{4}$/',
                'unique:tahun_ajaran,tahun',
            ],
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'previous_url'    => 'nullable|string',
        ]);

        TahunAjaran::create([
            'tahun'           => $validated['tahun'],
            'tanggal_mulai'   => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
        ]);

        $redirectTo = $validated['previous_url'] ?? route('admin.tahun-ajaran.index');

        return redirect($redirectTo)->with('success', 'created');
    }

    public function edit(TahunAjaran $tahunAjaran, Request $request): Response
    {
        return Inertia::render('admin/tahun-ajaran/Edit', [
            'tahunAjaran'  => $tahunAjaran,
            'previous_url' => url()->previous(route('admin.tahun-ajaran.index')),
        ]);
    }

    public function update(Request $request, TahunAjaran $tahunAjaran): RedirectResponse
    {
        $validated = $request->validate([
            'tahun' => [
                'required',
                'string',
                'max:20',
                'regex:/^\d{4}\/\d{4}$/',
                Rule::unique('tahun_ajaran')->ignore($tahunAjaran->id),
            ],
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'previous_url'    => 'nullable|string',
        ]);

        $tahunAjaran->update([
            'tahun'           => $validated['tahun'],
            'tanggal_mulai'   => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
        ]);

        $redirectTo = $validated['previous_url'] ?? route('admin.tahun-ajaran.index');

        return redirect($redirectTo)->with('success', 'updated');
    }

    public function destroy(TahunAjaran $tahunAjaran): RedirectResponse
    {
        // ✅ PERBAIKAN: cek status berdasarkan tanggal aktual, bukan is_aktif
        // Sama seperti getStatus() di frontend:
        //   today >= mulai DAN today <= selesai  →  "berjalan" → TIDAK BOLEH DIHAPUS
        //   today < mulai                        →  "akan-datang" → BOLEH DIHAPUS
        //   today > selesai                      →  "selesai" → BOLEH DIHAPUS
        $today   = Carbon::today();
        $mulai   = Carbon::parse($tahunAjaran->tanggal_mulai)->startOfDay();
        $selesai = Carbon::parse($tahunAjaran->tanggal_selesai)->endOfDay();

        $sedangBerjalan = $today->between($mulai, $selesai);

        if ($sedangBerjalan) {
            return redirect()->route('admin.tahun-ajaran.index')
                ->with('error', 'Tidak dapat menghapus tahun ajaran yang sedang berjalan.');
        }

        $hasData = $tahunAjaran->guru()->count() > 0
            || $tahunAjaran->siswaStatus()->count() > 0
            || $tahunAjaran->siswaKelas()->count() > 0
            || $tahunAjaran->pengajaran()->count() > 0
            || $tahunAjaran->tenagaKependidikan()->count() > 0
            || $tahunAjaran->waliKelas()->count() > 0
            || $tahunAjaran->kalenderAkademik()->count() > 0;

        if ($hasData) {
            return redirect()->route('admin.tahun-ajaran.index')
                ->with('error', 'Tidak dapat menghapus tahun ajaran yang masih memiliki data terkait.');
        }

        $tahunAjaran->delete();

        return redirect()->route('admin.tahun-ajaran.index')
                         ->with('success', 'deleted');
    }
}
