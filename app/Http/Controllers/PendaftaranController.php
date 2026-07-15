<?php

namespace App\Http\Controllers;

use App\Models\Pendaftaran;
use App\Models\Pengaturan;
use App\Models\TahunAjaran;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class PendaftaranController extends Controller
{
    public function create(): Response
    {
        $status = Pengaturan::pendaftaranStatus();

        if (!$status['dibuka']) {
            return Inertia::render('landing/PendaftaranClosed', [
                'tanggal_mulai'   => $status['tanggal_mulai'],
                'tanggal_selesai' => $status['tanggal_selesai'],
                'belum_mulai'     => $status['belum_mulai'],
                'sudah_lewat'     => $status['sudah_lewat'],
            ]);
        }

        $today = Carbon::today();

        $tahunPpdb = TahunAjaran::whereDate('tanggal_mulai', '>', $today)
            ->orderBy('tanggal_mulai', 'asc')
            ->first();

        if (!$tahunPpdb) {
            $tahunPpdb = TahunAjaran::whereDate('tanggal_mulai', '<=', $today)
                ->whereDate('tanggal_selesai', '>=', $today)
                ->first();
        }

        return Inertia::render('landing/Pendaftaran', [
            'tahun_ppdb' => $tahunPpdb?->tahun ?? null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $status = Pengaturan::pendaftaranStatus();

        if (!$status['dibuka']) {
            return redirect()->route('pendaftaran.create');
        }

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
        ], [
            'nama_lengkap.required'      => 'Nama lengkap wajib diisi.',
            'jenis_kelamin.required'     => 'Jenis kelamin wajib dipilih.',
            'tempat_lahir.required'      => 'Tempat lahir wajib diisi.',
            'tanggal_lahir.required'     => 'Tanggal lahir wajib diisi.',
            'nisn.required'              => 'NISN wajib diisi.',
            'agama.required'             => 'Agama wajib dipilih.',
            'anak_ke.required'           => 'Anak ke- wajib diisi.',
            'no_kartu_keluarga.required' => 'No. Kartu Keluarga wajib diisi.',
            'nik.required'               => 'NIK wajib diisi.',
            'no_akte.required'           => 'No. Akte Kelahiran wajib diisi.',
            'penerima_bantuan.required'  => 'Penerima bantuan wajib dipilih minimal satu.',
            'penerima_bantuan.min'       => 'Penerima bantuan wajib dipilih minimal satu.',
            'no_hp.required'             => 'No. HP wajib diisi.',
            'asal_sekolah.required'      => 'Asal sekolah wajib diisi.',
            'tahun_lulus.required'       => 'Tahun lulus wajib diisi.',
            'tahun_lulus.digits'         => 'Tahun lulus harus 4 digit.',
            'nama_ayah.required'         => 'Nama ayah wajib diisi.',
            'nik_ayah.required'          => 'NIK ayah wajib diisi.',
            'pendidikan_ayah.required'   => 'Pendidikan ayah wajib dipilih.',
            'tempat_lahir_ayah.required' => 'Tempat lahir ayah wajib diisi.',
            'pekerjaan_ayah.required'    => 'Pekerjaan ayah wajib diisi.',
            'no_hp_ayah.required'        => 'No. HP ayah wajib diisi.',
            'nama_ibu.required'          => 'Nama ibu wajib diisi.',
            'nik_ibu.required'           => 'NIK ibu wajib diisi.',
            'pendidikan_ibu.required'    => 'Pendidikan ibu wajib dipilih.',
            'tempat_lahir_ibu.required'  => 'Tempat lahir ibu wajib diisi.',
            'pekerjaan_ibu.required'     => 'Pekerjaan ibu wajib diisi.',
            'no_hp_ibu.required'         => 'No. HP ibu wajib diisi.',
            'jalan.required'             => 'Jalan wajib diisi.',
            'dusun_blok.required'        => 'Dusun/Blok wajib diisi.',
            'rt_rw.required'             => 'RT/RW wajib diisi.',
            'desa.required'              => 'Desa wajib diisi.',
            'kecamatan.required'         => 'Kecamatan wajib diisi.',
            'jurusan.required'           => 'Jurusan wajib dipilih.',
        ]);

        Pendaftaran::create($validated);

        return redirect()->route('pendaftaran.success');
    }

    public function success(): Response
    {
        return Inertia::render('landing/PendaftaranSuccess');
    }
}
