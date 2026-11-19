<?php

namespace App\Providers;

use App\Models\Setting;
use App\View\Composers\PublicHomeComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class PublicViewServiceProvider extends ServiceProvider
{
    /**
     * Share data with the new public layout and home page without changing
     * existing controllers or route names.
     */
    public function boot(): void
    {
        View::composer('layouts.public', function ($view) {
            $view->with('publicSetting', Setting::find(1));
        });

        View::composer('web.home', PublicHomeComposer::class);
    }
}
