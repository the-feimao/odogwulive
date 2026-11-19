<?php

use Illuminate\Support\Facades\Route;

/*
 * Internal-only routes. Required by RouteServiceProvider in local/dev only.
 */

Route::view('/style-guide', 'style-guide')->name('style-guide');
