@extends('layouts.public')

@section('title')
    {{ trim($data->first_name.' '.$data->last_name) ?: $data->name }} — {{ config('brand.name') }}
@endsection

@section('description', \Illuminate\Support\Str::limit(strip_tags((string) $data->bio), 160))

@section('content')
    @php
        $viewer = Auth::guard('appuser')->user();
        $following = $viewer
            ? array_map('strval', array_filter(explode(',', (string) ($viewer->following ?? ''))))
            : [];
        $isFollowing = in_array((string) $data->id, $following, true);
        $organizerName = trim($data->first_name.' '.$data->last_name) ?: $data->name;
        $image = $data->image ? url('images/upload/'.$data->image) : asset('web/bg/1.jpg');
    @endphp

    <section class="tw-relative tw-overflow-hidden tw-bg-ink-950">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-br tw-from-ink-950 tw-via-ink-900 tw-to-primary-900/60"></div>
        <div class="tw-relative tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
            <nav aria-label="{{ __('Breadcrumb') }}" class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-text-neutral-400">
                <a href="{{ url('/') }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ __('Home') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <span class="tw-text-neutral-200">{{ $organizerName }}</span>
            </nav>

            <div class="tw-mt-6 tw-grid tw-gap-8 md:tw-grid-cols-[minmax(0,1fr)_220px] md:tw-items-center">
                <div class="tw-min-w-0">
                    <x-badge tone="primary"><x-slot:icon><x-lucide-users class="tw-h-3.5 tw-w-3.5" /></x-slot:icon>{{ __('Organizer') }}</x-badge>
                    <h1 class="tw-mt-3 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-neutral-50 md:tw-text-4xl">
                        {{ $organizerName }}
                    </h1>
                    @if ($data->name)
                        <p class="tw-mt-1 tw-text-lg tw-font-semibold tw-text-primary-300">{{ $data->name }}</p>
                    @endif
                    @if ($data->bio)
                        <p class="tw-mt-4 tw-max-w-2xl tw-text-sm tw-leading-relaxed tw-text-neutral-300">{{ $data->bio }}</p>
                    @endif

                    <div class="tw-mt-6 tw-flex tw-flex-wrap tw-gap-x-6 tw-gap-y-3 tw-text-sm tw-text-neutral-200">
                        @if ($data->phone)
                            <span class="tw-inline-flex tw-items-center tw-gap-2">
                                <x-lucide-ticket class="tw-h-4 tw-w-4 tw-text-primary-400" />
                                {{ $data->phone }}
                            </span>
                        @endif
                        @if ($data->country)
                            <span class="tw-inline-flex tw-items-center tw-gap-2">
                                <x-lucide-map-pin class="tw-h-4 tw-w-4 tw-text-primary-400" />
                                {{ $data->country }}
                            </span>
                        @endif
                        @if ($data->email)
                            <span class="tw-inline-flex tw-items-center tw-gap-2">
                                <x-lucide-mail class="tw-h-4 tw-w-4 tw-text-primary-400" />
                                {{ $data->email }}
                            </span>
                        @endif
                    </div>

                    <div class="tw-mt-7">
                        @if ($viewer)
                            <button type="button" onclick="follow({{ $data->id }})"
                                data-follow-id="{{ $data->id }}"
                                data-follow-label="{{ __('Follow').' +' }}"
                                data-unfollow-label="{{ __('Unfollow') }}"
                                aria-pressed="{{ $isFollowing ? 'true' : 'false' }}"
                                class="tw-inline-flex tw-h-11 tw-items-center tw-justify-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-6 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition-colors hover:tw-bg-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600 disabled:tw-opacity-60">
                                <x-lucide-users class="tw-h-4 tw-w-4" />
                                {{ $isFollowing ? __('Unfollow') : __('Follow').' +' }}
                            </button>
                        @else
                            <a href="{{ url('user/login') }}"
                                class="tw-inline-flex tw-h-11 tw-items-center tw-justify-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-6 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition-colors hover:tw-bg-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600">
                                <x-lucide-users class="tw-h-4 tw-w-4" />
                                {{ __('Follow').' +' }}
                            </a>
                        @endif
                    </div>
                </div>

                <div class="tw-overflow-hidden tw-rounded-card tw-border tw-border-white/10 tw-bg-white/5 tw-shadow-card">
                    <img src="{{ $image }}" alt="{{ $organizerName }}" width="440" height="520" loading="lazy"
                        class="tw-aspect-[4/5] tw-h-full tw-w-full tw-object-cover">
                </div>
            </div>
        </div>
    </section>

    <p id="public-social-status" role="status" aria-live="polite" hidden
        class="tw-mx-auto tw-mt-6 tw-max-w-content tw-px-4 tw-text-sm tw-font-semibold tw-text-primary-700 md:tw-px-6"></p>

    <div class="tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
        <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-3">
            <div>
                <h2 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                    {{ __('My Events') }}
                    <span class="tw-text-primary-600 dark:tw-text-primary-400">({{ $data->total_event }})</span>
                </h2>
                <p class="tw-mt-1 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                    {{ __('Upcoming events hosted by this organizer.') }}
                </p>
            </div>
        </div>

        @if ($data->events->isNotEmpty())
            <div class="tw-mt-6 tw-grid tw-gap-6 sm:tw-grid-cols-2 xl:tw-grid-cols-3">
                @foreach ($data->events as $item)
                    @php
                        $eventUrl = url('event/'.$item->id.'/'.preg_replace('/\s+/', '-', $item->name));
                        $eventImage = $item->image ? url('images/upload/'.$item->image) : asset('web/bg/1.jpg');
                    @endphp

                    <x-card :padded="false">
                        <a href="{{ $eventUrl }}" class="tw-block tw-overflow-hidden tw-bg-neutral-100 dark:tw-bg-neutral-800" tabindex="-1" aria-hidden="true">
                            <div class="tw-aspect-video">
                                <img src="{{ $eventImage }}" alt="" loading="lazy" width="640" height="360"
                                    class="tw-h-full tw-w-full tw-object-cover">
                            </div>
                        </a>

                        <div class="tw-p-5">
                            <x-badge tone="primary" class="tw-mb-3">{{ $item->category?->name ?: __('Event') }}</x-badge>
                            <h3 class="tw-text-lg tw-font-bold tw-leading-snug tw-text-ink-900 dark:tw-text-neutral-50">
                                <a href="{{ $eventUrl }}" class="hover:tw-text-primary-700 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">{{ $item->name }}</a>
                            </h3>
                            <p class="tw-mt-2 tw-inline-flex tw-items-center tw-gap-1.5 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                <x-lucide-calendar-days class="tw-h-3.5 tw-w-3.5" />
                                {{ $item->start_time?->format('l, d F Y') }}
                            </p>

                            <dl class="tw-mt-4 tw-grid tw-grid-cols-3 tw-gap-2 tw-border-t tw-border-neutral-100 tw-pt-4 tw-text-center dark:tw-border-neutral-800">
                                <div>
                                    <dt class="tw-text-2xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-neutral-400">{{ __('People') }}</dt>
                                    <dd class="tw-mt-1 tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->people }}</dd>
                                </div>
                                <div>
                                    <dt class="tw-text-2xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-neutral-400">{{ __('Sold') }}</dt>
                                    <dd class="tw-mt-1 tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->sold_ticket }}</dd>
                                </div>
                                <div>
                                    <dt class="tw-text-2xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-neutral-400">{{ __('Available') }}</dt>
                                    <dd class="tw-mt-1 tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->available_ticket }}</dd>
                                </div>
                            </dl>

                            <a href="{{ $eventUrl }}"
                                class="tw-mt-4 tw-inline-flex tw-items-center tw-gap-1 tw-text-sm tw-font-bold tw-text-accent-700 hover:tw-text-accent-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-accent-300">
                                {{ __('More Detail') }}
                                <x-lucide-arrow-up-right class="tw-h-4 tw-w-4" />
                            </a>
                        </div>
                    </x-card>
                @endforeach
            </div>

            <div class="tw-mt-8">
                <x-pagination :paginator="$data->events" />
            </div>
        @else
            <x-card class="tw-mt-6">
                <x-empty-state :title="__('No upcoming events')" :description="__('This organizer has no published upcoming events right now.')" />
            </x-card>
        @endif
    </div>
@endsection

@push('scripts')
    @vite('resources/js/public-social.js')
@endpush
