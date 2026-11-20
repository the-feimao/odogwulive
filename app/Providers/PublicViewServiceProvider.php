<?php

namespace App\Providers;

use App\Support\NotificationPresenter;
use App\Support\RequestData;
use App\View\Composers\EventListingComposer;
use App\View\Composers\PublicHomeComposer;
use Illuminate\Support\Facades\Auth;
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
            $view->with('publicSetting', app(RequestData::class)->setting());
        });

        View::composer('web.home', PublicHomeComposer::class);
        View::composer(['web.landing', 'frontend.events'], EventListingComposer::class);

        View::composer('admin.layout.header', function ($view) {
            $user = Auth::user();
            if ($user && $user->hasRole('organization')) {
                $view->with('headerNotifications', app(NotificationPresenter::class)->forOrganizer($user->id));
            }
        });
    }
}
