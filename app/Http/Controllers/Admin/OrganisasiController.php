<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organisasi;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class OrganisasiController extends Controller
{
    /**
     * Ambil daftar jenis unik dari database untuk datalist.
     */
    private function getJenisList(): array
    {
        return Organisasi::select('jenis')
            ->distinct()
            ->orderBy('jenis')
            ->pluck('jenis')
            ->toArray();
    }

    public function index(Request $request): Response
    {
        $query = Organisasi::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'LIKE', "%{$search}%")
                  ->orWhere('jenis', 'LIKE', "%{$search}%")
                  ->orWhere('deskripsi', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->get('jenis'));
        }

        $organisasi = $query->orderBy('nama')
                            ->paginate(10)
                            ->withQueryString();

        $jenisList = Organisasi::select('jenis')
            ->distinct()
            ->orderBy('jenis')
            ->pluck('jenis')
            ->map(fn ($j) => ['value' => $j, 'label' => $j]);

        return Inertia::render('admin/organisasi/Index', [
            'organisasi' => $organisasi,
            'filters'    => $request->only(['search', 'jenis']),
            'jenisList'  => $jenisList,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('admin/organisasi/Create', [
            'jenisList' => $this->getJenisList(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:255',
            'jenis'     => 'required|string|max:255',
            'deskripsi' => 'nullable|string|max:1000',
            'logo'      => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('img/organisasi', 'public');
        }

        Organisasi::create($validated);

        return redirect()->route('admin.organisasi.index')
                         ->with('success', 'created');
    }

    public function show(string $id): Response
    {
        $organisasi = Organisasi::findOrFail($id);

        return Inertia::render('admin/organisasi/Show', [
            'organisasi' => $organisasi,
        ]);
    }

    public function edit(string $id): Response
    {
        $organisasi = Organisasi::findOrFail($id);

        return Inertia::render('admin/organisasi/Edit', [
            'organisasi' => $organisasi,
            'jenisList'  => $this->getJenisList(),
        ]);
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $organisasi = Organisasi::findOrFail($id);

        $validated = $request->validate([
            'nama'        => 'required|string|max:255',
            'jenis'       => 'required|string|max:255',
            'deskripsi'   => 'nullable|string|max:1000',
            'logo'        => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'remove_logo' => 'nullable|in:0,1',
        ]);

        if ($request->hasFile('logo')) {
            // Ada file baru → hapus lama, simpan baru
            if ($organisasi->logo) {
                Storage::disk('public')->delete($organisasi->logo);
            }
            $validated['logo'] = $request->file('logo')->store('img/organisasi', 'public');

        } elseif ($request->input('remove_logo') === '1') {
            // User sengaja hapus logo → kosongkan
            if ($organisasi->logo) {
                Storage::disk('public')->delete($organisasi->logo);
            }
            $validated['logo'] = null;

        } else {
            // Tidak ada perubahan logo → abaikan kolom ini
            unset($validated['logo']);
        }

        // Jangan simpan remove_logo ke database
        unset($validated['remove_logo']);

        $organisasi->update($validated);

        return redirect()->route('admin.organisasi.index')
                         ->with('success', 'updated');
    }

    public function destroy(string $id): RedirectResponse
    {
        $organisasi = Organisasi::findOrFail($id);

        if ($organisasi->logo) {
            Storage::disk('public')->delete($organisasi->logo);
        }

        $organisasi->delete();

        return redirect()->route('admin.organisasi.index')
                         ->with('success', 'deleted');
    }
}
