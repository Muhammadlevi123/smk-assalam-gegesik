<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Set locale Carbon ke Indonesia secara global
        // Sehingga translatedFormat() selalu pakai bahasa Indonesia
        Carbon::setLocale('id');
    }

    public function register(): void
    {
        //
    }
}
