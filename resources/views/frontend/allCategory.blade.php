@extends('layouts.public')

@section('title', __('Categories').' — '.config('brand.name'))

@section('content')
    <section class="tw-relative tw-overflow-hidden tw-bg-ink-950">
        <img src="{{ asset('web/bg/3.jpg') }}" alt="" width="1600" height="900"
            class="tw-absolute tw-inset-0 tw-h-full tw-w-full tw-object-cover tw-opacity-35">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-r tw-from-ink-950 tw-via-ink-950/85 tw-to-ink-950/20"></div>

        <div class="tw-relative tw-mx-auto tw-max-w-content tw-px-4 tw-py-16 md:tw-px-6 md:tw-py-24">
            <x-badge tone="primary">{{ __('Discover') }}</x-badge>
            <h1 class="tw-mt-4 tw-max-w-3xl tw-text-4xl tw-font-extrabold tw-leading-tight tw-tracking-tight tw-text-neutral-50 md:tw-text-5xl">
                {{ __('Explore every kind of event.') }}
            </h1>
            <p class="tw-mt-4 tw-max-w-xl tw-text-sm tw-leading-relaxed tw-text-neutral-300 md:tw-text-base">
                {{ __('From live music to community gatherings, pick a category and see what is coming up.') }}
            </p>
        </div>
    </section>

    <section class="tw-py-16 md:tw-py-20">
        <div class="tw-mx-auto tw-max-w-content tw-px-4 md:tw-px-6">
            <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
                <div>
                    <p class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-widest tw-text-accent-700 dark:tw-text-accent-300">{{ __('Featured category') }}</p>
                    <h2 class="tw-mt-2 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-ink-950 dark:tw-text-neutral-50 md:tw-text-4xl">
                        {{ __('Browse by category') }}
                    </h2>
                </div>
                <a href="{{ url('/all-events') }}"
                    class="tw-inline-flex tw-items-center tw-gap-1.5 tw-text-sm tw-font-bold tw-text-accent-700 hover:tw-text-accent-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-accent-300">
                    {{ __('See all events') }}
                    <x-lucide-arrow-up-right class="tw-h-4 tw-w-4" />
                </a>
            </div>

            @if ($data->isEmpty())
                <x-empty-state class="tw-mt-10 tw-rounded-card tw-border tw-border-dashed tw-border-neutral-300 dark:tw-border-neutral-700"
                    title="{{ __('No categories yet') }}"
                    description="{{ __('Categories will appear here once an organizer publishes an event.') }}">
                    <x-slot:icon><x-lucide-layout-grid class="tw-h-6 tw-w-6" /></x-slot:icon>
                </x-empty-state>
            @else
                <div class="tw-mt-10 tw-grid tw-gap-5 sm:tw-grid-cols-2 lg:tw-grid-cols-3">
                    @foreach ($data as $item)
                        @php
                            $image = $item->image
                                ? url('images/upload/'.$item->image)
                                : asset('web/bg/'.(($loop->iteration % 3) + 1).'.jpg');
                        @endphp
                        <a href="{{ url('events-category/'.$item->id.'/'.\Illuminate\Support\Str::slug($item->name)) }}"
                            class="tw-group tw-relative tw-block tw-overflow-hidden tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-100 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-800"
                            aria-label="{{ $item->name }}">
                            <div class="tw-aspect-[4/3]">
                                <img src="{{ $image }}" alt="" loading="lazy" width="600" height="450"
                                    class="tw-h-full tw-w-full tw-object-cover tw-transition-transform tw-duration-300 group-hover:tw-scale-105">
                            </div>
                            <span class="tw-absolute tw-inset-0 tw-bg-gradient-to-t tw-from-ink-950/90 tw-via-ink-950/20 tw-to-transparent"></span>
                            <span class="tw-absolute tw-inset-x-0 tw-bottom-0 tw-flex tw-items-center tw-justify-between tw-gap-2 tw-p-5">
                                <span class="tw-text-lg tw-font-bold tw-text-neutral-50">{{ $item->name }}</span>
                                <x-lucide-arrow-up-right class="tw-h-5 tw-w-5 tw-shrink-0 tw-text-primary-400" />
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
@endsection
