<?php

use Illuminate\Support\Facades\Route;

$manifestHeaders = ['Content-Type' => 'application/manifest+json'];

Route::view('/site.webmanifest', 'pwa.site-manifest', [], 200, $manifestHeaders)
    ->name('pwa.site-manifest');

Route::view('/scanner.webmanifest', 'pwa.scanner-manifest', [], 200, $manifestHeaders)
    ->name('pwa.scanner-manifest');
