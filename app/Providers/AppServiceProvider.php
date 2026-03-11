<?php

namespace App\Providers;
use Carbon\Carbon;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
    Post::class => PostPolicy::class,
];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
            Carbon::setLocale(Session::get('locale'));
        }
    }
}
