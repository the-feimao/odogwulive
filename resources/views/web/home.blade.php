@extends('layouts.public')

@section('title', config('brand.name').' — '.config('brand.headline'))

@section('content')
    @php
        $brand = config('brand');
    @endphp

    <section class="tw-relative tw-isolate tw-overflow-hidden tw-bg-ink-950">
        <img src="{{ asset('web/slider/wide5.jpg') }}" alt="" width="1600" height="900" fetchpriority="high"
            class="tw-absolute tw-inset-0 tw-h-full tw-w-full tw-object-cover tw-object-center tw-opacity-45">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-r tw-from-ink-950 tw-via-ink-950/90 tw-to-ink-950/30"></div>

        <div class="tw-relative tw-mx-auto tw-max-w-content tw-px-4 tw-pb-16 tw-pt-20 md:tw-px-6 md:tw-pb-20 md:tw-pt-28">
            <div class="tw-max-w-3xl">
                <p class="tw-inline-flex tw-items-center tw-gap-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest tw-text-primary-400">
                    <span class="tw-h-1.5 tw-w-1.5 tw-rounded-pill tw-bg-primary-500" aria-hidden="true"></span>
                    {{ $brand['name'] }} · {{ __('Live experiences') }}
                </p>
                <h1 class="tw-mt-5 tw-text-4xl tw-font-extrabold tw-leading-[1.08] tw-tracking-tight tw-text-neutral-50 md:tw-text-6xl">
                    {{ $brand['headline'] }}
                </h1>
                <p class="tw-mt-5 tw-max-w-xl tw-text-base tw-leading-relaxed tw-text-neutral-300 md:tw-text-lg">
                    {{ $brand['about'] }}
                </p>
                <div class="tw-mt-8 tw-flex tw-flex-wrap tw-gap-3">
                    <a href="#events"
                        class="tw-inline-flex tw-h-12 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-6 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition-colors hover:tw-bg-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">
                        {{ __('Explore events') }}
                        <x-lucide-arrow-right class="tw-h-4 tw-w-4" />
                    </a>
                    <a href="#categories"
                        class="tw-inline-flex tw-h-12 tw-items-center tw-rounded-pill tw-border tw-border-white/30 tw-px-6 tw-text-sm tw-font-bold tw-text-neutral-50 tw-transition-colors hover:tw-border-white/60 hover:tw-bg-white/10 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">
                        {{ __('Browse categories') }}
                    </a>
                </div>
            </div>

            <div class="tw-mt-12 tw-max-w-3xl tw-rounded-card tw-border tw-border-white/10 tw-bg-neutral-0/95 tw-p-3 tw-shadow-card-hover dark:tw-bg-ink-900/95">
                <form method="GET" action="{{ url('/all-events') }}" class="tw-flex tw-flex-col tw-gap-2 sm:tw-flex-row sm:tw-items-center">
                    <label for="home-category" class="tw-sr-only">{{ __('Category') }}</label>
                    <div class="tw-flex tw-min-w-0 tw-flex-1 tw-items-center tw-gap-2 tw-px-3">
                        <x-lucide-search class="tw-h-5 tw-w-5 tw-shrink-0 tw-text-neutral-500" />
                        <select id="home-category" name="category"
                            class="tw-h-11 tw-min-w-0 tw-flex-1 tw-bg-transparent tw-text-sm tw-font-medium tw-text-ink-900 focus:tw-outline-none dark:tw-text-neutral-100">
                            <option value="">{{ __('Any category') }}</option>
                            @foreach ($publicCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit"
                        class="tw-inline-flex tw-h-11 tw-items-center tw-justify-center tw-gap-2 tw-rounded-pill tw-bg-ink-950 tw-px-6 tw-text-sm tw-font-bold tw-text-neutral-50 tw-transition-colors hover:tw-bg-ink-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500 dark:tw-bg-primary-500 dark:tw-text-ink-950 dark:hover:tw-bg-primary-400">
                        {{ __('Find events') }}
                        <x-lucide-arrow-right class="tw-h-4 tw-w-4" />
                    </button>
                </form>
            </div>
        </div>
    </section>

    <div class="tw-border-b tw-border-neutral-200 tw-bg-neutral-0 dark:tw-border-neutral-800 dark:tw-bg-ink-900">
        <div class="tw-mx-auto tw-grid tw-max-w-content tw-gap-6 tw-px-4 tw-py-8 md:tw-px-6 md:tw-grid-cols-3">
            <div class="tw-flex tw-items-start tw-gap-3">
                <span class="tw-flex tw-h-10 tw-w-10 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-pill tw-bg-primary-100 tw-text-primary-900 dark:tw-bg-primary-500/20 dark:tw-text-primary-300">
                    <x-lucide-ticket class="tw-h-5 tw-w-5" />
                </span>
                <div>
                    <p class="tw-text-sm tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Tickets made simple') }}</p>
                    <p class="tw-mt-0.5 tw-text-xs tw-leading-relaxed tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Choose, pay and get your ticket in one place.') }}</p>
                </div>
            </div>
            <div class="tw-flex tw-items-start tw-gap-3">
                <span class="tw-flex tw-h-10 tw-w-10 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-pill tw-bg-accent-100 tw-text-accent-800 dark:tw-bg-accent-500/20 dark:tw-text-accent-200">
                    <x-lucide-shield-check class="tw-h-5 tw-w-5" />
                </span>
                <div>
                    <p class="tw-text-sm tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Secure checkout') }}</p>
                    <p class="tw-mt-0.5 tw-text-xs tw-leading-relaxed tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Trusted payment methods and clear pricing.') }}</p>
                </div>
            </div>
            <div class="tw-flex tw-items-start tw-gap-3">
                <span class="tw-flex tw-h-10 tw-w-10 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-pill tw-bg-neutral-100 tw-text-ink-700 dark:tw-bg-neutral-800 dark:tw-text-neutral-200">
                    <x-lucide-sparkles class="tw-h-5 tw-w-5" />
                </span>
                <div>
                    <p class="tw-text-sm tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Memorable moments') }}</p>
                    <p class="tw-mt-0.5 tw-text-xs tw-leading-relaxed tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Great artists, venues and atmosphere.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <section id="events" class="tw-py-16 md:tw-py-20">
        <div class="tw-mx-auto tw-max-w-content tw-px-4 md:tw-px-6">
            <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
                <div>
                    <x-badge tone="accent">{{ __('What’s on') }}</x-badge>
                    <h2 class="tw-mt-3 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-ink-950 dark:tw-text-neutral-50 md:tw-text-4xl">
                        {{ __('Upcoming events') }}
                    </h2>
                    <p class="tw-mt-2 tw-max-w-xl tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-400">
                        {{ __('A shorter list, better chosen. Here is what is coming up next.') }}
                    </p>
                </div>
                <a href="{{ url('/all-events') }}"
                    class="tw-inline-flex tw-items-center tw-gap-1.5 tw-text-sm tw-font-bold tw-text-accent-700 hover:tw-text-accent-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-accent-300">
                    {{ __('See all events') }}
                    <x-lucide-arrow-up-right class="tw-h-4 tw-w-4" />
                </a>
            </div>

            @if ($publicEvents->isEmpty())
                <x-empty-state class="tw-mt-10 tw-rounded-card tw-border tw-border-dashed tw-border-neutral-300 dark:tw-border-neutral-700"
                    title="{{ __('No events on sale yet') }}"
                    description="{{ __('New events are added all the time. Check back soon or explore the full listing.') }}">
                    <x-slot:icon><x-lucide-calendar-days class="tw-h-6 tw-w-6" /></x-slot:icon>
                    <x-slot:action>
                        <a href="{{ url('/all-events') }}" class="tw-inline-flex tw-h-10 tw-items-center tw-rounded-pill tw-bg-primary-500 tw-px-5 tw-text-sm tw-font-bold tw-text-ink-950">
                            {{ __('Browse all events') }}
                        </a>
                    </x-slot:action>
                </x-empty-state>
            @else
                <div class="tw-mt-10 tw-grid tw-gap-6 sm:tw-grid-cols-2 lg:tw-grid-cols-3">
                    @foreach ($publicEvents as $event)
                        <x-public-event-card :event="$event" :prices="$eventPrices" :currency="$currency ?? null" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section id="categories" class="tw-border-y tw-border-neutral-200 tw-bg-neutral-0 tw-py-16 dark:tw-border-neutral-800 dark:tw-bg-ink-900 md:tw-py-20">
        <div class="tw-mx-auto tw-max-w-content tw-px-4 md:tw-px-6">
            <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
                <div>
                    <x-badge tone="primary">{{ __('Discover') }}</x-badge>
                    <h2 class="tw-mt-3 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-ink-950 dark:tw-text-neutral-50 md:tw-text-4xl">
                        {{ __('Explore by category') }}
                    </h2>
                </div>
                <a href="{{ url('/all-category') }}"
                    class="tw-inline-flex tw-items-center tw-gap-1.5 tw-text-sm tw-font-bold tw-text-accent-700 hover:tw-text-accent-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-accent-300">
                    {{ __('All categories') }}
                    <x-lucide-arrow-up-right class="tw-h-4 tw-w-4" />
                </a>
            </div>

            @if ($publicCategories->isEmpty())
                <p class="tw-mt-8 tw-rounded-card tw-border tw-border-dashed tw-border-neutral-300 tw-p-6 tw-text-sm tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                    {{ __('Categories will appear here once an organizer publishes an event.') }}
                </p>
            @else
                <div class="tw-mt-8 tw-grid tw-grid-cols-2 tw-gap-4 md:tw-grid-cols-3 lg:tw-grid-cols-6">
                    @foreach ($publicCategories as $category)
                        @php
                            $categoryImage = $category->image
                                ? url('images/upload/'.$category->image)
                                : asset('web/bg/'.(($loop->iteration % 3) + 1).'.jpg');
                            $count = (int) $categoryCounts->get($category->id, 0);
                        @endphp
                        <a href="{{ url('/events-category/'.$category->id.'/'.\Illuminate\Support\Str::slug($category->name)) }}"
                            class="tw-group tw-relative tw-block tw-overflow-hidden tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-100 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-800"
                            aria-label="{{ $category->name }} — {{ $count }} {{ \Illuminate\Support\Str::plural('event', $count) }}">
                            <img src="{{ $categoryImage }}" alt="" loading="lazy" width="400" height="300"
                                class="tw-h-full tw-w-full tw-object-cover tw-transition-transform tw-duration-300 group-hover:tw-scale-105">
                            <span class="tw-absolute tw-inset-0 tw-bg-gradient-to-t tw-from-ink-950/90 tw-via-ink-950/20 tw-to-transparent"></span>
                            <span class="tw-absolute tw-inset-x-0 tw-bottom-0 tw-p-4">
                                <span class="tw-block tw-text-sm tw-font-bold tw-text-neutral-50">{{ $category->name }}</span>
                                <span class="tw-mt-0.5 tw-block tw-text-2xs tw-font-semibold tw-text-primary-300">
                                    {{ $count }} {{ \Illuminate\Support\Str::plural('event', $count) }}
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    @if ($publicOrganizers->isNotEmpty())
        <section class="tw-py-14">
            <div class="tw-mx-auto tw-max-w-content tw-px-4 md:tw-px-6">
                <h2 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-950 dark:tw-text-neutral-50">
                    {{ __('Hosted by organizers you can trust') }}
                </h2>
                <p class="tw-mt-2 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-400">
                    {{ __('The people behind the events on this page.') }}
                </p>
                <div class="tw-mt-6 tw-grid tw-gap-4 sm:tw-grid-cols-3">
                    @foreach ($publicOrganizers as $organizer)
                        @php
                            $organizerImage = $organizer->image
                                ? url('images/upload/'.$organizer->image)
                                : asset('images/upload/defaultuser.png');
                            $organizerEvents = $publicEvents->where('user_id', $organizer->id)->count();
                        @endphp
                        <x-card class="tw-transition-shadow hover:tw-shadow-card-hover">
                            <div class="tw-flex tw-items-center tw-gap-3">
                                <img src="{{ $organizerImage }}" alt="" loading="lazy" width="48" height="48"
                                    class="tw-h-12 tw-w-12 tw-shrink-0 tw-rounded-pill tw-object-cover tw-ring-2 tw-ring-neutral-100 dark:tw-ring-neutral-800">
                                <div class="tw-min-w-0">
                                    <p class="tw-truncate tw-text-sm tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ $organizer->name }}</p>
                                    <p class="tw-mt-0.5 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                        {{ $organizerEvents }} {{ \Illuminate\Support\Str::plural('event', $organizerEvents) }}
                                    </p>
                                </div>
                            </div>
                        </x-card>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="tw-bg-ink-950 tw-py-16 md:tw-py-20">
        <div class="tw-mx-auto tw-max-w-content tw-px-4 md:tw-px-6">
            <div class="tw-max-w-xl">
                <p class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest tw-text-primary-400">{{ __('How it works') }}</p>
                <h2 class="tw-mt-3 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-neutral-50 md:tw-text-4xl">
                    {{ __('From discovery to the gate, without the guesswork.') }}
                </h2>
            </div>

            <div class="tw-mt-10 tw-grid tw-gap-6 md:tw-grid-cols-3">
                <div class="tw-rounded-card tw-border tw-border-neutral-800 tw-p-6">
                    <span class="tw-text-xs tw-font-bold tw-tracking-widest tw-text-primary-500">01</span>
                    <x-lucide-search class="tw-mt-4 tw-h-6 tw-w-6 tw-text-neutral-300" />
                    <h3 class="tw-mt-4 tw-text-lg tw-font-bold tw-text-neutral-50">{{ __('Find your event') }}</h3>
                    <p class="tw-mt-2 tw-text-sm tw-leading-relaxed tw-text-neutral-400">{{ __('Filter by date, category and location to find the right night out.') }}</p>
                </div>
                <div class="tw-rounded-card tw-border tw-border-neutral-800 tw-p-6">
                    <span class="tw-text-xs tw-font-bold tw-tracking-widest tw-text-primary-500">02</span>
                    <x-lucide-ticket class="tw-mt-4 tw-h-6 tw-w-6 tw-text-neutral-300" />
                    <h3 class="tw-mt-4 tw-text-lg tw-font-bold tw-text-neutral-50">{{ __('Pick your tickets') }}</h3>
                    <p class="tw-mt-2 tw-text-sm tw-leading-relaxed tw-text-neutral-400">{{ __('See the full price up front, including tax, before you pay.') }}</p>
                </div>
                <div class="tw-rounded-card tw-border tw-border-neutral-800 tw-p-6">
                    <span class="tw-text-xs tw-font-bold tw-tracking-widest tw-text-primary-500">03</span>
                    <x-lucide-shield-check class="tw-mt-4 tw-h-6 tw-w-6 tw-text-neutral-300" />
                    <h3 class="tw-mt-4 tw-text-lg tw-font-bold tw-text-neutral-50">{{ __('Scan and go') }}</h3>
                    <p class="tw-mt-2 tw-text-sm tw-leading-relaxed tw-text-neutral-400">{{ __('Your QR ticket is ready in your account when you arrive.') }}</p>
                </div>
            </div>
        </div>
    </section>

    <section class="tw-py-16 md:tw-py-20">
        <div class="tw-mx-auto tw-max-w-content tw-px-4 md:tw-px-6">
            <div class="tw-overflow-hidden tw-rounded-card tw-bg-primary-500 tw-p-8 md:tw-p-12">
                <div class="tw-grid tw-gap-8 md:tw-grid-cols-[1.4fr_1fr] md:tw-items-center">
                    <div>
                        <p class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest tw-text-primary-900">{{ __('For organizers') }}</p>
                        <h2 class="tw-mt-3 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-ink-950 md:tw-text-4xl">
                            {{ __('Put your event in front of the right people.') }}
                        </h2>
                        <p class="tw-mt-3 tw-max-w-xl tw-text-sm tw-leading-relaxed tw-text-ink-800">
                            {{ __('Create tickets, manage orders and track entry from one place. Join the organizers already using the platform.') }}
                        </p>
                    </div>
                    <div class="tw-flex tw-flex-wrap tw-gap-3 md:tw-justify-end">
                        <a href="{{ url('/user/org-register') }}"
                            class="tw-inline-flex tw-h-12 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-ink-950 tw-px-6 tw-text-sm tw-font-bold tw-text-neutral-50 tw-transition-colors hover:tw-bg-ink-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-neutral-900">
                            {{ __('Become an organizer') }}
                            <x-lucide-arrow-right class="tw-h-4 tw-w-4" />
                        </a>
                        <a href="{{ url('/contact') }}"
                            class="tw-inline-flex tw-h-12 tw-items-center tw-rounded-pill tw-border tw-border-ink-950/20 tw-px-6 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition-colors hover:tw-bg-ink-950/5 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-neutral-900">
                            {{ __('Talk to us') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
