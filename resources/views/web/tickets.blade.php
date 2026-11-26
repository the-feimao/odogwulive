@extends('layouts.public')

@section('title', __('Choose a Ticket').' — '.$data->name.' — '.config('brand.name'))

@section('content')
    @php
        $eventImage = $data->image
            ? url('images/upload/'.$data->image)
            : asset('web/bg/1.jpg');
        $isOnline = $data->type === 'online';
        $joinUrl = $isOnline && $data->event_link && preg_match('#^https?://#i', $data->event_link)
            ? $data->event_link
            : null;
        $locationLabel = $isOnline ? __('Online event') : ($data->address ?: __('Venue to be announced'));
        $eventUrl = url('/event/'.$data->id.'/'.\Illuminate\Support\Str::slug($data->name));
    @endphp

    <section class="tw-relative tw-overflow-hidden tw-bg-ink-950">
        <img src="{{ $eventImage }}" alt="" width="1600" height="900"
            class="tw-absolute tw-inset-0 tw-h-full tw-w-full tw-object-cover tw-opacity-30">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-r tw-from-ink-950 tw-via-ink-950/90 tw-to-ink-950/30"></div>
        <div class="tw-relative tw-mx-auto tw-max-w-content tw-px-4 tw-py-12 md:tw-px-6 md:tw-py-16">
            <nav aria-label="{{ __('Breadcrumb') }}" class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-text-neutral-400">
                <a href="{{ url('/') }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ __('Home') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <a href="{{ $eventUrl }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ $data->name }}</a>
            </nav>
            <x-badge tone="primary" class="tw-mt-5">{{ __('Ticket selection') }}</x-badge>
            <h1 class="tw-mt-3 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-neutral-50 md:tw-text-4xl">
                {{ __('Choose a Ticket') }}
            </h1>
            <p class="tw-mt-2 tw-max-w-2xl tw-text-sm tw-text-neutral-300 md:tw-text-base">
                {{ $data->name }} · {{ $data->start_time?->format('l, F j, Y · g:i A') }} · {{ $locationLabel }}
            </p>
        </div>
    </section>

    <div class="tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
        <div class="tw-grid tw-gap-8 lg:tw-grid-cols-[minmax(0,1fr)_300px]">
            <div>
                <x-card :title="__('Available options')" :subtitle="__('Select a ticket to continue to checkout.')">
                    <x-tabs :tabs="[
                        ['label' => __('Paid').' ('.count($data->paid_ticket).')', 'slot' => 'paid'],
                        ['label' => __('Free').' ('.count($data->free_ticket).')', 'slot' => 'free'],
                    ]">
                        <x-slot:paid>
                            <div class="tw-grid tw-gap-4 md:tw-grid-cols-2">
                                @forelse ($data->paid_ticket as $ticket)
                                    <x-event-ticket-offer :ticket="$ticket" :event="$data" :currency="$currency ?? null" />
                                @empty
                                    <x-empty-state class="md:tw-col-span-2 tw-rounded-card tw-border tw-border-dashed tw-border-neutral-300 dark:tw-border-neutral-700"
                                        title="{{ __('No Paid Tickets found') }}"
                                        description="{{ __('Check the free tab or return to the event page.') }}">
                                        <x-slot:icon><x-lucide-ticket class="tw-h-6 tw-w-6" /></x-slot:icon>
                                    </x-empty-state>
                                @endforelse
                            </div>
                        </x-slot:paid>

                        <x-slot:free>
                            <div class="tw-grid tw-gap-4 md:tw-grid-cols-2">
                                @forelse ($data->free_ticket as $ticket)
                                    <x-event-ticket-offer :ticket="$ticket" :event="$data" :currency="$currency ?? null" />
                                @empty
                                    <x-empty-state class="md:tw-col-span-2 tw-rounded-card tw-border tw-border-dashed tw-border-neutral-300 dark:tw-border-neutral-700"
                                        title="{{ __('No Free Tickets found') }}"
                                        description="{{ __('Browse the paid options instead.') }}">
                                        <x-slot:icon><x-lucide-ticket class="tw-h-6 tw-w-6" /></x-slot:icon>
                                    </x-empty-state>
                                @endforelse
                            </div>
                        </x-slot:free>
                    </x-tabs>
                </x-card>
            </div>

            <aside class="tw-space-y-6 lg:tw-sticky lg:tw-top-24 lg:tw-self-start">
                <x-card :title="__('Event summary')">
                    <img src="{{ $eventImage }}" alt="{{ $data->name }}" width="400" height="240" loading="lazy"
                        class="tw-aspect-video tw-w-full tw-rounded-stub tw-object-cover">
                    <dl class="tw-mt-4 tw-space-y-3 tw-text-sm">
                        <div class="tw-flex tw-gap-2">
                            <x-lucide-calendar-days class="tw-mt-0.5 tw-h-4 tw-w-4 tw-shrink-0 tw-text-primary-600" />
                            <div>
                                <dt class="tw-sr-only">{{ __('Date') }}</dt>
                                <dd class="tw-font-semibold tw-text-ink-950 dark:tw-text-neutral-50">{{ $data->start_time?->format('l, F j, Y') }}</dd>
                                <dd class="tw-text-neutral-500 dark:tw-text-neutral-400">{{ $data->start_time?->format('g:i A') }}</dd>
                            </div>
                        </div>
                        <div class="tw-flex tw-gap-2">
                            <x-lucide-map-pin class="tw-mt-0.5 tw-h-4 tw-w-4 tw-shrink-0 tw-text-primary-600" />
                            <div>
                                <dt class="tw-sr-only">{{ __('Location') }}</dt>
                                <dd class="tw-font-semibold tw-text-ink-950 dark:tw-text-neutral-50">{{ $locationLabel }}</dd>
                                @if ($joinUrl)
                                    <a href="{{ $joinUrl }}" target="_blank" rel="noopener noreferrer"
                                        class="tw-mt-2 tw-inline-flex tw-items-center tw-gap-1 tw-text-xs tw-font-bold tw-text-accent-700 hover:tw-text-accent-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-accent-300">
                                        {{ __('Join online') }}
                                        <x-lucide-external-link class="tw-h-3.5 tw-w-3.5" />
                                    </a>
                                @endif
                            </div>
                        </div>
                        @if ($data->category)
                            <div class="tw-flex tw-gap-2">
                                <x-lucide-ticket class="tw-mt-0.5 tw-h-4 tw-w-4 tw-shrink-0 tw-text-primary-600" />
                                <div>
                                    <dt class="tw-sr-only">{{ __('Category') }}</dt>
                                    <dd class="tw-font-semibold tw-text-ink-950 dark:tw-text-neutral-50">{{ $data->category->name }}</dd>
                                </div>
                            </div>
                        @endif
                    </dl>
                    <a href="{{ $eventUrl }}"
                        class="tw-mt-5 tw-inline-flex tw-items-center tw-gap-1.5 tw-text-sm tw-font-bold tw-text-accent-700 hover:tw-text-accent-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-accent-300">
                        <x-lucide-chevron-left class="tw-h-4 tw-w-4" />
                        {{ __('Back to event') }}
                    </a>
                </x-card>
            </aside>
        </div>
    </div>
@endsection
