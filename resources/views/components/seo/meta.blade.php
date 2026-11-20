@props([
    'title' => null,
    'description' => null,
    'url' => null,
    'image' => null,
    'type' => 'website',
    'siteName' => null,
    'twitterCard' => 'summary_large_image',
    'twitterSite' => '',
    'locale' => null,
])

@php
    $title = trim(strip_tags((string) ($title ?: config('brand.name'))));
    $description = trim(strip_tags((string) ($description ?: config('brand.about'))));
    $description = \Illuminate\Support\Str::limit($description, 300, '');

    $base = rtrim(config('brand.canonical_url', config('app.url')), '/');
    $url = $url ?: $base.'/'.ltrim(request()->path(), '/');

    $image = $image ?: $base.'/'.ltrim((string) config('brand.logo'), '/');
    $siteName = $siteName ?: config('brand.name');

    $twitterSite = trim((string) $twitterSite);
    if ($twitterSite !== '' && ! str_starts_with($twitterSite, '@')) {
        $twitterSite = '@'.$twitterSite;
    }

    $locale = $locale ?: str_replace('-', '_', app()->getLocale());
@endphp

<link rel="canonical" href="{{ $url }}">

<meta property="og:type" content="{{ $type }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $url }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:image:alt" content="{{ $title }}">
<meta property="og:locale" content="{{ $locale }}">

<meta name="twitter:card" content="{{ $twitterCard }}">
@if ($twitterSite !== '')
    <meta name="twitter:site" content="{{ $twitterSite }}">
@endif
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">
