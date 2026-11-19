<?php

return [
    /*
     * Public-facing defaults. The application name, accent colour, logo, email
     * and copyright still come from the Settings row so admins can change them.
     */
    'name' => env('BRAND_NAME', 'Odogwu Fest'),
    'headline' => env('BRAND_HEADLINE', 'The stage is set. Find your next unforgettable moment.'),
    'about' => env('BRAND_ABOUT', 'A concert that delivers more than music: production, atmosphere, great artists and memories that last.'),
    'location' => env('BRAND_LOCATION', 'Lagos, Nigeria'),
    'logo' => env('BRAND_LOGO', 'web/logo.png'),
    'social' => [
        'facebook' => 'https://facebook.com/Odogwufest',
        'twitter' => 'https://twitter.com/Odogwufest',
        'instagram' => 'https://www.instagram.com/Odogwufest/',
    ],
];
