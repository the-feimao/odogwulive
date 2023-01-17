<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Setting;
use App\Models\Language;
use App;
use Illuminate\Http\Request;

class Localization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (session()->has('locale')) 
        {
            \App::setLocale(session()->get('locale'));
        }
        else
        {
            $language = Setting::first()->language;
            $direction = Language::where('name',$language)->first()->direction;
            App::setLocale($language);
            session()->put('locale',$language);
            session()->put('direction',$direction);
        }
        return $next($request);
    }
}
