<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class BeritaController extends Controller
{
    // ──────────────────────────────────────────────────────────────
    // INDEX
    // ──────────────────────────────────────────────────────────────
    public function index(Request $request): Response
    {
        $query = Berita::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('judul',    'LIKE', "%{$search}%")
                  ->orWhere('isi',      'LIKE', "%{$search}%")
                  ->orWhere('kategori', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('kategori')) {
            $query->where('kategori', $request->get('kategori'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('tahun')) {
            $query->whereYear('tanggal_publikasi', $request->get('tahun'));
        }

        $beritaRaw = $query->orderBy('tanggal_publikasi', 'desc')
                           ->orderBy('created_at', 'desc')
                           ->paginate(10)
                           ->appends($request->query());

        $beritaRaw->getCollection()->transform(function ($berita) {
            $berita->tanggal_formatted = $berita->tanggal_publikasi
                ? Carbon::parse($berita->tanggal_publikasi)->translatedFormat('d M Y')
                : null;
            $berita->tahun = $berita->tanggal_publikasi
                ? Carbon::parse($berita->tanggal_publikasi)->year
                : null;
            return $berita;
        });

        $kategoriList = Berita::select('kategori')
            ->distinct()->whereNotNull('kategori')->where('kategori', '!=', '')->orderBy('kategori')
            ->get()->map(fn ($item) => ['value' => $item->kategori, 'label' => $item->kategori]);

        $tahunList = Berita::selectRaw('YEAR(tanggal_publikasi) as tahun')
            ->distinct()->whereNotNull('tanggal_publikasi')->orderBy('tahun', 'desc')
            ->get()->map(fn ($item) => ['value' => $item->tahun, 'label' => $item->tahun]);

        $statusList = [
            ['value' => 'draft',   'label' => 'Draft'],
            ['value' => 'publish', 'label' => 'Publish'],
        ];

        return Inertia::render('admin/berita/Index', [
            'berita'       => $beritaRaw,
            'filters'      => $request->only(['search', 'kategori', 'status', 'tahun']),
            'kategoriList' => $kategoriList,
            'tahunList'    => $tahunList,
            'statusList'   => $statusList,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // CREATE
    // ──────────────────────────────────────────────────────────────
    public function create(): Response
    {
        $kategoriList = Berita::select('kategori')
            ->distinct()->whereNotNull('kategori')->where('kategori', '!=', '')->orderBy('kategori')
            ->get()->map(fn ($item) => ['value' => $item->kategori, 'label' => $item->kategori]);

        $statusOptions = [
            ['value' => 'draft',   'label' => 'Draft'],
            ['value' => 'publish', 'label' => 'Publish'],
        ];

        return Inertia::render('admin/berita/Create', [
            'kategoriList'  => $kategoriList,
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
        while (Berita::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }
        $validated['slug'] = $slug;

        // ── Upload foto utama ─────────────────────────────────────
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('img/berita', 'public');
        }
        $validated['foto'] = $fotoPath;

        // ── Upload foto tambahan (images) ─────────────────────────
        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $imagePaths[] = $file->store('img/berita', 'public');
            }
        }
        $validated['images'] = count($imagePaths) > 0 ? $imagePaths : null;

        // ── Logika tanggal_publikasi ──────────────────────────────
        if ($validated['status'] === 'publish' && empty($validated['tanggal_publikasi'])) {
            $validated['tanggal_publikasi'] = now();
        }

        Berita::create($validated);

        return redirect()->route('admin.berita.index')
                         ->with('success', 'created');
    }

    // ──────────────────────────────────────────────────────────────
    // SHOW
    // ──────────────────────────────────────────────────────────────
    public function show(string $id): Response
    {
        $berita = Berita::findOrFail($id);

        $berita->tanggal_formatted = $berita->tanggal_publikasi
            ? Carbon::parse($berita->tanggal_publikasi)->translatedFormat('d F Y')
            : null;

        return Inertia::render('admin/berita/Show', [
            'berita' => $berita,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────────
    public function edit(string $id): Response
    {
        $berita = Berita::findOrFail($id);

        $kategoriList = Berita::select('kategori')
            ->distinct()->whereNotNull('kategori')->where('kategori', '!=', '')->orderBy('kategori')
            ->get()->map(fn ($item) => ['value' => $item->kategori, 'label' => $item->kategori]);

        $statusOptions = [
            ['value' => 'draft',   'label' => 'Draft'],
            ['value' => 'publish', 'label' => 'Publish'],
        ];

        return Inertia::render('admin/berita/Edit', [
            'berita'        => $berita,
            'kategoriList'  => $kategoriList,
            'statusOptions' => $statusOptions,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // UPDATE
    // ──────────────────────────────────────────────────────────────
    public function update(Request $request, string $id): RedirectResponse
    {
        $berita = Berita::findOrFail($id);

        $validated = $request->validate([
            'judul'              => 'required|string|max:255',
            'isi'                => 'required|string',
            'kategori'           => 'required|string|max:255',
            'foto'               => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'images'             => 'nullable|array|max:10',
            'images.*'           => 'image|mimes:jpeg,png,jpg|max:2048',
            // Array path foto lama yang masih ingin DIPERTAHANKAN
            'existing_images'    => 'nullable|array',
            'existing_images.*'  => 'nullable|string',
            'remove_foto'        => 'nullable|in:0,1',
            'status'             => 'required|in:draft,publish',
            'tanggal_publikasi'  => 'nullable|date',
        ]);

        // ── Update slug jika judul berubah ────────────────────────
        if ($validated['judul'] !== $berita->judul) {
            $slug         = Str::slug($validated['judul']);
            $originalSlug = $slug;
            $counter      = 1;
            while (Berita::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            $validated['slug'] = $slug;
        }

        // ── Foto utama ────────────────────────────────────────────
        $fotoPath = $berita->foto;

        // Hapus foto utama jika diminta
        if ($request->input('remove_foto') === '1') {
            if ($berita->foto) {
                Storage::disk('public')->delete($berita->foto);
            }
            $fotoPath = null;
        }

        // Upload foto utama baru jika ada
        if ($request->hasFile('foto')) {
            if ($fotoPath) {
                Storage::disk('public')->delete($fotoPath);
            }
            $fotoPath = $request->file('foto')->store('img/berita', 'public');
        }
        $validated['foto'] = $fotoPath;

        // ── Foto tambahan (images) ────────────────────────────────
        // 1. Ambil foto lama yang ada di DB
        $oldImages      = $berita->images ?? [];  // array path lama

        // 2. Foto lama yang masih mau dipertahankan (dikirim dari frontend)
        $keepImages     = $validated['existing_images'] ?? [];

        // 3. Tentukan foto lama yang DIHAPUS (ada di DB tapi tidak di keepImages)
        $deletedImages  = array_diff($oldImages, $keepImages);

        // 4. Hapus file yang dihapus dari storage
        foreach ($deletedImages as $deletedPath) {
            Storage::disk('public')->delete($deletedPath);
        }

        // 5. Upload foto tambahan baru jika ada
        $newImagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $newImagePaths[] = $file->store('img/berita', 'public');
            }
        }

        // 6. Gabung foto lama yang dipertahankan + foto baru
        $finalImages = array_values(array_merge($keepImages, $newImagePaths));
        $validated['images'] = count($finalImages) > 0 ? $finalImages : null;

        // ── Logika tanggal_publikasi ──────────────────────────────
        if ($validated['status'] === 'publish' && empty($validated['tanggal_publikasi'])) {
            $validated['tanggal_publikasi'] = now();
        }

        // Bersihkan key yang tidak ada di fillable sebelum update
        unset($validated['existing_images'], $validated['remove_foto']);

        $berita->update($validated);

        return redirect()->route('admin.berita.index')
                         ->with('success', 'updated');
    }

    // ──────────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────────
    public function destroy(string $id): RedirectResponse
    {
        $berita = Berita::findOrFail($id);

        try {
            // Hapus foto utama
            if ($berita->foto) {
                Storage::disk('public')->delete($berita->foto);
            }

            // Hapus semua foto tambahan
            if (!empty($berita->images)) {
                foreach ($berita->images as $imagePath) {
                    Storage::disk('public')->delete($imagePath);
                }
            }

            $berita->delete();

            return redirect()->route('admin.berita.index')
                             ->with('success', 'deleted');

        } catch (\Exception $e) {
            \Log::error('Error deleting berita: ' . $e->getMessage());

            return back()->withErrors([
                'delete_error' => 'Terjadi kesalahan saat menghapus berita. Silakan coba lagi.',
            ]);
        }
    }
}
