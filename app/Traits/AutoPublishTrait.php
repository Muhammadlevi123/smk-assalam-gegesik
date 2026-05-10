<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

trait AutoPublishTrait
{
    /**
     * Boot the auto publish trait.
     *
     * CATATAN PENTING:
     * Hook `saving` dihapus karena menyebabkan status draft → publish
     * secara paksa saat create/update, bahkan untuk tanggal masa depan.
     * Auto-publish sekarang sepenuhnya ditangani oleh:
     * App\Console\Commands\AutoPublishBerita (via scheduler setiap menit).
     */
    public static function bootAutoPublishTrait(): void
    {
        // Tidak ada hook saving — biarkan scheduler yang handle auto-publish
    }

    /**
     * Cek apakah konten ini sudah waktunya dipublish.
     * Digunakan oleh command AutoPublishBerita.
     */
    public function shouldAutoPublish(): bool
    {
        if (!$this->tanggal_publikasi) return false;

        return $this->status === 'draft'
            && Carbon::parse($this->tanggal_publikasi)
                     ->startOfDay()
                     ->lte(Carbon::now()->startOfDay());
    }

    /**
     * Scope untuk mengambil konten yang sudah waktunya publish
     * (digunakan di command AutoPublishBerita).
     */
    public function scopeAutoPublishReady(Builder $query): Builder
    {
        return $query->where('status', 'draft')
                     ->whereNotNull('tanggal_publikasi')
                     ->whereDate('tanggal_publikasi', '<=', Carbon::today());
    }

    /**
     * Scope untuk menampilkan konten yang sudah publish
     * ATAU draft yang sudah melewati tanggal publish-nya
     * (berguna untuk frontend publik).
     */
    public function scopePublishable(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('status', 'publish')
              ->orWhere(function ($sub) {
                  $sub->where('status', 'draft')
                      ->whereNotNull('tanggal_publikasi')
                      ->whereDate('tanggal_publikasi', '<=', Carbon::today());
              });
        });
    }

    /**
     * Trigger auto-publish manual untuk satu record.
     */
    public function triggerAutoPublish(): bool
    {
        if ($this->shouldAutoPublish()) {
            // Pakai query langsung agar tidak trigger saving hook lain
            static::where('id', $this->id)->update(['status' => 'publish']);
            $this->status = 'publish';
            return true;
        }
        return false;
    }

    /**
     * Batch auto-publish — dipanggil oleh command AutoPublishBerita.
     * Lebih efisien karena satu query untuk semua record.
     */
    public static function batchAutoPublish(): int
    {
        return static::where('status', 'draft')
            ->whereNotNull('tanggal_publikasi')
            ->whereDate('tanggal_publikasi', '<=', Carbon::today())
            ->update(['status' => 'publish']);
    }
}
