<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Design system — {{ config('app.name', 'Laravel') }}</title>

    <x-theme-init />

    @vite([
        'resources/sass/app.scss',
        'resources/css/app.css',
        'resources/js/app.js',
    ])
</head>
<body class="ds tw-min-h-screen tw-bg-neutral-50 dark:tw-bg-ink-950">
    <div class="tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6">
        <header class="tw-mb-12 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-4 tw-border-b tw-border-neutral-200 tw-pb-6 dark:tw-border-neutral-800">
            <div>
                <p class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest tw-text-accent-700 dark:tw-text-accent-300">Internal reference</p>
                <h1 class="tw-mt-1 tw-text-3xl tw-font-bold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">Design system</h1>
                <p class="tw-mt-1 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">Reusable Blade components, tokens and states.</p>
            </div>
            <div class="tw-flex tw-items-center tw-gap-3">
                <a href="{{ url('/') }}" class="tw-text-sm tw-font-semibold tw-text-accent-700 hover:tw-underline dark:tw-text-accent-300">Back to app</a>
                <x-theme-toggle />
            </div>
        </header>

        <main>
            @hasSection('content')
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </main>
    </div>
</body>
</html>
