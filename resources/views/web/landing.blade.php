@extends('layouts.public')

@section('title', __('Events').' — '.config('brand.name'))

@section('content')
    <section class="tw-relative tw-overflow-hidden tw-bg-ink-950">
        <img src="{{ asset('web/slider/wide1.jpg') }}" alt="" width="1600" height="900"
            class="tw-absolute tw-inset-0 tw-h-full tw-w-full tw-object-cover tw-opacity-35">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-r tw-from-ink-950 tw-via-ink-950/85 tw-to-ink-950/20"></div>

        <div class="tw-relative tw-mx-auto tw-max-w-content tw-px-4 tw-py-16 md:tw-px-6 md:tw-py-24">
            <x-badge tone="primary">{{ __('Event guide') }}</x-badge>
            <h1 class="tw-mt-4 tw-max-w-3xl tw-text-4xl tw-font-extrabold tw-leading-tight tw-tracking-tight tw-text-neutral-50 md:tw-text-5xl">
                {{ __('Find something worth showing up for.') }}
            </h1>
            <p class="tw-mt-4 tw-max-w-xl tw-text-sm tw-leading-relaxed tw-text-neutral-300 md:tw-text-base">
                {{ __('Browse the events on sale now, or open the full listing to filter by category, type and date.') }}
            </p>

            <form method="GET" action="{{ url('/all-events') }}"
                class="tw-mt-8 tw-grid tw-max-w-3xl tw-gap-2 tw-rounded-card tw-border tw-border-white/10 tw-bg-neutral-0/95 tw-p-3 sm:tw-grid-cols-[1fr_1fr_auto] dark:tw-bg-ink-900/95">
                <div>
                    <label for="landing-category" class="tw-sr-only">{{ __('Category') }}</label>
                    <select id="landing-category" name="category"
                        class="tw-h-11 tw-w-full tw-rounded-stub tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-px-3 tw-text-sm tw-font-medium tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-bg-ink-900 dark:tw-text-neutral-100">
                        <option value="">{{ __('Any category') }}</option>
                        @foreach ($listingCategories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="landing-type" class="tw-sr-only">{{ __('Event type') }}</label>
                    <select id="landing-type" name="type"
                        class="tw-h-11 tw-w-full tw-rounded-stub tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-px-3 tw-text-sm tw-font-medium tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-bg-ink-900 dark:tw-text-neutral-100">
                        <option value="">{{ __('Any type') }}</option>
                        <option value="online">{{ __('Online') }}</option>
                        <option value="offline">{{ __('Venue') }}</option>
                    </select>
                </div>
                <button type="submit"
                    class="tw-inline-flex tw-h-11 tw-items-center tw-justify-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-6 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition-colors hover:tw-bg-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">
                    {{ __('Browse events') }}
                    <x-lucide-arrow-right class="tw-h-4 tw-w-4" />
                </button>
            </form>
        </div>
    </section>

    <section class="tw-py-16 md:tw-py-20">
        <div class="tw-mx-auto tw-max-w-content tw-px-4 md:tw-px-6">
            <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
                <div>
                    <p class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest tw-text-accent-700 dark:tw-text-accent-300">{{ __('On sale now') }}</p>
                    <h2 class="tw-mt-2 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-ink-950 dark:tw-text-neutral-50 md:tw-text-4xl">
                        {{ __('Upcoming events') }}
                    </h2>
                </div>
                <a href="{{ url('/all-events') }}"
                    class="tw-inline-flex tw-items-center tw-gap-1.5 tw-text-sm tw-font-bold tw-text-accent-700 hover:tw-text-accent-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-accent-300">
                    {{ __('Open full listing') }}
                    <x-lucide-arrow-up-right class="tw-h-4 tw-w-4" />
                </a>
            </div>

            @if ($events->isEmpty())
                <x-empty-state class="tw-mt-10 tw-rounded-card tw-border tw-border-dashed tw-border-neutral-300 dark:tw-border-neutral-700"
                    title="{{ __('No events on sale yet') }}"
                    description="{{ __('Check the full listing again soon — new events are added all the time.') }}">
                    <x-slot:icon><x-lucide-calendar-days class="tw-h-6 tw-w-6" /></x-slot:icon>
                </x-empty-state>
            @else
                <div class="tw-mt-10 tw-grid tw-gap-6 sm:tw-grid-cols-2 lg:tw-grid-cols-3">
                    @foreach ($events as $event)
                        <x-public-event-card :event="$event" :prices="$eventPrices" :currency="$currency ?? null" />
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
