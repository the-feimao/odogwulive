@extends('layouts.public')

@php
    $galleryImages = collect([$data->image])
        ->merge(explode(',', (string) $data->gallery))
        ->filter()
        ->map(fn ($image) => url('images/upload/'.trim($image)))
        ->unique()
        ->values()
        ->all();

    if (count($galleryImages) === 0) {
        $galleryImages = [asset('web/bg/1.jpg')];
    }

    $heroImage = $galleryImages[0];
    $isOnline = $data->type === 'online';
    $locationLabel = $isOnline
        ? __('Online event')
        : ($data->address ?: __('Venue to be announced'));
    $hasCoordinates = is_numeric($data->lat) && is_numeric($data->lang);
    $osmUrl = $hasCoordinates
        ? 'https://www.openstreetmap.org/?mlat='.(float) $data->lat.'&mlon='.(float) $data->lang.'#map=15/'.(float) $data->lat.'/'.(float) $data->lang
        : null;
    $tags = array_values(array_filter(array_map('trim', explode(',', (string) $data->tags))));
    $reviews = $data->review ?? collect();
    $categoryUrl = url('events-category/'.$data->category_id.'/'.(\Illuminate\Support\Str::slug((string) ($data->category?->name ?? '')) ?: 'category'));

    $seoEventUrl = rtrim(config('brand.canonical_url', config('app.url')), '/').'/'.ltrim(request()->path(), '/');
@endphp

@section('title', $data->name.' — '.config('brand.name'))
@section('description', \Illuminate\Support\Str::limit(strip_tags((string) $data->description), 160))

{{-- Additive overrides; the original title/description sections stay unchanged. --}}
@section('seo.title', $data->name.' — '.config('brand.name'))
@section('seo.description', \Illuminate\Support\Str::limit(strip_tags((string) $data->description), 160))
@section('seo.image', $heroImage)
@section('seo.type', 'event')
@section('seo.url', $seoEventUrl)

@push('head')
    <x-seo.event-json-ld
        :event="$data"
        :url="$seoEventUrl"
        :image="$heroImage"
        :organization="$data->organization"
        :tickets="$data->paid_ticket->concat($data->free_ticket)"
        :currency="$setting->currency ?? null"
    />
@endpush

@section('content')

    <section class="tw-relative tw-overflow-hidden tw-bg-ink-950">
        <img src="{{ $heroImage }}" alt="" width="1600" height="900"
            class="tw-absolute tw-inset-0 tw-h-full tw-w-full tw-object-cover tw-opacity-35">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-r tw-from-ink-950 tw-via-ink-950/90 tw-to-ink-950/30"></div>

        <div class="tw-relative tw-mx-auto tw-max-w-content tw-px-4 tw-py-14 md:tw-px-6 md:tw-py-20">
            <nav aria-label="{{ __('Breadcrumb') }}" class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-text-neutral-400">
                <a href="{{ url('/') }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ __('Home') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <a href="{{ url('/all-events') }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ __('Events') }}</a>
                @if ($data->category)
                    <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                    <a href="{{ $categoryUrl }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ $data->category->name }}</a>
                @endif
            </nav>

            <div class="tw-mt-6 tw-flex tw-flex-wrap tw-items-center tw-gap-2">
                @if ($data->category)
                    <a href="{{ $categoryUrl }}"><x-badge tone="primary">{{ $data->category->name }}</x-badge></a>
                @endif
                <x-badge :tone="$isOnline ? 'accent' : 'neutral'">
                    {{ $isOnline ? __('Online') : __('In person') }}
                </x-badge>
            </div>

            <div class="tw-mt-4 tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-6">
                <div class="tw-max-w-3xl">
                    <h1 class="tw-text-4xl tw-font-extrabold tw-leading-tight tw-tracking-tight tw-text-neutral-50 md:tw-text-5xl">
                        {{ $data->name }}
                    </h1>
                    <div class="tw-mt-5 tw-flex tw-flex-wrap tw-gap-x-6 tw-gap-y-3 tw-text-sm tw-text-neutral-200">
                        <span class="tw-inline-flex tw-items-center tw-gap-2">
                            <x-lucide-calendar-days class="tw-h-4 tw-w-4 tw-text-primary-400" />
                            {{ $data->start_time?->format('l, F j, Y') }}
                        </span>
                        <span class="tw-inline-flex tw-items-center tw-gap-2">
                            <x-lucide-clock class="tw-h-4 tw-w-4 tw-text-primary-400" />
                            {{ $data->start_time?->format('g:i A') }}
                            @if ($data->end_time)
                                – {{ $data->end_time->format('g:i A') }}
                            @endif
                        </span>
                        <span class="tw-inline-flex tw-items-center tw-gap-2">
                            <x-lucide-map-pin class="tw-h-4 tw-w-4 tw-text-primary-400" />
                            {{ $locationLabel }}
                        </span>
                    </div>
                </div>

                <div class="tw-flex tw-items-center tw-gap-3">
                    <x-favorite-button :id="$data->id" />
                    <div x-data="{ copied: false, copyLink() { navigator.clipboard.writeText(window.location.href).then(() => { this.copied = true; setTimeout(() => this.copied = false, 1800); }); }, share() { if (navigator.share) { navigator.share({ title: document.title, url: window.location.href }).catch(() => {}); } } }">
                        <button type="button" @click="copyLink()"
                            class="tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-border tw-border-white/20 tw-bg-white/10 tw-px-4 tw-text-sm tw-font-bold tw-text-neutral-50 tw-backdrop-blur tw-transition-colors hover:tw-bg-white/20 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">
                            <x-lucide-copy class="tw-h-4 tw-w-4" />
                            <span x-text="copied ? '{{ __('Copied!') }}' : '{{ __('Share') }}'">{{ __('Share') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
        <div class="tw-grid tw-gap-8 lg:tw-grid-cols-[minmax(0,1fr)_360px]">
            <div class="tw-space-y-8">
                @if (count($galleryImages) > 1)
                    <section aria-labelledby="gallery-heading">
                        <h2 id="gallery-heading" class="tw-text-xl tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">{{ __('Gallery') }}</h2>
                        <div x-data="{ images: @js($galleryImages), active: 0 }" class="tw-mt-4">
                            <div class="tw-aspect-video tw-overflow-hidden tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-100 dark:tw-border-neutral-800">
                                <img :src="images[active]" src="{{ $heroImage }}" alt="{{ $data->name }}"
                                    width="1280" height="720" class="tw-h-full tw-w-full tw-object-contain">
                            </div>
                            <div class="tw-mt-3 tw-flex tw-gap-2 tw-overflow-x-auto tw-pb-1">
                                <template x-for="(image, index) in images" :key="image + '-' + index">
                                    <button type="button" @click="active = index"
                                        x-bind:aria-label="'{{ __('Show image') }} ' + (index + 1)"
                                        x-bind:class="active === index ? 'tw-ring-2 tw-ring-primary-500' : 'tw-ring-1 tw-ring-neutral-200 dark:tw-ring-neutral-700'"
                                        class="tw-h-16 tw-w-24 tw-shrink-0 tw-overflow-hidden tw-rounded-stub focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                                        <img :src="image" alt="" width="96" height="64" class="tw-h-full tw-w-full tw-object-cover">
                                    </button>
                                </template>
                            </div>
                        </div>
                    </section>
                @endif

                <section aria-labelledby="about-heading">
                    <h2 id="about-heading" class="tw-text-xl tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">{{ __('About this event') }}</h2>
                    <div class="tw-mt-4 tw-max-w-2xl tw-text-sm tw-leading-relaxed tw-text-neutral-600 dark:tw-text-neutral-300">
                        {!! nl2br(e((string) $data->description)) !!}
                    </div>

                    @if (count($tags) > 0)
                        <div class="tw-mt-5 tw-flex tw-flex-wrap tw-gap-2">
                            @foreach ($tags as $tag)
                                <x-badge tone="neutral">{{ $tag }}</x-badge>
                            @endforeach
                        </div>
                    @endif
                </section>

                @if ($data->organization)
                    @php
                        $organization = $data->organization;
                        $orgImage = $organization->image
                            ? url('images/upload/'.$organization->image)
                            : asset('web/bg/2.jpg');
                    @endphp
                    <x-card :title="__('Organized by')">
                        <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-4">
                            <img src="{{ $orgImage }}" alt="" width="72" height="72" loading="lazy"
                                class="tw-h-18 tw-w-18 tw-rounded-pill tw-object-cover">
                            <div class="tw-min-w-0 tw-flex-1">
                                <p class="tw-text-lg tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">
                                    {{ $organization->name ?: trim($organization->first_name.' '.$organization->last_name) }}
                                </p>
                                @if ($organization->bio)
                                    <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-400">{{ \Illuminate\Support\Str::limit(strip_tags($organization->bio), 220) }}</p>
                                @endif
                            </div>
                            <a href="{{ url('organization/'.$organization->id.'/'.\Illuminate\Support\Str::slug((string) ($organization->name ?: 'organizer'))) }}"
                                class="tw-inline-flex tw-items-center tw-gap-1.5 tw-text-sm tw-font-bold tw-text-accent-700 hover:tw-text-accent-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-accent-300">
                                {{ __('View organizer') }}
                                <x-lucide-arrow-up-right class="tw-h-4 tw-w-4" />
                            </a>
                        </div>
                    </x-card>
                @endif

                <section aria-labelledby="reviews-heading">
                    <div class="tw-flex tw-items-center tw-justify-between tw-gap-3">
                        <h2 id="reviews-heading" class="tw-text-xl tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">
                            {{ __('Reviews') }}
                            @if ($reviews->isNotEmpty())
                                <span class="tw-ml-1 tw-text-sm tw-font-medium tw-text-neutral-500 dark:tw-text-neutral-400">({{ $reviews->count() }})</span>
                            @endif
                        </h2>
                    </div>

                    @if ($reviews->isEmpty())
                        <x-empty-state class="tw-mt-4 tw-rounded-card tw-border tw-border-dashed tw-border-neutral-300 dark:tw-border-neutral-700"
                            title="{{ __('No reviews yet') }}"
                            description="{{ __('Be the first to share your experience after the event.') }}">
                            <x-slot:icon><x-lucide-star class="tw-h-6 tw-w-6" /></x-slot:icon>
                        </x-empty-state>
                    @else
                        <div class="tw-mt-4 tw-grid tw-gap-4 md:tw-grid-cols-2">
                            @foreach ($reviews as $review)
                                <x-card>
                                    <div class="tw-flex tw-items-start tw-gap-3">
                                        <div class="tw-flex tw-h-10 tw-w-10 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-pill tw-bg-primary-100 tw-text-sm tw-font-bold tw-text-primary-900 dark:tw-bg-primary-500/20 dark:tw-text-primary-300">
                                            {{ strtoupper(mb_substr((string) ($review->reviewer?->name ?? '?'), 0, 1)) }}
                                        </div>
                                        <div class="tw-min-w-0">
                                            <p class="tw-text-sm tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">
                                                {{ trim(($review->reviewer?->name ?? '').' '.($review->user?->last_name ?? '')) ?: __('Attendee') }}
                                            </p>
                                            <div class="tw-mt-1 tw-flex tw-gap-0.5" aria-label="{{ (int) $review->rate }} / 5">
                                                @for ($star = 1; $star <= 5; $star++)
                                                    <x-lucide-star class="{{ $star <= (int) $review->rate ? 'tw-fill-current tw-text-warning-500' : 'tw-text-neutral-300 dark:tw-text-neutral-700' }} tw-h-3.5 tw-w-3.5" />
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                    <p class="tw-mt-4 tw-text-sm tw-leading-relaxed tw-text-neutral-600 dark:tw-text-neutral-300">{{ $review->message }}</p>
                                </x-card>
                            @endforeach
                        </div>
                    @endif
                </section>

                @unless ($isOnline)
                    <x-card :title="__('Venue')">
                        <div class="tw-flex tw-flex-wrap tw-items-start tw-gap-4">
                            <div class="tw-flex tw-h-11 tw-w-11 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-pill tw-bg-accent-100 tw-text-accent-800 dark:tw-bg-accent-500/20 dark:tw-text-accent-200">
                                <x-lucide-map-pin class="tw-h-5 tw-w-5" />
                            </div>
                            <div class="tw-min-w-0 tw-flex-1">
                                <p class="tw-text-sm tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">{{ $locationLabel }}</p>
                                @if ($hasCoordinates)
                                    <p class="tw-mt-1 tw-font-mono tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $data->lat }}, {{ $data->lang }}</p>
                                    <a href="{{ $osmUrl }}" target="_blank" rel="noopener noreferrer"
                                        class="tw-mt-3 tw-inline-flex tw-items-center tw-gap-1.5 tw-text-sm tw-font-bold tw-text-accent-700 hover:tw-text-accent-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-accent-300">
                                        {{ __('View on OpenStreetMap') }}
                                        <x-lucide-external-link class="tw-h-4 tw-w-4" />
                                    </a>
                                @endif
                            </div>
                        </div>
                    </x-card>
                @endunless

                <x-card :title="__('Good to know')">
                    <div class="tw-divide-y tw-divide-neutral-100 dark:tw-divide-neutral-800">
                        <details class="tw-py-3">
                            <summary class="tw-cursor-pointer tw-text-sm tw-font-bold tw-text-ink-950 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-neutral-50">{{ __('When should I arrive?') }}</summary>
                            <p class="tw-mt-2 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-400">{{ __('Arrive 20–30 minutes before the start time so you can collect your ticket and find your seat.') }}</p>
                        </details>
                        <details class="tw-py-3">
                            <summary class="tw-cursor-pointer tw-text-sm tw-font-bold tw-text-ink-950 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-neutral-50">{{ __('Can I transfer my ticket?') }}</summary>
                            <p class="tw-mt-2 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-400">{{ __('Contact the organizer through their profile for questions about transfers and refunds.') }}</p>
                        </details>
                        <details class="tw-py-3">
                            <summary class="tw-cursor-pointer tw-text-sm tw-font-bold tw-text-ink-950 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-neutral-50">{{ __('Where do I find my ticket?') }}</summary>
                            <p class="tw-mt-2 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-400">{{ __('After checkout, your ticket and QR code are available under My Tickets in your account.') }}</p>
                        </details>
                    </div>
                </x-card>
            </div>

            <aside class="tw-space-y-6 lg:tw-sticky lg:tw-top-24 lg:tw-self-start">
                <x-card :title="__('Tickets on sale')" :subtitle="__('Choose an option to continue to checkout.')">
                    <x-tabs :tabs="[
                        ['label' => __('Paid').' ('.count($data->paid_ticket).')', 'slot' => 'paid'],
                        ['label' => __('Free').' ('.count($data->free_ticket).')', 'slot' => 'free'],
                    ]">
                        <x-slot:paid>
                            <div class="tw-space-y-4">
                                @forelse ($data->paid_ticket as $ticket)
                                    <x-event-ticket-offer :ticket="$ticket" :event="$data" :currency="$currency ?? null" />
                                @empty
                                    <x-empty-state title="{{ __('No Paid Tickets found') }}"
                                        description="{{ __('Check back later or switch to the free tab.') }}">
                                        <x-slot:icon><x-lucide-ticket class="tw-h-6 tw-w-6" /></x-slot:icon>
                                    </x-empty-state>
                                @endforelse
                            </div>
                        </x-slot:paid>

                        <x-slot:free>
                            <div class="tw-space-y-4">
                                @forelse ($data->free_ticket as $ticket)
                                    <x-event-ticket-offer :ticket="$ticket" :event="$data" :currency="$currency ?? null" />
                                @empty
                                    <x-empty-state title="{{ __('No Free Tickets found') }}"
                                        description="{{ __('Browse the paid tickets instead.') }}">
                                        <x-slot:icon><x-lucide-ticket class="tw-h-6 tw-w-6" /></x-slot:icon>
                                    </x-empty-state>
                                @endforelse
                            </div>
                        </x-slot:free>
                    </x-tabs>
                </x-card>

                <div class="tw-rounded-card tw-border tw-border-primary-100 tw-bg-primary-50/70 tw-p-5 dark:tw-border-primary-500/20 dark:tw-bg-primary-500/10">
                    <p class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-font-bold tw-text-primary-900 dark:tw-text-primary-200">
                        <x-lucide-info class="tw-h-4 tw-w-4" />
                        {{ __('Need help?') }}
                    </p>
                    <p class="tw-mt-2 tw-text-sm tw-leading-relaxed tw-text-primary-900/80 dark:tw-text-primary-200/80">
                        {{ __('Check the good-to-know questions, or contact the organizer before you buy.') }}
                    </p>
                </div>
            </aside>
        </div>
    </div>
@endsection
