<?php

namespace App\Providers;

use App\Support\NotificationPresenter;
use App\Support\PaystackWithLocalCa;
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
        // Keep the Paystack facade/API unchanged, but give its Guzzle client a
        // project-local CA bundle so local checkout does not hit cURL error 60.
        $this->app->bind('laravel-paystack', fn () => new PaystackWithLocalCa());

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
