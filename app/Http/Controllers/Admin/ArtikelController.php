<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class ArtikelController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Artikel::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'LIKE', "%{$search}%")
                  ->orWhere('isi', 'LIKE', "%{$search}%")
                  ->orWhere('kategori', 'LIKE', "%{$search}%")
                  ->orWhere('penulis', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->get('kategori'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('penulis')) {
            $query->where('penulis', 'LIKE', '%' . $request->get('penulis') . '%');
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_publikasi', $request->get('tahun'));
        }

        $artikelRaw = $query->orderBy('tanggal_publikasi', 'desc')
                            ->orderBy('created_at', 'desc')
                            ->paginate(10)
                            ->appends($request->query());

        $artikelRaw->getCollection()->transform(function ($artikel) {
            $artikel->tanggal_formatted = $artikel->tanggal_publikasi
                ? Carbon::parse($artikel->tanggal_publikasi)->translatedFormat('d M Y')
                : null;
            $artikel->tahun = $artikel->tanggal_publikasi
                ? Carbon::parse($artikel->tanggal_publikasi)->year
                : null;
            return $artikel;
        });

        $kategoriList = Artikel::select('kategori')
            ->distinct()->whereNotNull('kategori')->where('kategori', '!=', '')->orderBy('kategori')
            ->get()->map(fn ($item) => ['value' => $item->kategori, 'label' => $item->kategori]);

        $penulisList = Artikel::select('penulis')
            ->distinct()->whereNotNull('penulis')->where('penulis', '!=', '')->orderBy('penulis')
            ->get()->map(fn ($item) => ['value' => $item->penulis, 'label' => $item->penulis]);

        $tahunList = Artikel::selectRaw('YEAR(tanggal_publikasi) as tahun')
            ->distinct()->whereNotNull('tanggal_publikasi')->orderBy('tahun', 'desc')
            ->get()->map(fn ($item) => ['value' => $item->tahun, 'label' => $item->tahun]);

        $statusList = [
            ['value' => 'draft',   'label' => 'Draft'],
            ['value' => 'publish', 'label' => 'Publish'],
        ];

        return Inertia::render('admin/artikel/Index', [
            'artikel'      => $artikelRaw,
            'filters'      => $request->only(['search', 'kategori', 'status', 'penulis', 'tahun']),
            'kategoriList' => $kategoriList,
            'penulisList'  => $penulisList,
            'tahunList'    => $tahunList,
            'statusList'   => $statusList,
        ]);
    }

    public function create(): Response
    {
        $kategoriList = Artikel::select('kategori')
            ->distinct()->whereNotNull('kategori')->where('kategori', '!=', '')->orderBy('kategori')
            ->get()->map(fn ($item) => ['value' => $item->kategori, 'label' => $item->kategori]);

        $penulisList = Artikel::select('penulis')
            ->distinct()->whereNotNull('penulis')->where('penulis', '!=', '')->orderBy('penulis')
            ->get()->map(fn ($item) => ['value' => $item->penulis, 'label' => $item->penulis]);

        $statusOptions = [
            ['value' => 'draft',   'label' => 'Draft'],
            ['value' => 'publish', 'label' => 'Publish'],
        ];

        return Inertia::render('admin/artikel/Create', [
            'kategoriList'  => $kategoriList,
            'penulisList'   => $penulisList,
            'statusOptions' => $statusOptions,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul'             => 'required|string|max:255',
            'isi'               => 'required|string',
            'kategori'          => 'required|string|max:255',
            'penulis'           => 'required|string|max:255',
            'foto'              => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status'            => 'required|in:draft,publish',
            'tanggal_publikasi' => 'nullable|date',
        ]);

        // Generate slug unik
        $slug         = Str::slug($validated['judul']);
        $originalSlug = $slug;
        $counter      = 1;
        while (Artikel::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }
        $validated['slug'] = $slug;

        // Upload foto
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('img/artikel', 'public');
        }
        $validated['foto'] = $fotoPath;

        /*
         * Logika tanggal_publikasi:
         * - Status PUBLISH + ada tanggal  → simpan tanggal (bisa masa depan = terjadwal)
         * - Status PUBLISH + tanpa tanggal → set ke sekarang (publish langsung)
         * - Status DRAFT + ada tanggal    → simpan tanggal untuk penjadwalan, STATUS TETAP DRAFT
         * - Status DRAFT + tanpa tanggal  → tanpa tanggal, draft biasa
         *
         * Auto-publish berdasarkan jadwal dilakukan oleh scheduler (AutoPublishBerita command).
         * JANGAN ubah status di sini.
         */
        if ($validated['status'] === 'publish' && empty($validated['tanggal_publikasi'])) {
            $validated['tanggal_publikasi'] = now();
        }

        Artikel::create($validated);

        return redirect()->route('admin.artikel.index')
                         ->with('success', 'created');
    }

    public function show(string $id): Response
    {
        $artikel = Artikel::findOrFail($id);

        $artikel->tanggal_formatted = $artikel->tanggal_publikasi
            ? Carbon::parse($artikel->tanggal_publikasi)->translatedFormat('d F Y')
            : null;

        return Inertia::render('admin/artikel/Show', [
            'artikel' => $artikel,
        ]);
    }

    public function edit(string $id): Response
    {
        $artikel = Artikel::findOrFail($id);

        $kategoriList = Artikel::select('kategori')
            ->distinct()->whereNotNull('kategori')->where('kategori', '!=', '')->orderBy('kategori')
            ->get()->map(fn ($item) => ['value' => $item->kategori, 'label' => $item->kategori]);

        $penulisList = Artikel::select('penulis')
            ->distinct()->whereNotNull('penulis')->where('penulis', '!=', '')->orderBy('penulis')
            ->get()->map(fn ($item) => ['value' => $item->penulis, 'label' => $item->penulis]);

        $statusOptions = [
            ['value' => 'draft',   'label' => 'Draft'],
            ['value' => 'publish', 'label' => 'Publish'],
        ];

        return Inertia::render('admin/artikel/Edit', [
            'artikel'       => $artikel,
            'kategoriList'  => $kategoriList,
            'penulisList'   => $penulisList,
            'statusOptions' => $statusOptions,
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $artikel = Artikel::findOrFail($id);

        $validated = $request->validate([
            'judul'             => 'required|string|max:255',
            'isi'               => 'required|string',
            'kategori'          => 'required|string|max:255',
            'penulis'           => 'required|string|max:255',
            'foto'              => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status'            => 'required|in:draft,publish',
            'tanggal_publikasi' => 'nullable|date',
        ]);

        // Update slug hanya jika judul berubah
        if ($validated['judul'] !== $artikel->judul) {
            $slug         = Str::slug($validated['judul']);
            $originalSlug = $slug;
            $counter      = 1;
            while (Artikel::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            $validated['slug'] = $slug;
        }

        // Upload foto baru
        $fotoPath = $artikel->foto;
        if ($request->hasFile('foto')) {
            if ($artikel->foto) {
                Storage::disk('public')->delete($artikel->foto);
            }
            $fotoPath = $request->file('foto')->store('img/artikel', 'public');
        }
        $validated['foto'] = $fotoPath;

        /*
         * Logika tanggal_publikasi saat update:
         * - Status PUBLISH + ada tanggal  → simpan tanggal (bisa masa depan)
         * - Status PUBLISH + tanpa tanggal → set ke sekarang
         * - Status DRAFT                  → simpan apa adanya, JANGAN ubah status
         */
        if ($validated['status'] === 'publish' && empty($validated['tanggal_publikasi'])) {
            $validated['tanggal_publikasi'] = now();
        }

        $artikel->update($validated);

        return redirect()->route('admin.artikel.index')
                         ->with('success', 'updated');
    }

    public function destroy(string $id): RedirectResponse
    {
        $artikel = Artikel::findOrFail($id);

        try {
            if ($artikel->foto) {
                Storage::disk('public')->delete($artikel->foto);
            }
            $artikel->delete();

            return redirect()->route('admin.artikel.index')
                             ->with('success', 'deleted');

        } catch (\Exception $e) {
            \Log::error('Error deleting artikel: ' . $e->getMessage());

            return back()->withErrors([
                'delete_error' => 'Terjadi kesalahan saat menghapus artikel. Silakan coba lagi.',
            ]);
        }
    }
}
