<?php

use Illuminate\Support\Facades\Route;

$manifestHeaders = ['Content-Type' => 'application/manifest+json'];

Route::view('/site.webmanifest', 'pwa.site-manifest', [], 200, $manifestHeaders)
    ->name('pwa.site-manifest');

Route::view('/scanner.webmanifest', 'pwa.scanner-manifest', [], 200, $manifestHeaders)
    ->name('pwa.scanner-manifest');

Route::get('/robots.txt', function () {
    $sitemap = rtrim(config('brand.canonical_url', config('app.url')), '/').'/sitemap.xml';

    return response(
        "User-agent: *\nDisallow:\n\nSitemap: {$sitemap}\n",
        200,
        ['Content-Type' => 'text/plain; charset=UTF-8'],
    );
})->name('robots');
