<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;


class AppServiceProvider extends ServiceProvider
{
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

        Route::model('babysitter', \App\Models\Babysitter::class);
        Route::model('conversation', \App\Models\Conversation::class);
        Route::pattern('babysitter', '[0-9]+');
        Route::pattern('conversation', '[0-9]+');
    }
}
