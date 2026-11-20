<?php

namespace App\Providers;

use App\Support\NotificationPresenter;
use App\Support\RequestData;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->scoped(RequestData::class, fn () => new RequestData());
        $this->app->scoped(NotificationPresenter::class, fn () => new NotificationPresenter());
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);      
        view()->composer('*', function ($view) {
            if(env('DB_DATABASE')!=null){
                $data = app(RequestData::class);
                $view->with([
                    'currency' => $data->currencySymbol(),
                    'sharedData' => $data,
                ]);
            }         
        });
    }
}
