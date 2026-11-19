<?php

use Illuminate\Support\Facades\Route;

// Additive installable scanner PWA. This does not replace or rename any
// existing scanner management route under routes/web.php.
Route::view('/scanner-app', 'scanner.pwa')->name('scanner.pwa');
