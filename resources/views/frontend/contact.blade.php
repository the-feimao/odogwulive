@extends('layouts.public')

@section('title', __('Contact Us').' — '.config('brand.name'))
@section('description', __('Get in touch with the '.config('brand.name').' team.'))

@section('content')
    @php
        $setting = \App\Models\Setting::find(1);
        $hasCoordinates = $setting && is_numeric($setting->default_lat) && is_numeric($setting->default_long);
    @endphp

    <section class="tw-relative tw-overflow-hidden tw-bg-ink-950">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-br tw-from-ink-950 tw-via-ink-900 tw-to-primary-900/60"></div>
        <div class="tw-relative tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
            <nav aria-label="{{ __('Breadcrumb') }}" class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-text-neutral-400">
                <a href="{{ url('/') }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ __('Home') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <span class="tw-text-neutral-200">{{ __('Contact') }}</span>
            </nav>

            <div class="tw-mt-6 tw-max-w-2xl">
                <x-badge tone="primary"><x-slot:icon><x-lucide-mail class="tw-h-3.5 tw-w-3.5" /></x-slot:icon>{{ __('Get in touch') }}</x-badge>
                <h1 class="tw-mt-3 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-neutral-50 md:tw-text-4xl">
                    {{ __('Contact Us') }}
                </h1>
                <p class="tw-mt-2 tw-text-sm tw-leading-relaxed tw-text-neutral-300">
                    {{ __('Questions about an event or your order? Send us a message.') }}
                </p>
            </div>
        </div>
    </section>

    <div class="tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
        <div class="tw-grid tw-gap-8 lg:tw-grid-cols-[minmax(0,1fr)_320px]">
            <div class="tw-min-w-0 tw-space-y-6">
                <x-card :title="__('Send a message')" :subtitle="__('We would love to hear from you.')">
                    <form action="#" method="post" role="form">
                        <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                            <x-input name="name" :label="__('Your Name')" type="text" autocomplete="name"
                                placeholder="{{ __('Your Name') }}" />
                            <x-input name="email" :label="__('Your Email')" type="email" autocomplete="email"
                                placeholder="{{ __('Your Email') }}" />
                            <div class="md:tw-col-span-2">
                                <x-input name="subject" :label="__('Subject')" type="text"
                                    placeholder="{{ __('Subject') }}" />
                            </div>
                            <div class="md:tw-col-span-2">
                                <x-textarea name="message" :label="__('Message')" :rows="8"
                                    placeholder="{{ __('Message') }}" />
                            </div>
                        </div>

                        <x-button type="submit" class="tw-mt-6">
                            {{ __('Send Message') }}
                        </x-button>
                    </form>
                </x-card>

                <x-card :padded="false">
                    <div id="contact_map" class="contact-map tw-h-72 tw-overflow-hidden tw-rounded-card sm:tw-h-96"
                        data-lat="{{ $hasCoordinates ? (float) $setting->default_lat : '' }}"
                        data-lng="{{ $hasCoordinates ? (float) $setting->default_long : '' }}"
                        role="img"
                        aria-label="{{ __('Map showing our location') }}">
                        <div data-map-fallback class="tw-flex tw-h-full tw-items-center tw-justify-center tw-bg-neutral-100 tw-px-6 tw-text-center tw-text-sm tw-text-neutral-500 dark:tw-bg-neutral-800 dark:tw-text-neutral-400">
                            {{ __('A map will appear here once a location is configured.') }}
                        </div>
                    </div>
                </x-card>
            </div>

            <aside class="tw-min-w-0 tw-space-y-6">
                <x-card :title="__('Find Us')">
                    @if ($setting?->favicon)
                        <img src="{{ url('images/upload/'.$setting->favicon) }}" alt="" width="70" height="70"
                            class="tw-h-16 tw-w-16 tw-object-contain">
                    @endif

                    @if ($setting?->email)
                        <p class="tw-mt-4 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-neutral-400">{{ __('Email') }}</p>
                        <a href="mailto:{{ $setting->email }}"
                            class="tw-mt-1 tw-inline-flex tw-items-center tw-break-all tw-gap-2 tw-text-sm tw-font-semibold tw-text-primary-700 hover:tw-text-primary-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-primary-300">
                            <x-lucide-mail class="tw-h-4 tw-w-4 tw-shrink-0" />
                            {{ $setting->email }}
                        </a>
                    @endif
                </x-card>

                <x-card :title="__('Social networks')" :subtitle="__('Follow the conversation.')">
                    <div class="tw-flex tw-flex-wrap tw-gap-2">
                        @foreach (config('brand.social') as $network => $url)
                            <a href="{{ $url }}" target="_blank" rel="noopener"
                                class="tw-inline-flex tw-h-10 tw-items-center tw-rounded-pill tw-border tw-border-neutral-200 tw-px-4 tw-text-xs tw-font-bold tw-capitalize tw-text-neutral-600 tw-transition-colors hover:tw-border-primary-400 hover:tw-text-primary-700 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-text-neutral-300">
                                {{ $network }}
                            </a>
                        @endforeach
                    </div>
                </x-card>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/contact-map.js')
@endpush
