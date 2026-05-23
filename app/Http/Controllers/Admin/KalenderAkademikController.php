<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KalenderAkademik;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class KalenderAkademikController extends Controller
{
    public function index(Request $request): Response
    {
        $tahunAktif = TahunAjaran::getAktif();

        $query = KalenderAkademik::with(['tahunAjaran']);

        if ($request->filled('search')) {
            $query->where('judul', 'LIKE', "%{$request->search}%");
        }

        $tahunAjaranId = $request->filled('tahun_ajaran_id')
            ? $request->tahun_ajaran_id
            : $tahunAktif?->id;

        if ($tahunAjaranId) {
            $query->where('tahun_ajaran_id', $tahunAjaranId);
        }

        $kalenderAkademik = $query->orderBy('tanggal_mulai', 'asc')
                                  ->paginate(10)
                                  ->withQueryString();

        $tahunAjaranList = TahunAjaran::select('id', 'tahun')
            ->orderBy('tahun', 'desc')
            ->get()
            ->map(fn ($t) => ['value' => $t->id, 'label' => $t->tahun]);

        return Inertia::render('admin/kalender-akademik/Index', [
            'kalenderAkademik' => $kalenderAkademik,
            'filters'          => [
                'search'          => $request->get('search', ''),
                'tahun_ajaran_id' => $tahunAjaranId ?? '',
            ],
            'tahunAjaranList'  => $tahunAjaranList,
            'tahunAktifId'     => $tahunAktif?->id,
        ]);
    }

    public function create(): Response
    {
        $tahunAjaranList = TahunAjaran::select('id', 'tahun')
            ->orderBy('tahun', 'desc')
            ->get()
            ->map(fn ($t) => ['value' => $t->id, 'label' => $t->tahun]);

        $tahunAktif = TahunAjaran::getAktif();

        return Inertia::render('admin/kalender-akademik/Create', [
            'tahunAjaranList' => $tahunAjaranList,
            'tahunAktifId'    => $tahunAktif?->id,
            // ✅ Tambah previous_url agar setelah store balik ke halaman yang sama (termasuk ?page=X&tahun_ajaran_id=X)
            'previous_url'    => url()->previous(route('admin.kalender-akademik.index')),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul'           => 'required|string|max:255',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            // ✅ Terima previous_url dari form
            'previous_url'    => 'nullable|string',
        ]);

        KalenderAkademik::create([
            'judul'           => $validated['judul'],
            'tanggal_mulai'   => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'tahun_ajaran_id' => $validated['tahun_ajaran_id'],
        ]);

        // ✅ Redirect ke previous_url (mempertahankan ?page=X&tahun_ajaran_id=X) atau fallback ke index
        $redirectTo = $validated['previous_url'] ?? route('admin.kalender-akademik.index');

        return redirect($redirectTo)->with('success', 'created');
    }

    public function show(KalenderAkademik $kalenderAkademik): Response
    {
        $kalenderAkademik->load('tahunAjaran');

        return Inertia::render('admin/kalender-akademik/Show', [
            'kalenderAkademik' => $kalenderAkademik,
        ]);
    }

    public function edit(KalenderAkademik $kalenderAkademik): Response
    {
        $kalenderAkademik->load('tahunAjaran');

        $tahunAjaranList = TahunAjaran::select('id', 'tahun')
            ->orderBy('tahun', 'desc')
            ->get()
            ->map(fn ($t) => ['value' => $t->id, 'label' => $t->tahun]);

        return Inertia::render('admin/kalender-akademik/Edit', [
            'kalenderAkademik' => $kalenderAkademik,
            'tahunAjaranList'  => $tahunAjaranList,
            // ✅ Tambah previous_url agar setelah update balik ke halaman yang sama (termasuk ?page=X&tahun_ajaran_id=X)
            'previous_url'     => url()->previous(route('admin.kalender-akademik.index')),
        ]);
    }

    public function update(Request $request, KalenderAkademik $kalenderAkademik): RedirectResponse
    {
        $validated = $request->validate([
            'judul'           => 'required|string|max:255',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            // ✅ Terima previous_url dari form
            'previous_url'    => 'nullable|string',
        ]);

        $kalenderAkademik->update([
            'judul'           => $validated['judul'],
            'tanggal_mulai'   => $validated['tanggal_mulai'],
            'tanggal_selesai' => $validated['tanggal_selesai'],
            'tahun_ajaran_id' => $validated['tahun_ajaran_id'],
        ]);

        // ✅ Redirect ke previous_url (mempertahankan ?page=X&tahun_ajaran_id=X) atau fallback ke index
        $redirectTo = $validated['previous_url'] ?? route('admin.kalender-akademik.index');

        return redirect($redirectTo)->with('success', 'updated');
    }

    public function destroy(KalenderAkademik $kalenderAkademik): RedirectResponse
    {
        $kalenderAkademik->delete();

        return redirect()->route('admin.kalender-akademik.index')
            ->with('success', 'deleted');
    }
}
