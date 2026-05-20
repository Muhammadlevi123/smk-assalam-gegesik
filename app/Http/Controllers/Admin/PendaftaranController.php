<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pendaftaran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Barryvdh\DomPDF\Facade\Pdf;

class PendaftaranController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Pendaftaran::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap',  'LIKE', "%{$search}%")
                  ->orWhere('nisn',        'LIKE', "%{$search}%")
                  ->orWhere('nik',         'LIKE', "%{$search}%")
                  ->orWhere('no_hp',       'LIKE', "%{$search}%")
                  ->orWhere('asal_sekolah','LIKE', "%{$search}%");
            });
        }

        // Filter bulan daftar
        if ($request->filled('bulan')) {
            $query->whereMonth('created_at', $request->get('bulan'));
        }

        // Filter tahun daftar
        if ($request->filled('tahun_daftar')) {
            $query->whereYear('created_at', $request->get('tahun_daftar'));
        }

        $pendaftaran = $query->orderBy('created_at', 'desc')
                             ->paginate(15)
                             ->withQueryString();

        $tahunLulusList = Pendaftaran::select('tahun_lulus')
            ->distinct()
            ->orderBy('tahun_lulus', 'desc')
            ->pluck('tahun_lulus');

        // Tahun yang ada datanya (dari created_at)
        $tahunDaftarList = Pendaftaran::selectRaw('YEAR(created_at) as tahun')
            ->distinct()
            ->orderBy('tahun', 'desc')
            ->pluck('tahun')
            ->map(fn($t) => (int) $t)
            ->values()
            ->toArray();

        // Bulan per tahun yang ada datanya
        $bulanDaftarMap = [];
        foreach ($tahunDaftarList as $tahun) {
            $bulanDaftarMap[$tahun] = Pendaftaran::selectRaw('MONTH(created_at) as bulan')
                ->whereYear('created_at', $tahun)
                ->distinct()
                ->orderBy('bulan')
                ->pluck('bulan')
                ->map(fn($b) => (int) $b)
                ->values()
                ->toArray();
        }

        return Inertia::render('admin/pendaftaran/Index', [
            'pendaftaran'    => $pendaftaran,
            'filters'        => $request->only([
                'search', 'jenis_kelamin', 'jurusan', 'tahun_lulus', 'penerima_bantuan',
            ]),
            'tahunLulusList'  => $tahunLulusList,
            'tahunDaftarList' => $tahunDaftarList,
            'bulanDaftarMap'  => $bulanDaftarMap,
            'totalPendaftar'  => Pendaftaran::count(),
        ]);
    }

    public function show(Pendaftaran $pendaftaran): Response
    {
        return Inertia::render('admin/pendaftaran/Show', [
            'pendaftaran' => $pendaftaran,
        ]);
    }

    public function edit(Pendaftaran $pendaftaran): Response
    {
        return Inertia::render('admin/pendaftaran/Edit', [
            'pendaftaran' => $pendaftaran,
        ]);
    }

    public function update(Request $request, Pendaftaran $pendaftaran): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lengkap'       => 'required|string|max:255',
            'jenis_kelamin'      => 'required|in:Laki-laki,Perempuan',
            'tempat_lahir'       => 'required|string|max:100',
            'tanggal_lahir'      => 'required|date',
            'nisn'               => 'required|string|max:20',
            'agama'              => 'required|string|max:50',
            'anak_ke'            => 'required|integer|min:1|max:30',
            'no_kartu_keluarga'  => 'required|string|max:30',
            'nik'                => 'required|string|max:20',
            'no_akte'            => 'required|string|max:100',
            'penerima_bantuan'   => 'required|array|min:1',
            'penerima_bantuan.*' => 'in:KIP,KPS/KKS/PKH,SKTM,Tidak Ada',
            'nomor_kip'          => 'nullable|string|max:50',
            'no_hp'              => 'required|string|max:20',
            'asal_sekolah'       => 'required|string|max:255',
            'tahun_lulus'        => 'required|digits:4|integer|min:2000|max:2099',
            'nama_ayah'          => 'required|string|max:255',
            'nik_ayah'           => 'required|string|max:20',
            'pendidikan_ayah'    => 'required|string|max:50',
            'tempat_lahir_ayah'  => 'required|string|max:100',
            'tanggal_lahir_ayah' => 'nullable|date',
            'pekerjaan_ayah'     => 'required|string|max:100',
            'no_hp_ayah'         => 'required|string|max:20',
            'nama_ibu'           => 'required|string|max:255',
            'nik_ibu'            => 'required|string|max:20',
            'pendidikan_ibu'     => 'required|string|max:50',
            'tempat_lahir_ibu'   => 'required|string|max:100',
            'tanggal_lahir_ibu'  => 'nullable|date',
            'pekerjaan_ibu'      => 'required|string|max:100',
            'no_hp_ibu'          => 'required|string|max:20',
            'jalan'              => 'required|string|max:255',
            'dusun_blok'         => 'required|string|max:100',
            'rt_rw'              => 'required|string|max:10',
            'desa'               => 'required|string|max:100',
            'kecamatan'          => 'required|string|max:100',
            'jurusan'            => 'required|in:TKRO,TJKT',
        ]);

        $pendaftaran->update($validated);

        return redirect()->route('admin.pendaftaran.show', $pendaftaran->id)
                         ->with('success', 'updated');
    }

    public function destroy(Pendaftaran $pendaftaran): RedirectResponse
    {
        $pendaftaran->delete();

        return redirect()->route('admin.pendaftaran.index')
                         ->with('success', 'deleted');
    }

    // ── Export CSV ────────────────────────────────────────────────
    public function exportExcel(Request $request)
    {
        $query = Pendaftaran::query();

        if ($request->filled('tahun_lulus')) {
            $query->where('tahun_lulus', $request->get('tahun_lulus'));
        }
        if ($request->filled('jurusan')) {
            $query->where('jurusan', $request->get('jurusan'));
        }
        if ($request->filled('bulan')) {
            $query->whereMonth('created_at', $request->get('bulan'));
        }
        if ($request->filled('tahun_daftar')) {
            $query->whereYear('created_at', $request->get('tahun_daftar'));
        }

        $data     = $query->orderBy('created_at', 'desc')->get();
        $filename = 'pendaftaran_' . date('Ymd') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $columns = [
            'No', 'Nama Lengkap', 'Jenis Kelamin', 'Tempat Lahir', 'Tanggal Lahir',
            'NISN', 'NIK', 'Agama', 'Anak Ke', 'No. KK', 'No. Akte',
            'Penerima Bantuan', 'Nomor KIP', 'No. HP', 'Asal Sekolah', 'Tahun Lulus', 'Jurusan',
            'Nama Ayah', 'NIK Ayah', 'Pendidikan Ayah', 'Tempat Lahir Ayah',
            'Tanggal Lahir Ayah', 'Pekerjaan Ayah', 'No. HP Ayah',
            'Nama Ibu', 'NIK Ibu', 'Pendidikan Ibu', 'Tempat Lahir Ibu',
            'Tanggal Lahir Ibu', 'Pekerjaan Ibu', 'No. HP Ibu',
            'Jalan', 'Dusun/Blok', 'RT/RW', 'Desa', 'Kecamatan', 'Tanggal Daftar',
        ];

        $callback = function () use ($data, $columns) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $columns, ';');
            foreach ($data as $i => $row) {
                // penerima_bantuan sekarang array — join dengan koma
                $penerimaBantuan = is_array($row->penerima_bantuan)
                    ? implode(', ', $row->penerima_bantuan)
                    : ($row->penerima_bantuan ?? '-');

                fputcsv($file, [
                    $i + 1, $row->nama_lengkap, $row->jenis_kelamin, $row->tempat_lahir,
                    $row->tanggal_lahir?->format('d/m/Y'), $row->nisn, $row->nik, $row->agama,
                    $row->anak_ke, $row->no_kartu_keluarga, $row->no_akte ?? '-',
                    $penerimaBantuan, $row->nomor_kip ?? '-', $row->no_hp,
                    $row->asal_sekolah, $row->tahun_lulus, $row->jurusan,
                    $row->nama_ayah, $row->nik_ayah, $row->pendidikan_ayah, $row->tempat_lahir_ayah,
                    $row->tanggal_lahir_ayah?->format('d/m/Y') ?? '-', $row->pekerjaan_ayah, $row->no_hp_ayah,
                    $row->nama_ibu, $row->nik_ibu, $row->pendidikan_ibu, $row->tempat_lahir_ibu,
                    $row->tanggal_lahir_ibu?->format('d/m/Y') ?? '-', $row->pekerjaan_ibu, $row->no_hp_ibu,
                    $row->jalan, $row->dusun_blok, $row->rt_rw, $row->desa, $row->kecamatan,
                    $row->created_at?->format('d/m/Y H:i'),
                ], ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Export PDF ────────────────────────────────────────────────
    public function exportPdf(Request $request)
    {
        $query = Pendaftaran::query();

        if ($request->filled('tahun_lulus')) {
            $query->where('tahun_lulus', $request->get('tahun_lulus'));
        }
        if ($request->filled('jurusan')) {
            $query->where('jurusan', $request->get('jurusan'));
        }
        if ($request->filled('bulan')) {
            $query->whereMonth('created_at', $request->get('bulan'));
        }
        if ($request->filled('tahun_daftar')) {
            $query->whereYear('created_at', $request->get('tahun_daftar'));
        }

        $data     = $query->orderBy('created_at', 'desc')->get();
        $filename = 'pendaftaran_' . date('Ymd') . '.pdf';

        $pdf = Pdf::loadView('exports.pendaftaran-pdf', [
            'pendaftaran'  => $data,
            'tahun_lulus'  => $request->get('tahun_lulus', 'Semua'),
            'jurusan'      => $request->get('jurusan', 'Semua'),
            'bulan'        => $request->get('bulan', 'Semua'),
            'tahun_daftar' => $request->get('tahun_daftar', 'Semua'),
            'generated_at' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }
}
