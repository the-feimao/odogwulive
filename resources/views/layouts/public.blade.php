<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description', config('brand.about'))">

    <title>@yield('title', config('brand.name').' — '.($publicSetting->app_name ?? config('app.name')))</title>

    <link rel="icon" href="{{ $publicSetting?->favicon ? url('images/upload/'.$publicSetting->favicon) : asset('web/logo.png') }}" type="image/png">

    <link rel="manifest" href="@yield('manifest', route('pwa.site-manifest'))">
    <meta name="theme-color" content="@yield('theme-color', '#FEC009')">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="@yield('app-title', config('brand.name', config('app.name')))">

    <x-theme-init />

    @vite([
        'resources/sass/app.scss',
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/pwa-register.js',
    ])

    @stack('head')
</head>

<body class="ds tw-min-h-screen tw-bg-neutral-50 tw-text-ink-900 dark:tw-bg-ink-950 dark:tw-text-neutral-100">
    <a href="#main-content"
        class="tw-sr-only tw-fixed tw-left-4 tw-top-4 tw-z-50 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-py-2 tw-text-sm tw-font-bold tw-text-ink-950 focus:tw-not-sr-only">
        {{ __('Skip to content') }}
    </a>

    <x-public-header :setting="$publicSetting ?? null" />

    <x-flash-messages />

    <main id="main-content">
        @yield('content')
    </main>

    <x-public-footer :setting="$publicSetting ?? null" />

    @stack('scripts')
</body>

</html>
