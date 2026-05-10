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
    public function index(Request $request): Response
    {
        $query = Berita::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'LIKE', "%{$search}%")
                  ->orWhere('isi', 'LIKE', "%{$search}%")
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

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'judul'             => 'required|string|max:255',
            'isi'               => 'required|string',
            'kategori'          => 'required|string|max:255',
            'foto'              => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status'            => 'required|in:draft,publish',
            'tanggal_publikasi' => 'nullable|date',
        ]);

        // Generate slug unik
        $slug         = Str::slug($validated['judul']);
        $originalSlug = $slug;
        $counter      = 1;
        while (Berita::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }
        $validated['slug'] = $slug;

        // Upload foto
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('img/berita', 'public');
        }
        $validated['foto'] = $fotoPath;

        /*
         * Logika tanggal_publikasi:
         * - Status PUBLISH + ada tanggal  → simpan tanggal tersebut (bisa masa depan = terjadwal)
         * - Status PUBLISH + tanpa tanggal → set ke sekarang (publish langsung)
         * - Status DRAFT + ada tanggal    → simpan tanggal untuk penjadwalan, STATUS TETAP DRAFT
         * - Status DRAFT + tanpa tanggal  → tanpa tanggal, draft biasa
         *
         * CATATAN: Auto-publish berdasarkan jadwal dilakukan oleh App\Console\Commands\AutoPublishBerita
         * yang dijalankan via scheduler setiap menit. Jangan ubah status di sini.
         */
        if ($validated['status'] === 'publish' && empty($validated['tanggal_publikasi'])) {
            $validated['tanggal_publikasi'] = now();
        }

        // PENTING: Jangan pernah ubah status dari draft → publish di sini.
        // Status yang dikirim dari form adalah keputusan admin.

        Berita::create($validated);

        return redirect()->route('admin.berita.index')
                         ->with('success', 'created');
    }

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

    public function update(Request $request, string $id): RedirectResponse
    {
        $berita = Berita::findOrFail($id);

        $validated = $request->validate([
            'judul'             => 'required|string|max:255',
            'isi'               => 'required|string',
            'kategori'          => 'required|string|max:255',
            'foto'              => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status'            => 'required|in:draft,publish',
            'tanggal_publikasi' => 'nullable|date',
        ]);

        // Update slug hanya jika judul berubah
        if ($validated['judul'] !== $berita->judul) {
            $slug         = Str::slug($validated['judul']);
            $originalSlug = $slug;
            $counter      = 1;
            while (Berita::where('slug', $slug)->where('id', '!=', $id)->exists()) {
                $slug = $originalSlug . '-' . $counter++;
            }
            $validated['slug'] = $slug;
        }

        // Upload foto baru
        $fotoPath = $berita->foto;
        if ($request->hasFile('foto')) {
            if ($berita->foto) {
                Storage::disk('public')->delete($berita->foto);
            }
            $fotoPath = $request->file('foto')->store('img/berita', 'public');
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

        $berita->update($validated);

        return redirect()->route('admin.berita.index')
                         ->with('success', 'updated');
    }

    public function destroy(string $id): RedirectResponse
    {
        $berita = Berita::findOrFail($id);

        try {
            if ($berita->foto) {
                Storage::disk('public')->delete($berita->foto);
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
