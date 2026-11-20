@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Event Detail')" :parent="__('Event')" url="events" />

    <x-flash-messages />

    <div class="event-single tw-grid tw-gap-5 lg:tw-grid-cols-3">
        <div class="tw-space-y-5 lg:tw-col-span-2">
            <x-card :padded="false">
                <x-slot:header>
                    <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3">
                        <div>
                            <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ $event->name }}</h2>
                            <p class="tw-mt-0.5 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                                {{ __('Event overview and ticketing.') }}
                            </p>
                        </div>
                        <a href="{{ url($event->id.'/'.preg_replace('/\s+/', '-', $event->name).'/tickets') }}"
                            class="tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                            <x-lucide-ticket class="tw-h-4 tw-w-4" />
                            {{ __('Manage Tickets') }}
                        </a>
                    </div>
                </x-slot:header>

                <div class="tw-space-y-6 tw-p-5">
                    <div class="event-img tw-h-64 tw-rounded-card tw-bg-cover tw-bg-center"
                        style="background-image: url('{{ url('images/upload/'.$event->image) }}')"
                        role="img" aria-label="{{ $event->name }}"></div>

                    <div class="event-description tw-prose tw-max-w-none tw-text-sm tw-text-ink-800 dark:tw-prose-invert dark:tw-text-neutral-200">
                        {!! $event->description !!}
                    </div>

                    <div class="tw-grid tw-gap-3 sm:tw-grid-cols-3">
                        <div class="tw-flex tw-items-start tw-gap-3 tw-rounded-card tw-bg-neutral-50 tw-p-4 dark:tw-bg-ink-950">
                            <span class="tw-text-primary-600 dark:tw-text-primary-300"><x-lucide-users class="tw-h-5 tw-w-5" /></span>
                            <span>
                                <span class="tw-block tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('People allowed') }}</span>
                                <span class="tw-mt-1 tw-block tw-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ $event->people }}</span>
                            </span>
                        </div>
                        <div class="tw-flex tw-items-start tw-gap-3 tw-rounded-card tw-bg-neutral-50 tw-p-4 dark:tw-bg-ink-950">
                            <span class="tw-text-primary-600 dark:tw-text-primary-300"><x-lucide-calendar-days class="tw-h-5 tw-w-5" /></span>
                            <span>
                                <span class="tw-block tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Date') }}</span>
                                <span class="tw-mt-1 tw-block tw-text-sm tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">
                                    {{ Carbon\Carbon::parse($event->start_time)->format('l').', '.$event->start_time->format('d F Y') }}
                                </span>
                            </span>
                        </div>
                        <div class="tw-flex tw-items-start tw-gap-3 tw-rounded-card tw-bg-neutral-50 tw-p-4 dark:tw-bg-ink-950">
                            <span class="tw-text-primary-600 dark:tw-text-primary-300"><x-lucide-map-pin class="tw-h-5 tw-w-5" /></span>
                            <span>
                                <span class="tw-block tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Location') }}</span>
                                <span class="tw-mt-1 tw-block tw-text-sm tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">
                                    {{ $event->type == 'offline' ? $event->address : __('Online Event') }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </x-card>

            <x-card :padded="false">
                <x-slot:header>
                    <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Recent Sales') }}</h2>
                </x-slot:header>

                <div class="tw-overflow-x-auto">
                    <table class="tw-min-w-full tw-text-left tw-text-sm">
                        <thead>
                            <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                                <th class="tw-px-3 tw-py-3">{{ __('Order Id') }}</th>
                                <th class="tw-px-3 tw-py-3">{{ __('Customer Name') }}</th>
                                <th class="tw-px-3 tw-py-3">{{ __('Ticket Name') }}</th>
                                <th class="tw-px-3 tw-py-3">{{ __('Date') }}</th>
                                <th class="tw-px-3 tw-py-3">{{ __('Sold Ticket') }}</th>
                                <th class="tw-px-3 tw-py-3">{{ __('Payment') }}</th>
                                <th class="tw-px-3 tw-py-3">{{ __('Payment Gateway') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($event->sales as $item)
                                <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                                    <td class="tw-px-3 tw-py-3 tw-font-mono tw-text-xs tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->order_id }}</td>
                                    <td class="tw-px-3 tw-py-3 tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->customer->name }}</td>
                                    <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->ticket->name }}</td>
                                    <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->created_at->format('Y-m-d') }}</td>
                                    <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->quantity }}</td>
                                    <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $currency.$item->payment }}</td>
                                    <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->payment_type }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>
        </div>

        <div class="tw-space-y-4">
            @if (count($event->ticket) > 0)
                @foreach ($event->ticket as $item)
                    <x-card class="card-hero" :padded="false">
                        <div class="tw-border-b tw-border-primary-500/20 tw-bg-primary-500/10 tw-p-5">
                            <p class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-primary-900 dark:tw-text-primary-200">
                                {{ __('Sales end on') }}
                            </p>
                            <p class="tw-mt-1 tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-50">
                                {{ $item->start_time->format('Y-m-d').','.$item->end_time->format('h:i a') }}
                            </p>
                            <div class="tw-mt-4 tw-flex tw-items-start tw-gap-3">
                                <span class="tw-flex tw-h-10 tw-w-10 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-stub tw-bg-primary-500 tw-text-ink-950">
                                    <x-lucide-ticket class="tw-h-5 tw-w-5" />
                                </span>
                                <span>
                                    <span class="tw-block tw-text-sm tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">
                                        {{ $item->name }} | {{ $currency.$item->price }}
                                    </span>
                                    <span class="tw-mt-1 tw-block tw-lg tw-font-extrabold tw-text-primary-700 dark:tw-text-primary-300">
                                        {{ $item->used_ticket.'/'.$item->quantity }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </x-card>
                @endforeach
            @else
                <x-card class="card-hero" :padded="false">
                    <div class="tw-flex tw-items-center tw-gap-4 tw-p-5">
                        <span class="tw-flex tw-h-11 tw-w-11 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-stub tw-bg-neutral-100 tw-text-neutral-500 dark:tw-bg-neutral-800 dark:tw-text-neutral-300">
                            <x-lucide-ticket class="tw-h-5 tw-w-5" />
                        </span>
                        <span>
                            <span class="tw-block tw-text-sm tw-text-sm tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('No tickets') }}</span>
                            <a href="{{ url($event->id.'/ticket/create') }}"
                                class="tw-mt-1 tw-inline-flex tw-h-8 tw-items-center tw-gap-1 tw-rounded-pill tw-bg-primary-500 tw-px-3 tw-text-xs tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                                <x-lucide-plus class="tw-h-3.5 tw-w-3.5" />
                                {{ __('Add Ticket') }}
                            </a>
                        </span>
                    </div>
                </x-card>
            @endif
        </div>
    </div>
@endsection
