@extends('layouts.public')

@section('title', __('Events').' — '.config('brand.name'))

@section('content')
    @php
        $heading = isset($category)
            ? $category->name.' '.__('events')
            : (isset($type) ? ucfirst($type).' '.__('events') : __('All events'));
    @endphp

    <section class="tw-border-b tw-border-neutral-200 tw-bg-neutral-0 dark:tw-border-neutral-800 dark:tw-bg-ink-900">
        <div class="tw-mx-auto tw-max-w-content tw-px-4 tw-py-12 md:tw-px-6 md:tw-py-14">
            <x-badge tone="accent">{{ __('Explore') }}</x-badge>
            <h1 class="tw-mt-3 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-ink-950 dark:tw-text-neutral-50 md:tw-text-4xl">
                {{ $heading }}
            </h1>
            <p class="tw-mt-2 tw-max-w-2xl tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-400">
                {{ __('Filter the events on this page instantly, or apply the filters to reload the full listing.') }}
            </p>

            @if (isset($category) || isset($type) || (isset($chip) && count($chip) > 0))
                <div class="tw-mt-5 tw-flex tw-flex-wrap tw-items-center tw-gap-2">
                    @isset($category)
                        <a href="{{ url('/all-events') }}"
                            class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-pill tw-bg-primary-100 tw-px-3 tw-py-1.5 tw-text-xs tw-font-bold tw-text-primary-900 dark:tw-bg-primary-500/20 dark:tw-text-primary-300">
                            {{ $category->name }}
                            <x-lucide-x class="tw-h-3.5 tw-w-3.5" />
                        </a>
                    @endisset

                    @isset($type)
                        <a href="{{ url('/event-type/all') }}"
                            class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-pill tw-bg-primary-100 tw-px-3 tw-py-1.5 tw-text-xs tw-font-bold tw-text-primary-900 dark:tw-bg-primary-500/20 dark:tw-text-primary-300">
                            {{ ucfirst($type) }}
                            <x-lucide-x class="tw-h-3.5 tw-w-3.5" />
                        </a>
                    @endisset

                    @if (isset($chip) && count($chip) > 0)
                        @foreach ($chip as $key => $value)
                            @php
                                $clearUrl = $key === 'type' ? url('/event-type/all') : url('/all-events');
                            @endphp
                            <a href="{{ $clearUrl }}"
                                class="tw-inline-flex tw-items-center tw-gap-1.5 tw-rounded-pill tw-bg-neutral-100 tw-px-3 tw-py-1.5 tw-text-xs tw-font-bold tw-text-neutral-700 dark:tw-bg-neutral-800 dark:tw-text-neutral-200">
                                {{ is_scalar($value) ? $value : $key }}
                                <x-lucide-x class="tw-h-3.5 tw-w-3.5" />
                            </a>
                        @endforeach
                    @endif
                </div>
            @endif
        </div>
    </section>

    <div x-data='{
        search: "",
        category: @js(request("category", "")),
        type: @js(request("type", isset($type) ? $type : "")),
        duration: @js(request("duration", "")),
        date: @js(request("date", "")),
        sort: "date",
        loading: false,
        visibleCount: {{ count($events) }},
        timer: null,
        init() {
            this.apply(false);
            this.$watch("search", () => this.apply());
            this.$watch("category", () => this.apply());
            this.$watch("type", () => this.apply());
            this.$watch("duration", () => this.apply());
            this.$watch("date", () => this.apply());
            this.$watch("sort", () => this.apply(false));
        },
        apply(withLoading = true) {
            if (withLoading) {
                this.loading = true;
            }
            clearTimeout(this.timer);
            this.timer = setTimeout(() => {
                const cards = Array.from(this.$root.querySelectorAll("[data-event-card]"));
                let shown = 0;
                cards.forEach((card) => {
                    const visible = this.matches(card);
                    card.style.display = visible ? "" : "none";
                    if (visible) shown++;
                });
                this.visibleCount = shown;
                this.loading = false;
            }, withLoading ? 160 : 0);
        },
        matches(card) {
            const needle = this.search.trim().toLowerCase();
            const name = (card.dataset.name || "").toLowerCase();
            const category = (card.dataset.category || "").toLowerCase();

            if (needle && !name.includes(needle) && !category.includes(needle)) {
                return false;
            }
            if (this.category && card.dataset.categoryId !== this.category) {
                return false;
            }
            if (this.type && card.dataset.type !== this.type) {
                return false;
            }
            if (this.duration === "date" && this.date && card.dataset.date !== this.date) {
                return false;
            }
            if (this.duration && this.duration !== "date") {
                const rawDate = card.dataset.date;
                if (rawDate) {
                    const eventDate = new Date(rawDate + "T00:00:00");
                    const today = new Date();
                    today.setHours(0, 0, 0, 0);
                    const days = Math.round((eventDate - today) / 86400000);
                    if (this.duration === "Today" && days !== 0) return false;
                    if (this.duration === "Tomorrow" && days !== 1) return false;
                    if (this.duration === "ThisWeek" && (days < 0 || days > 7)) return false;
                }
            }
            return true;
        },
        rank(card) {
            const cards = Array.from(this.$root.querySelectorAll("[data-event-card]"));
            if (this.sort === "name-asc") {
                return cards.slice().sort((a, b) => (a.dataset.name || "").localeCompare(b.dataset.name || "")).indexOf(card);
            }
            if (this.sort === "name-desc") {
                return cards.slice().sort((a, b) => (b.dataset.name || "").localeCompare(a.dataset.name || "")).indexOf(card);
            }
            if (this.sort === "date-desc") {
                return cards.slice().sort((a, b) => (b.dataset.date || "").localeCompare(a.dataset.date || "")).indexOf(card);
            }
            return cards.indexOf(card);
        }
    }'>
        <div class="tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
            <form method="GET" action="{{ url('/all-events') }}" @submit="loading = true"
                class="tw-grid tw-gap-3 tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-p-4 tw-shadow-card dark:tw-border-neutral-800 dark:tw-bg-ink-900 md:tw-grid-cols-[1.4fr_1fr_1fr_1fr_auto]">
                <div>
                    <label for="listing-search" class="tw-mb-1.5 tw-block tw-text-xs tw-font-bold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Find on this page') }}</label>
                    <div class="tw-relative">
                        <x-lucide-search class="tw-pointer-events-none tw-absolute tw-left-3 tw-top-1/2 tw-h-4 tw-w-4 tw--translate-y-1/2 tw-text-neutral-400" />
                        <input id="listing-search" type="search" x-model="search" placeholder="{{ __('Event or category') }}"
                            class="tw-h-10 tw-w-full tw-rounded-stub tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-pl-9 tw-pr-3 tw-text-sm tw-text-ink-900 placeholder:tw-text-neutral-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-bg-ink-950 dark:tw-text-neutral-100">
                    </div>
                </div>

                <div>
                    <label for="listing-category" class="tw-mb-1.5 tw-block tw-text-xs tw-font-bold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Category') }}</label>
                    <select id="listing-category" name="category" x-model="category"
                        class="tw-h-10 tw-w-full tw-rounded-stub tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-px-3 tw-text-sm tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-bg-ink-950 dark:tw-text-neutral-100">
                        <option value="">{{ __('All categories') }}</option>
                        @foreach ($listingCategories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="listing-type" class="tw-mb-1.5 tw-block tw-text-xs tw-font-bold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Type') }}</label>
                    <select id="listing-type" name="type" x-model="type"
                        class="tw-h-10 tw-w-full tw-rounded-stub tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-px-3 tw-text-sm tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-bg-ink-950 dark:tw-text-neutral-100">
                        <option value="">{{ __('All types') }}</option>
                        <option value="online">{{ __('Online') }}</option>
                        <option value="offline">{{ __('Venue') }}</option>
                    </select>
                </div>

                <div>
                    <label for="listing-duration" class="tw-mb-1.5 tw-block tw-text-xs tw-font-bold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('When') }}</label>
                    <select id="listing-duration" name="duration" x-model="duration"
                        class="tw-h-10 tw-w-full tw-rounded-stub tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-px-3 tw-text-sm tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-bg-ink-950 dark:tw-text-neutral-100">
                        <option value="">{{ __('Any time') }}</option>
                        <option value="Today">{{ __('Today') }}</option>
                        <option value="Tomorrow">{{ __('Tomorrow') }}</option>
                        <option value="ThisWeek">{{ __('This week') }}</option>
                        <option value="date">{{ __('Choose a date') }}</option>
                    </select>
                </div>

                <div class="tw-flex tw-items-end tw-gap-2">
                    <button type="submit"
                        class="tw-inline-flex tw-h-10 tw-flex-1 tw-items-center tw-justify-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition-colors hover:tw-bg-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600 md:tw-flex-none">
                        <x-lucide-filter class="tw-h-4 tw-w-4" />
                        {{ __('Apply') }}
                    </button>
                </div>

                <div x-show="duration === 'date'" class="md:tw-col-span-4">
                    <label for="listing-date" class="tw-mb-1.5 tw-block tw-text-xs tw-font-bold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Date') }}</label>
                    <input id="listing-date" type="date" name="date" x-model="date"
                        class="tw-h-10 tw-w-full tw-max-w-xs tw-rounded-stub tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-px-3 tw-text-sm tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-bg-ink-950 dark:tw-text-neutral-100">
                </div>
            </form>

            <div class="tw-mt-8 tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3">
                <p class="tw-text-sm tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">
                    {{ __('Showing') }} <span x-text="visibleCount">{{ count($events) }}</span> {{ __('of') }} {{ count($events) }} {{ __('events') }}
                </p>
                <div class="tw-flex tw-items-center tw-gap-2">
                    <label for="listing-sort" class="tw-text-xs tw-font-bold tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Sort') }}</label>
                    <select id="listing-sort" x-model="sort"
                        class="tw-h-9 tw-rounded-stub tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-px-3 tw-text-xs tw-font-semibold tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-bg-ink-950 dark:tw-text-neutral-100">
                        <option value="date">{{ __('Soonest first') }}</option>
                        <option value="date-desc">{{ __('Latest first') }}</option>
                        <option value="name-asc">{{ __('Name A–Z') }}</option>
                        <option value="name-desc">{{ __('Name Z–A') }}</option>
                    </select>
                </div>
            </div>

            <template x-if="loading">
                <div class="tw-mt-6 tw-grid tw-gap-6 sm:tw-grid-cols-2 lg:tw-grid-cols-3" aria-hidden="true">
                    @for ($i = 0; $i < 6; $i++)
                        <div class="tw-overflow-hidden tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 dark:tw-border-neutral-800 dark:tw-bg-ink-900">
                            <div class="tw-aspect-video tw-animate-pulse tw-bg-neutral-200 dark:tw-bg-neutral-800"></div>
                            <div class="tw-space-y-3 tw-p-5 tw-animate-pulse">
                                <div class="tw-h-3 tw-w-1/3 tw-rounded tw-bg-neutral-200 dark:tw-bg-neutral-800"></div>
                                <div class="tw-h-5 tw-w-4/5 tw-rounded tw-bg-neutral-200 dark:tw-bg-neutral-800"></div>
                                <div class="tw-h-3 tw-w-2/3 tw-rounded tw-bg-neutral-200 dark:tw-bg-neutral-800"></div>
                                <div class="tw-h-10 tw-rounded-pill tw-bg-neutral-200 dark:tw-bg-neutral-800"></div>
                            </div>
                        </div>
                    @endfor
                </div>
            </template>

            <div x-show="!loading" class="tw-mt-6">
                @if (count($events) === 0)
                    <x-empty-state class="tw-rounded-card tw-border tw-border-dashed tw-border-neutral-300 dark:tw-border-neutral-700"
                        title="{{ __('No Events found') }}"
                        description="{{ __('Try a different category, type or date.') }}">
                        <x-slot:icon><x-lucide-calendar-days class="tw-h-6 tw-w-6" /></x-slot:icon>
                    </x-empty-state>
                @else
                    <div class="tw-grid tw-gap-6 sm:tw-grid-cols-2 lg:tw-grid-cols-3">
                        @foreach ($events as $item)
                            <div data-event-card
                                data-name="{{ $item->name }}"
                                data-category="{{ $item->category?->name }}"
                                data-category-id="{{ $item->category_id }}"
                                data-type="{{ $item->type }}"
                                data-date="{{ $item->start_time?->format('Y-m-d') }}"
                                x-bind:style="`order: ${rank($el)}`">
                                <x-public-event-card :event="$item" :prices="$eventPrices" :currency="$currency ?? null" />
                            </div>
                        @endforeach
                    </div>

                    <p x-show="visibleCount === 0"
                        class="tw-mt-8 tw-rounded-card tw-border tw-border-dashed tw-border-neutral-300 tw-p-8 tw-text-center tw-text-sm tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                        {{ __('No events match these filters on this page.') }}
                    </p>
                @endif
            </div>
        </div>
    </div>
@endsection
