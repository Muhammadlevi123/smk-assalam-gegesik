<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ContactMessage extends Model
{
    protected $table = 'contact_messages';

    protected $fillable = [
        'nama',
        'email',
        'nomor_telepon',
        'pesan',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Set locale Carbon ke Indonesia
        Carbon::setLocale('id');
    }

    /**
     * Accessor untuk mendapatkan tanggal created_at dalam format Indonesia
     */
    public function getCreatedAtFormattedAttribute()
    {
        return Carbon::parse($this->created_at)
            ->setTimezone('Asia/Jakarta')
            ->translatedFormat('d F Y, H:i:s');
    }

    /**
     * Accessor untuk mendapatkan tanggal updated_at dalam format Indonesia
     */
    public function getUpdatedAtFormattedAttribute()
    {
        return Carbon::parse($this->updated_at)
            ->setTimezone('Asia/Jakarta')
            ->translatedFormat('d F Y, H:i:s');
    }

    /**
     * Accessor untuk mendapatkan tanggal created_at dalam format singkat Indonesia
     */
    public function getCreatedAtShortAttribute()
    {
        return Carbon::parse($this->created_at)
            ->setTimezone('Asia/Jakarta')
            ->translatedFormat('d M Y');
    }

    /**
     * Accessor untuk mendapatkan waktu created_at saja
     */
    public function getCreatedAtTimeAttribute()
    {
        return Carbon::parse($this->created_at)
            ->setTimezone('Asia/Jakarta')
            ->format('H:i');
    }

    /**
     * Accessor untuk mendapatkan selisih waktu dalam bahasa Indonesia
     */
    public function getTimeAgoAttribute()
    {
        return Carbon::parse($this->created_at)
            ->setTimezone('Asia/Jakarta')
            ->diffForHumans();
    }

    /**
     * Accessor untuk mendapatkan nama hari dalam bahasa Indonesia
     */
    public function getDayNameAttribute()
    {
        return Carbon::parse($this->created_at)
            ->setTimezone('Asia/Jakarta')
            ->translatedFormat('l');
    }

    /**
     * Accessor untuk mendapatkan nama bulan dalam bahasa Indonesia
     */
    public function getMonthNameAttribute()
    {
        return Carbon::parse($this->created_at)
            ->setTimezone('Asia/Jakarta')
            ->translatedFormat('F');
    }

    /**
     * Accessor untuk mendapatkan preview pesan
     */
    public function getPesanPreviewAttribute()
    {
        return \Str::limit($this->pesan, 50, '...');
    }

    /**
     * Scope untuk filter berdasarkan bulan
     */
    public function scopeByMonth($query, $month)
    {
        return $query->whereMonth('created_at', $month);
    }

    /**
     * Scope untuk filter berdasarkan tahun
     */
    public function scopeByYear($query, $year)
    {
        return $query->whereYear('created_at', $year);
    }

    /**
     * Scope untuk filter berdasarkan rentang tanggal
     */
    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope untuk pencarian
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nama', 'LIKE', "%{$search}%")
              ->orWhere('email', 'LIKE', "%{$search}%")
              ->orWhere('pesan', 'LIKE', "%{$search}%")
              ->orWhere('nomor_telepon', 'LIKE', "%{$search}%");
        });
    }
}
