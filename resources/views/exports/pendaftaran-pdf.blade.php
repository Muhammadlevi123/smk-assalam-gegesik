<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>Laporan Pendaftaran</title>
<style>
@page {
    margin: 0;
    size: a4 portrait;
}
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'DejaVu Sans', sans-serif; font-size: 8.5px; color: #1a1a1a; background: #fff; }

/* ── HEADER — pakai table agar logo & teks sejajar ── */
.header {
    background: #1a2332;
    color: white;
    padding: 12px 24px;
}
.header-table { width: 100%; border-collapse: collapse; }
.header-logo-cell { width: 46px; vertical-align: middle; }
.header-logo  { width: 38px; height: 38px; }
.header-school-cell { vertical-align: middle; padding-left: 10px; }
.header-date-cell { vertical-align: middle; text-align: right; white-space: nowrap; }
.school-name  { font-size: 11px; font-weight: bold; letter-spacing: 0.4px; color: white; }
.school-sub   { font-size: 7px; color: rgba(255,255,255,0.55); margin-top: 3px; }
.header-date  { font-size: 7.5px; color: rgba(255,255,255,0.6); }

/* ── TITLE AREA — putih, tengah dengan margin ── */
.title-area {
    text-align: center;
    padding: 40px 200px 14px;
}
.title-main {
    font-size: 14px;
    font-weight: bold;
    color: #1a2332;
    letter-spacing: 0.5px;
}
.title-period {
    font-size: 9px;
    color: #374151;
    margin-top: 4px;
    font-weight: bold;
}

/* ── META INFO — seperti referensi ── */
.meta-area {
    padding: 10px 30px 12px;
    margin-bottom: 12px;
}
.meta-row { margin-bottom: 3px; }
.meta-row .meta-key { display: inline-block; width: 100px; color: #6b7280; font-size: 8px; }
.meta-row .meta-val { font-size: 8px; font-weight: bold; color: #1a1a1a; }

/* ── TABLE ── */
.table-wrap { padding: 0 30px 20px; }
table.main { width: 100%; border-collapse: collapse; }
/* Thead repeat di setiap halaman baru */
table.main thead { display: table-header-group; }
table.main tbody { display: table-row-group; }
table.main thead tr { background: #1a2332; }
/* Hindari baris terpotong di tengah halaman */
table.main tbody tr { page-break-inside: avoid; }
table.main thead th {
    color: white;
    padding: 8px 6px;
    text-align: left;
    font-size: 8px;
    font-weight: bold;
    letter-spacing: 0.3px;
}
table.main tbody tr:nth-child(even) { background: #f8fafc; }
table.main tbody tr:nth-child(odd)  { background: #ffffff; }
table.main tbody td {
    padding: 5px 4px;
    font-size: 8px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

/* ── FOOTER ── */
.footer { padding: 8px 30px; border-top: 1px solid #e2e8f0; margin-top: 6px; }
.footer-left  { float: left;  font-size: 7px; color: #9ca3af; }
.footer-right { float: right; font-size: 7px; color: #9ca3af; }
.footer-clear { clear: both; }
</style>
</head>
<body>

@php
    $bulanNames = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
    if (isset($tahun_daftar) && $tahun_daftar !== 'Semua' && isset($bulan) && $bulan !== 'Semua') {
        $periodLabel = 'Bulan ' . ($bulanNames[(int)$bulan] ?? $bulan) . ' ' . $tahun_daftar;
    } elseif (isset($tahun_daftar) && $tahun_daftar !== 'Semua') {
        $periodLabel = 'Tahun ' . $tahun_daftar;
    } elseif (isset($bulan) && $bulan !== 'Semua') {
        $periodLabel = 'Bulan ' . ($bulanNames[(int)$bulan] ?? $bulan);
    } else {
        $periodLabel = 'Semua Periode';
    }

    // Path logo absolut dari public_path
    $logoPath = public_path('storage/img/logo/logo_w.png');
    $logoBase64 = '';
    if (file_exists($logoPath)) {
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    }
@endphp

<!-- HEADER -->
<div class="header">
    <table class="header-table">
        <tr>
            @if($logoBase64)
            <td class="header-logo-cell">
                <img src="{{ $logoBase64 }}" class="header-logo" alt="Logo" />
            </td>
            @endif
            <td class="header-school-cell">
                <div class="school-name">SMK ASSALAM GEGESIK</div>
                <div class="school-sub">Gegesik Lor, Kec. Gegesik, Kabupaten Cirebon, Jawa Barat &nbsp;|&nbsp; T: 0231 8830069</div>
            </td>
            <td class="header-date-cell">
                <div class="header-date">{{ $generated_at }}</div>
            </td>
        </tr>
    </table>
</div>

<!-- TITLE AREA -->
<div class="title-area">
    <div class="title-main">LAPORAN DATA PENDAFTARAN</div>
    <div class="title-period">{{ $periodLabel }}</div>
</div>

<!-- META INFO -->
<div class="meta-area">
    <div class="meta-row">
        <span class="meta-key">Total Pendaftar</span>
        <span class="meta-val">: {{ count($pendaftaran) }} siswa</span>
    </div>
    <div class="meta-row">
        <span class="meta-key">Laki-laki / Perempuan</span>
        <span class="meta-val">: {{ $pendaftaran->where('jenis_kelamin','Laki-laki')->count() }} / {{ $pendaftaran->where('jenis_kelamin','Perempuan')->count() }} siswa</span>
    </div>
    <div class="meta-row">
        <span class="meta-key">Jurusan TKRO</span>
        <span class="meta-val">: {{ $pendaftaran->where('jurusan','TKRO')->count() }} siswa</span>
    </div>
    <div class="meta-row">
        <span class="meta-key">Jurusan TJKT</span>
        <span class="meta-val">: {{ $pendaftaran->where('jurusan','TJKT')->count() }} siswa</span>
    </div>
</div>

<!-- TABLE -->
<div class="table-wrap">
    <table class="main">
        <thead>
            <tr>
                <th style="width:14px">No</th>
                <th style="width:80px">Nama Lengkap</th>
                <th style="width:12px; text-align:center">JK</th>
                <th style="width:60px">Tempat, Tgl Lahir</th>
                <th style="width:55px">NISN / NIK</th>
                <th style="width:25px">Agama</th>
                <th style="width:65px">Asal Sekolah</th>
                <th style="width:18px; text-align:center">Th. Lulus</th>
                <th style="width:24px; text-align:center">Jurusan</th>
                <th style="width:40px">Bantuan</th>
                <th style="width:50px">No. HP</th>

            </tr>
        </thead>
        <tbody>
            @forelse($pendaftaran as $i => $row)
            @php
                $pb = is_array($row->penerima_bantuan)
                    ? $row->penerima_bantuan
                    : (json_decode($row->penerima_bantuan, true) ?? [$row->penerima_bantuan]);
                $filtered = array_values(array_filter($pb, fn($v) => $v !== 'Tidak Ada'));
            @endphp
            <tr>
                <td style="text-align:center; color:#9ca3af">{{ $i + 1 }}</td>
                <td><strong>{{ $row->nama_lengkap }}</strong></td>
                <td style="text-align:center; font-weight:bold; color:{{ $row->jenis_kelamin === 'Laki-laki' ? '#1e40af' : '#be185d' }}">
                    {{ $row->jenis_kelamin === 'Laki-laki' ? 'L' : 'P' }}
                </td>
                <td>{{ $row->tempat_lahir }},<br>{{ $row->tanggal_lahir?->format('d/m/Y') }}</td>
                <td>
                    {{ $row->nisn }}<br>
                    <span style="color:#9ca3af; font-size:10px">{{ $row->nik }}</span>
                </td>
                <td>{{ $row->agama }}</td>
                <td>{{ $row->asal_sekolah }}</td>
                <td style="text-align:center">{{ $row->tahun_lulus }}</td>
                <td style="text-align:center; font-weight:bold; color:{{ $row->jurusan === 'TKRO' ? '#1e40af' : '#5b21b6' }}">
                    {{ $row->jurusan }}
                </td>
                <td>
                    @if(count($filtered) > 0)
                        {{ implode(', ', $filtered) }}
                    @else
                        <span style="color:#9ca3af">-</span>
                    @endif
                </td>
                <td>{{ $row->no_hp }}</td>

            </tr>
            @empty
            <tr>
                <td colspan="11" style="text-align:center; padding:20px; color:#9ca3af">
                    Tidak ada data pendaftaran
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- FOOTER -->
<div class="footer">
    <div class="footer-right">Dokumen resmi, harap dijaga kerahasiaannya</div>
    <div class="footer-clear"></div>
</div>

</body>
</html>
