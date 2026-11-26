<!doctype html>
<html lang="{{ config('app.locale_codes.'.app()->getLocale(), 'en') }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('description', config('brand.about'))">

    <title>@yield('title', config('brand.name').' — '.($publicSetting->app_name ?? config('app.name')))</title>

    @php
        $seoFallbackTitle = trim(view()->yieldContent('title', config('brand.name')));
        if ($seoFallbackTitle === '') {
            $seoFallbackTitle = config('brand.name');
        }

        $seoFallbackDescription = trim(view()->yieldContent('description', config('brand.about')));
        if ($seoFallbackDescription === '') {
            $seoFallbackDescription = config('brand.about');
        }

        $seoSectionTitle = trim(view()->yieldContent('seo.title'));
        $seoTitle = $seoSectionTitle !== '' ? $seoSectionTitle : $seoFallbackTitle;

        $seoSectionDescription = trim(view()->yieldContent('seo.description'));
        $seoDescription = $seoSectionDescription !== '' ? $seoSectionDescription : $seoFallbackDescription;

        $seoSectionImage = trim(view()->yieldContent('seo.image'));
        $seoImage = $seoSectionImage !== ''
            ? $seoSectionImage
            : ($publicSetting?->logo
                ? url('images/upload/'.$publicSetting->logo)
                : rtrim(config('brand.canonical_url', config('app.url')), '/').'/'.ltrim((string) config('brand.logo'), '/'));

        $seoType = trim(view()->yieldContent('seo.type', 'website')) ?: 'website';

        $seoSectionUrl = trim(view()->yieldContent('seo.url'));
        $seoUrl = $seoSectionUrl !== ''
            ? $seoSectionUrl
            : rtrim(config('brand.canonical_url', config('app.url')), '/').'/'.ltrim(request()->path(), '/');
    @endphp

    <x-seo.meta
        :title="$seoTitle"
        :description="$seoDescription"
        :url="$seoUrl"
        :image="$seoImage"
        :type="$seoType"
        :site-name="$publicSetting?->app_name ?? config('brand.name')"
        :twitter-site="config('brand.twitter_handle')"
    />

    <link rel="icon" href="{{ $publicSetting?->favicon ? url('images/upload/'.$publicSetting->favicon) : asset('web/logo.png') }}" type="image/png">

    <link rel="manifest" href="@yield('manifest', route('pwa.site-manifest'))">
    <meta name="theme-color" content="@yield('theme-color', '#FEC009')">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="@yield('app-title', config('brand.name', config('app.name')))">

    <x-theme-init />

    @vite([
        'resources/css/app.css',
        'resources/js/public.js',
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
