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
    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request): Response
    {
        $query = Artikel::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('judul',    'LIKE', "%{$search}%")
                  ->orWhere('isi',     'LIKE', "%{$search}%")
                  ->orWhere('kategori','LIKE', "%{$search}%")
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

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
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

    // ──────────────────────────────────────────────────────────────
    // STORE
    // ──────────────────────────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul'             => 'required|string|max:255',
            'isi'               => 'required|string',
            'kategori'          => 'required|string|max:255',
            'penulis'           => 'required|string|max:255',
            'foto'              => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'images'            => 'nullable|array|max:10',
            'images.*'          => 'image|mimes:jpeg,png,jpg|max:2048',
            'status'            => 'required|in:draft,publish',
            'tanggal_publikasi' => 'nullable|date',
        ]);

        // ── Slug unik ─────────────────────────────────────────────
        $slug         = Str::slug($validated['judul']);
        $originalSlug = $slug;
        $counter      = 1;
        while (Artikel::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }
        $validated['slug'] = $slug;

        // ── Upload foto utama ─────────────────────────────────────
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('img/artikel', 'public');
        }
        $validated['foto'] = $fotoPath;

        // ── Upload foto tambahan ──────────────────────────────────
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('img/artikel', 'public');
            }
        }
        $validated['images'] = count($imagePaths) > 0 ? $imagePaths : null;

        // ── Logika tanggal_publikasi ──────────────────────────────
        if ($validated['status'] === 'publish' && empty($validated['tanggal_publikasi'])) {
            $validated['tanggal_publikasi'] = now();
        }

        Artikel::create($validated);

        return redirect()->route('admin.artikel.index')
                         ->with('success', 'created');
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
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

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
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

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, string $id): RedirectResponse
    {
        $artikel = Artikel::findOrFail($id);

        $validated = $request->validate([
            'judul'              => 'required|string|max:255',
            'isi'                => 'required|string',
            'kategori'           => 'required|string|max:255',
            'penulis'            => 'required|string|max:255',
            'foto'               => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'images'             => 'nullable|array|max:10',
            'images.*'           => 'image|mimes:jpeg,png,jpg|max:2048',
            'existing_images'    => 'nullable|array',
            'existing_images.*'  => 'nullable|string',
            'remove_foto'        => 'nullable|in:0,1',
            'status'             => 'required|in:draft,publish',
            'tanggal_publikasi'  => 'nullable|date',
        ]);

        // ── Update slug jika judul berubah ────────────────────────
        if ($validated['judul'] !== $artikel->judul) {
            $slug         = Str::slug($validated['judul']);
            $originalSlug = $slug;
            $counter      = 1;
            while (Artikel::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            $validated['slug'] = $slug;
        }

        // ── Foto utama ────────────────────────────────────────────
        $fotoPath = $artikel->foto;

        if ($request->input('remove_foto') === '1') {
            if ($artikel->foto) {
                Storage::disk('public')->delete($artikel->foto);
            }
            $fotoPath = null;
        }

        if ($request->hasFile('foto')) {
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            $fotoPath = $request->file('foto')->store('img/artikel', 'public');
        }
        $validated['foto'] = $fotoPath;

        // ── Foto tambahan ─────────────────────────────────────────
        $oldImages     = $artikel->images ?? [];
        $keepImages    = $validated['existing_images'] ?? [];
        $deletedImages = array_diff($oldImages, $keepImages);

        foreach ($deletedImages as $deletedPath) {
            Storage::disk('public')->delete($deletedPath);
        }

        $newImagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $newImagePaths[] = $file->store('img/artikel', 'public');
            }
        }

        $finalImages = array_values(array_merge($keepImages, $newImagePaths));
        $validated['images'] = count($finalImages) > 0 ? $finalImages : null;

        // ── Logika tanggal_publikasi ──────────────────────────────
        if ($validated['status'] === 'publish' && empty($validated['tanggal_publikasi'])) {
            $validated['tanggal_publikasi'] = now();
        }

        unset($validated['existing_images'], $validated['remove_foto']);

        $artikel->update($validated);

        return redirect()->route('admin.artikel.index')
                         ->with('success', 'updated');
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(string $id): RedirectResponse
    {
        $artikel = Artikel::findOrFail($id);

        try {
            // Hapus foto utama
            if ($artikel->foto) {
                Storage::disk('public')->delete($artikel->foto);
            }

            // Hapus semua foto tambahan
            if (!empty($artikel->images)) {
                foreach ($artikel->images as $imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
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
