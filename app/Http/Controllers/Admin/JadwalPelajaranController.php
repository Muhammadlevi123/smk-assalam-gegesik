<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaran;
use App\Models\TahunAjaran;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class JadwalPelajaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = JadwalPelajaran::with(['kelas', 'tahunAjaran']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('kelas', function($q) use ($search) {
                $q->where('nama_kelas', 'LIKE', "%{$search}%")
                  ->orWhere('jurusan', 'LIKE', "%{$search}%")
                  ->orWhere('tingkat', 'LIKE', "%{$search}%");
            });
        }

        // Filter by kelas
        if ($request->filled('kelas')) {
            $query->where('kelas_id', $request->get('kelas'));
        }

        // Filter by tahun ajaran
        if ($request->filled('tahun_ajaran')) {
            $query->whereHas('tahunAjaran', function($q) use ($request) {
                $q->where('tahun', $request->get('tahun_ajaran'));
            });
        }

        // Filter by jurusan
        if ($request->filled('jurusan')) {
            $query->whereHas('kelas', function($q) use ($request) {
                $q->where('jurusan', $request->get('jurusan'));
            });
        }

        // Filter by tingkat
        if ($request->filled('tingkat')) {
            $query->whereHas('kelas', function($q) use ($request) {
                $q->where('tingkat', $request->get('tingkat'));
            });
        }

        $jadwalPelajaran = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());

        // Get unique jurusan for filter dropdown
        $jurusanList = Kelas::select('jurusan')
            ->distinct()
            ->whereNotNull('jurusan')
            ->where('jurusan', '!=', '')
            ->orderBy('jurusan')
            ->get()
            ->map(function($item) {
                return [
                    'value' => $item->jurusan,
                    'label' => $item->jurusan
                ];
            });

        // Get unique tingkat for filter dropdown
        $tingkatList = Kelas::select('tingkat')
            ->distinct()
            ->whereNotNull('tingkat')
            ->where('tingkat', '!=', '')
            ->orderBy('tingkat')
            ->get()
            ->map(function($item) {
                return [
                    'value' => $item->tingkat,
                    'label' => $item->tingkat
                ];
            });

        // Get all kelas for filter dropdown
        $kelasList = Kelas::with('tahunAjaran')
            ->orderBy('nama_kelas')
            ->get()
            ->map(function($kelas) {
                return [
                    'id' => $kelas->id,
                    'nama' => $kelas->nama_kelas . ' - ' . $kelas->tahunAjaran->tahun,
                    'jurusan' => $kelas->jurusan,
                    'tingkat' => $kelas->tingkat
                ];
            });

        // Get all tahun ajaran for filter dropdown
        $tahunAjaranList = TahunAjaran::orderBy('tahun')->get()->map(function($tahun) {
            return [
                'id' => $tahun->id,
                'tahun' => $tahun->tahun
            ];
        });

        return Inertia::render('admin/jadwal-pelajaran/Index', [
            'jadwalPelajaran' => $jadwalPelajaran,
            'filters' => [
                'search' => $request->get('search', ''),
                'kelas' => $request->get('kelas', ''),
                'tahun_ajaran' => $request->get('tahun_ajaran', ''),
                'jurusan' => $request->get('jurusan', ''),
                'tingkat' => $request->get('tingkat', ''),
            ],
            'jurusanList' => $jurusanList,
            'tingkatList' => $tingkatList,
            'kelasList' => $kelasList,
            'tahunAjaranList' => $tahunAjaranList,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $tahunAjaran = TahunAjaran::orderBy('tahun')->get();
        $kelas = Kelas::with('tahunAjaran')->orderBy('nama_kelas')->get();

        // Get unique jurusan for combobox dropdown
        $jurusanList = Kelas::select('jurusan')
            ->distinct()
            ->whereNotNull('jurusan')
            ->where('jurusan', '!=', '')
            ->orderBy('jurusan')
            ->get()
            ->map(function($item) {
                return [
                    'nama' => $item->jurusan
                ];
            });

        return Inertia::render('admin/jadwal-pelajaran/Create', [
            'tahunAjaran' => $tahunAjaran,
            'kelas' => $kelas,
            'jurusanList' => $jurusanList
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'foto_jadwal' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Check if jadwal already exists for this kelas and tahun ajaran
        $existingJadwal = JadwalPelajaran::where('kelas_id', $validated['kelas_id'])
            ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
            ->first();

        if ($existingJadwal) {
            return redirect()->back()
                ->withErrors(['kelas_id' => 'Jadwal untuk kelas dan tahun ajaran ini sudah ada.'])
                ->withInput();
        }

        // Handle foto upload
        if ($request->hasFile('foto_jadwal')) {
            $foto = $request->file('foto_jadwal');
            $fotoPath = $foto->store('img/jadwal-pelajaran', 'public');
            $validated['foto_jadwal'] = $fotoPath;
        }

        // Create jadwal pelajaran
        JadwalPelajaran::create($validated);

        return redirect()->route('admin.jadwal-pelajaran.index')
            ->with('success', 'Jadwal pelajaran berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): Response
    {
        $jadwalPelajaran = JadwalPelajaran::with(['kelas', 'tahunAjaran'])->findOrFail($id);

        return Inertia::render('admin/jadwal-pelajaran/Show', [
            'jadwalPelajaran' => $jadwalPelajaran
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): Response
    {
        $jadwalPelajaran = JadwalPelajaran::with(['kelas', 'tahunAjaran'])->findOrFail($id);
        $tahunAjaran = TahunAjaran::orderBy('tahun')->get();
        $kelas = Kelas::with('tahunAjaran')->orderBy('nama_kelas')->get();

        // Get unique jurusan for combobox dropdown
        $jurusanList = Kelas::select('jurusan')
            ->distinct()
            ->whereNotNull('jurusan')
            ->where('jurusan', '!=', '')
            ->orderBy('jurusan')
            ->get()
            ->map(function($item) {
                return [
                    'nama' => $item->jurusan
                ];
            });

        return Inertia::render('admin/jadwal-pelajaran/Edit', [
            'jadwalPelajaran' => $jadwalPelajaran,
            'tahunAjaran' => $tahunAjaran,
            'kelas' => $kelas,
            'jurusanList' => $jurusanList
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $jadwalPelajaran = JadwalPelajaran::findOrFail($id);

        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'foto_jadwal' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // Check if jadwal already exists for this kelas and tahun ajaran (excluding current record)
        $existingJadwal = JadwalPelajaran::where('kelas_id', $validated['kelas_id'])
            ->where('tahun_ajaran_id', $validated['tahun_ajaran_id'])
            ->where('id', '!=', $id)
            ->first();

        if ($existingJadwal) {
            return redirect()->back()
                ->withErrors(['kelas_id' => 'Jadwal untuk kelas dan tahun ajaran ini sudah ada.'])
                ->withInput();
        }

        // Handle foto upload
        if ($request->hasFile('foto_jadwal')) {
            // Delete old foto if exists
            if ($jadwalPelajaran->foto_jadwal && Storage::disk('public')->exists($jadwalPelajaran->foto_jadwal)) {
                Storage::disk('public')->delete($jadwalPelajaran->foto_jadwal);
            }

            $foto = $request->file('foto_jadwal');
            $fotoPath = $foto->store('img/jadwal-pelajaran', 'public');
            $validated['foto_jadwal'] = $fotoPath;
        }

        // Update jadwal pelajaran
        $jadwalPelajaran->update($validated);

        return redirect()->route('admin.jadwal-pelajaran.index')
            ->with('success', 'Jadwal pelajaran berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $jadwalPelajaran = JadwalPelajaran::findOrFail($id);

        // Delete foto if exists
        if ($jadwalPelajaran->foto_jadwal && Storage::disk('public')->exists($jadwalPelajaran->foto_jadwal)) {
            Storage::disk('public')->delete($jadwalPelajaran->foto_jadwal);
        }

        $jadwalPelajaran->delete();

        return redirect()->route('admin.jadwal-pelajaran.index')
            ->with('success', 'Jadwal pelajaran berhasil dihapus');
    }
}
