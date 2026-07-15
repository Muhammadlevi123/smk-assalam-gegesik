<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class Pengaturan extends Model
{
    protected $table = 'pengaturans';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        return Cache::remember("pengaturan_{$key}", 3600, function () use ($key, $default) {
            $row = self::where('key', $key)->first();
            return $row?->value ?? $default;
        });
    }

    public static function set(string $key, $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("pengaturan_{$key}");
    }

    // Diganti dari delete() -> remove() karena delete() sudah dipakai Eloquent (instance method)
    public static function remove(string $key): void
    {
        self::where('key', $key)->delete();
        Cache::forget("pengaturan_{$key}");
    }

    public static function isTrue(string $key, bool $default = false): bool
    {
        $val = self::get($key, $default ? '1' : '0');
        return $val === '1' || $val === true || $val === 1;
    }

    public static function pendaftaranStatus(): array
    {
        $tanggalMulai   = self::get('pendaftaran_tanggal_mulai');
        $tanggalSelesai = self::get('pendaftaran_tanggal_selesai');

        $dibuka     = false;
        $belumMulai = false;
        $sudahLewat = false;

        if ($tanggalMulai && $tanggalSelesai) {
            $today   = Carbon::today();
            $mulai   = Carbon::parse($tanggalMulai)->startOfDay();
            $selesai = Carbon::parse($tanggalSelesai)->endOfDay();

            if ($today->lt($mulai)) {
                $belumMulai = true;
            } elseif ($today->gt($selesai)) {
                $sudahLewat = true;
            } else {
                $dibuka = true;
            }
        }

        return [
            'dibuka'          => $dibuka,
            'tanggal_mulai'   => $tanggalMulai,
            'tanggal_selesai' => $tanggalSelesai,
            'belum_mulai'     => $belumMulai,
            'sudah_lewat'     => $sudahLewat,
        ];
    }
}
