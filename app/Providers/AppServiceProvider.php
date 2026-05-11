<?php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (app()->environment('production')) {
            \Illuminate\Support\Facades\URL::forceRootUrl(config('app.url'));
        }
        Carbon::setLocale('id');
    }
    public function register(): void
    {
        //
    }
}
