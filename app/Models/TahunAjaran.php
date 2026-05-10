<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';

    protected $fillable = [
        'tahun',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    protected $appends = ['is_aktif'];

    // ==========================================
    // ACCESSOR - otomatis cek dari tanggal
    // ==========================================

    public function getIsAktifAttribute(): bool
    {
        // ✅ DIPERBAIKI: null check sebelum gte/lte agar tidak error Carbon
        if (is_null($this->tanggal_mulai) || is_null($this->tanggal_selesai)) {
            return false;
        }

        $today = today();
        return $today->gte($this->tanggal_mulai) && $today->lte($this->tanggal_selesai);
    }

    // ==========================================
    // STATIC HELPERS
    // ==========================================

    /**
     * Ambil tahun ajaran yang sedang aktif berdasarkan tanggal hari ini.
     * Fallback ke yang terbaru jika tidak ada yang cocok.
     */
    public static function getAktif(): ?self
    {
        return self::whereDate('tanggal_mulai', '<=', today())
                   ->whereDate('tanggal_selesai', '>=', today())
                   ->first()
            ?? self::latest('tanggal_mulai')->first();
    }

    /**
     * Generate tahun ajaran berikutnya secara otomatis.
     * misal aktif 2024/2025 → return "2025/2026"
     */
    public static function generateTahunBerikutnya(): string
    {
        $latest = self::latest('tanggal_mulai')->first();

        if (!$latest) {
            $tahun = Carbon::now()->year;
            return $tahun . '/' . ($tahun + 1);
        }

        $tahunAkhir = (int) explode('/', $latest->tahun)[1];
        return $tahunAkhir . '/' . ($tahunAkhir + 1);
    }

    // ==========================================
    // ACCESSORS FORMAT TANGGAL
    // ==========================================

    public function getTanggalMulaiFormattedAttribute(): string
    {
        // ✅ null check untuk accessor format juga
        return $this->tanggal_mulai
            ? $this->tanggal_mulai->translatedFormat('d F Y')
            : '-';
    }

    public function getTanggalSelesaiFormattedAttribute(): string
    {
        return $this->tanggal_selesai
            ? $this->tanggal_selesai->translatedFormat('d F Y')
            : '-';
    }

    // ==========================================
    // RELASI
    // ==========================================

    public function guru(): BelongsToMany
    {
        return $this->belongsToMany(Guru::class, 'guru_tahun_ajaran')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    public function siswaStatus(): BelongsToMany
{
    return $this->belongsToMany(Siswa::class, 'siswa_tahun_ajaran')
                ->withPivot('status', 'kelulusan')
                ->withTimestamps();
}

    public function siswaKelas(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'siswa_kelas')
                    ->withPivot('kelas_id')
                    ->withTimestamps();
    }

    public function pengajaran(): BelongsToMany
    {
        return $this->belongsToMany(MataPelajaran::class, 'pengajaran')
                    ->withPivot('guru_id')
                    ->withTimestamps();
    }

    public function tenagaKependidikan(): BelongsToMany
    {
        return $this->belongsToMany(TenagaKependidikan::class, 'tenaga_kependidikan_tahun_ajaran')
                    ->withPivot('status')
                    ->withTimestamps();
    }

    public function waliKelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'wali_kelas')
                    ->withPivot('guru_id')
                    ->withTimestamps();
    }

    public function kalenderAkademik(): HasMany
    {
        return $this->hasMany(KalenderAkademik::class);
    }
}
