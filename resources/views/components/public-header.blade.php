@props(['setting' => null])

@php
    $brand = config('brand');
    $brandName = $brand['name'];
    $logo = $setting->logo ?? null;
    $logoUrl = $logo ? url('images/upload/'.$logo) : asset($brand['logo']);

    $links = [
        ['label' => __('Home'), 'url' => url('/'), 'active' => request()->is('/')],
        ['label' => __('Events'), 'url' => route('frontend.index'), 'active' => request()->is('index')],
        ['label' => __('Categories'), 'url' => url('/all-category'), 'active' => request()->is('all-category*')],
        ['label' => __('Blog'), 'url' => url('/all-blogs'), 'active' => request()->is('all-blogs*')],
        ['label' => __('Contact'), 'url' => url('/contact'), 'active' => request()->is('contact*')],
    ];
@endphp

<header x-data="{ open: false }"
    class="tw-sticky tw-top-0 tw-z-40 tw-border-b tw-border-neutral-200/80 tw-bg-neutral-0/95 tw-backdrop-blur dark:tw-border-neutral-800 dark:tw-bg-ink-950/95">
    <div class="tw-mx-auto tw-flex tw-h-18 tw-max-w-content tw-items-center tw-justify-between tw-gap-4 tw-px-4 md:tw-px-6">
        <a href="{{ url('/') }}" class="tw-flex tw-shrink-0 tw-items-center tw-gap-2.5 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
            <img src="{{ $logoUrl }}" alt="" width="40" height="40" class="tw-h-10 tw-w-10 tw-object-contain">
            <span class="tw-text-base tw-font-extrabold tw-tracking-tight tw-text-ink-950 dark:tw-text-neutral-50">
                {{ $brandName }}
            </span>
        </a>

        <nav class="tw-hidden tw-items-center tw-gap-6 lg:tw-flex" aria-label="{{ __('Main navigation') }}">
            <x-public-nav-links :links="$links" />
        </nav>

        <div class="tw-hidden tw-items-center tw-gap-2 lg:tw-flex">
            @if (Auth::guard('appuser')->check())
                <a href="{{ url('/my-tickets') }}"
                    class="tw-inline-flex tw-h-9 tw-items-center tw-gap-1.5 tw-rounded-pill tw-px-3 tw-text-sm tw-font-semibold tw-text-neutral-600 hover:tw-bg-neutral-100 hover:tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-neutral-300 dark:hover:tw-bg-neutral-800 dark:hover:tw-text-neutral-50">
                    <x-lucide-ticket class="tw-h-4 tw-w-4" />
                    {{ __('My tickets') }}
                </a>
                <a href="{{ url('/user/profile') }}"
                    class="tw-inline-flex tw-h-9 tw-items-center tw-rounded-pill tw-border tw-border-neutral-300 tw-px-3.5 tw-text-sm tw-font-semibold tw-text-ink-900 hover:tw-border-neutral-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-text-neutral-100 dark:hover:tw-border-neutral-600">
                    {{ __('Profile') }}
                </a>
                <form method="POST" action="{{ url('/user/logout') }}">
                    @csrf
                    <button type="submit"
                        class="tw-inline-flex tw-h-9 tw-items-center tw-gap-1.5 tw-rounded-pill tw-border-0 tw-bg-transparent tw-px-3 tw-text-sm tw-font-semibold tw-text-neutral-600 hover:tw-bg-neutral-100 hover:tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-neutral-300 dark:hover:tw-bg-neutral-800 dark:hover:tw-text-neutral-50">
                        <x-lucide-log-out class="tw-h-4 tw-w-4" />
                        {{ __('Log out') }}
                    </button>
                </form>
            @else
                <a href="{{ url('/user/login') }}"
                    class="tw-inline-flex tw-h-9 tw-items-center tw-rounded-pill tw-px-3 tw-text-sm tw-font-semibold tw-text-neutral-600 hover:tw-bg-neutral-100 hover:tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-neutral-300 dark:hover:tw-bg-neutral-800 dark:hover:tw-text-neutral-50">
                    {{ __('Log in') }}
                </a>
                <a href="{{ url('/user/register') }}"
                    class="tw-inline-flex tw-h-9 tw-items-center tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition-colors hover:tw-bg-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600">
                    {{ __('Get started') }}
                </a>
            @endif
            <x-theme-toggle />
        </div>

        <div class="tw-flex tw-items-center tw-gap-2 lg:tw-hidden">
            <x-theme-toggle />
            <button type="button" @click="open = !open" :aria-expanded="open.toString()"
                :aria-label="open ? '{{ __('Close menu') }}' : '{{ __('Open menu') }}'"
                class="tw-inline-flex tw-h-10 tw-w-10 tw-items-center tw-justify-center tw-rounded-pill tw-border tw-border-neutral-200 tw-bg-transparent tw-text-ink-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-text-neutral-100 dark:hover:tw-bg-neutral-800">
                <x-lucide-x x-cloak x-show="open" class="tw-h-5 tw-w-5" />
                <x-lucide-menu x-cloak x-show="!open" class="tw-h-5 tw-w-5" />
            </button>
        </div>
    </div>

    <div x-cloak x-show="open" x-transition:enter="tw-transition tw-duration-150"
        x-transition:enter-start="tw-opacity-0 tw--translate-y-1" x-transition:leave="tw-transition tw-duration-100"
        x-transition:leave-end="tw-opacity-0" class="lg:tw-hidden">
        <nav class="tw-border-t tw-border-neutral-200 tw-bg-neutral-0 tw-px-4 tw-py-3 dark:tw-border-neutral-800 dark:tw-bg-ink-950"
            aria-label="{{ __('Mobile navigation') }}">
            <x-public-nav-links :links="$links" mobile />

            <div class="tw-mt-3 tw-grid tw-gap-2 tw-border-t tw-border-neutral-200 tw-pt-4 dark:tw-border-neutral-800">
                @if (Auth::guard('appuser')->check())
                    <a href="{{ url('/my-tickets') }}"
                        class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-stub tw-bg-neutral-100 tw-px-3 tw-py-2.5 tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-bg-neutral-800 dark:tw-text-neutral-50">
                        <x-lucide-ticket class="tw-h-4 tw-w-4" />
                        {{ __('My tickets') }}
                    </a>
                    <a href="{{ url('/user/profile') }}"
                        class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-stub tw-px-3 tw-py-2.5 tw-text-sm tw-font-semibold tw-text-neutral-700 dark:tw-text-neutral-200">
                        <x-lucide-users class="tw-h-4 tw-w-4" />
                        {{ __('Profile') }}
                    </a>
                    <form method="POST" action="{{ url('/user/logout') }}">
                        @csrf
                        <button type="submit"
                            class="tw-inline-flex tw-w-full tw-items-center tw-gap-2 tw-rounded-stub tw-border-0 tw-bg-transparent tw-px-3 tw-py-2.5 tw-text-left tw-text-sm tw-font-semibold tw-text-neutral-700 hover:tw-bg-neutral-100 dark:tw-text-neutral-200 dark:hover:tw-bg-neutral-800">
                            <x-lucide-log-out class="tw-h-4 tw-w-4" />
                            {{ __('Log out') }}
                        </button>
                    </form>
                @else
                    <a href="{{ url('/user/login') }}"
                        class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-stub tw-px-3 tw-py-2.5 tw-text-sm tw-font-semibold tw-text-neutral-700 dark:tw-text-neutral-200">
                        <x-lucide-users class="tw-h-4 tw-w-4" />
                        {{ __('Log in') }}
                    </a>
                    <a href="{{ url('/user/register') }}"
                        class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-stub tw-bg-primary-500 tw-px-3 tw-py-2.5 tw-text-sm tw-font-bold tw-text-ink-950">
                        <x-lucide-plus class="tw-h-4 tw-w-4" />
                        {{ __('Get started') }}
                    </a>
                @endif
            </div>
        </nav>
    </div>
</header>
