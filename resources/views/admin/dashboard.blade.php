@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Dashboard')" />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('Dashboard') }}
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('A snapshot of orders, customers and upcoming events.') }}
            </p>
        </div>
    </div>

    <x-flash-messages />

    <div class="tw-grid tw-gap-4 md:tw-grid-cols-2 xl:tw-grid-cols-3">
        <x-admin-stat-card :label="__('Total Orders')" tone="primary">
            <x-slot:icon><x-lucide-ticket class="tw-h-5 tw-w-5" /></x-slot:icon>
            <span class="card-stats order-total">{{ $master['total_order'] }}</span>
            <div class="tw-mt-3 tw-grid tw-grid-cols-3 tw-gap-2 tw-border-t tw-border-neutral-100 tw-pt-3 dark:tw-border-neutral-800">
                <div>
                    <p class="card-stats order-pending tw-text-base tw-font-bold tw-text-warning-600">{{ $master['pending_order'] }}</p>
                    <p class="tw-text-2xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500">{{ __('Pending') }}</p>
                </div>
                <div>
                    <p class="card-stats order-complete tw-text-base tw-font-bold tw-text-success-600">{{ $master['complete_order'] }}</p>
                    <p class="tw-text-2xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500">{{ __('Completed') }}</p>
                </div>
                <div>
                    <p class="card-stats order-cancel tw-text-base tw-font-bold tw-text-danger-600">{{ $master['cancel_order'] }}</p>
                    <p class="tw-text-2xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500">{{ __('Cancel') }}</p>
                </div>
            </div>
        </x-admin-stat-card>

        <x-dropdown :label="__('Order Statistics')" class="tw-justify-self-start">
            <x-slot:trigger>
                <span id="orders-month" class="tw-inline-flex tw-items-center tw-gap-2">
                    <x-lucide-ticket class="tw-h-4 tw-w-4" />
                    {{ __('All') }}
                </span>
            </x-slot:trigger>
            <p class="tw-px-3 tw-py-2 tw-text-2xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500">
                {{ __('Select Month') }}
            </p>
            @for ($i = 1; $i <= 12; $i++)
                <button type="button" onclick="getStatistics({{ $i }})"
                    class="tw-flex tw-w-full tw-items-center tw-rounded-stub tw-px-3 tw-py-2 tw-text-left tw-text-sm tw-text-ink-700 tw-transition hover:tw-bg-neutral-100 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-neutral-200 dark:hover:tw-bg-neutral-800">
                    {{ date('F', mktime(0, 0, 0, $i, 10)) }}
                </button>
            @endfor
        </x-dropdown>

        <x-admin-stat-card :label="__('Customers')" tone="accent" :hint="__('Registered app users')">
            <x-slot:icon><x-lucide-users class="tw-h-5 tw-w-5" /></x-slot:icon>
            {{ $master['users'] }}
        </x-admin-stat-card>

        <x-admin-stat-card :label="__('Organizations')" tone="success" :hint="__('Verified event partners')">
            <x-slot:icon><x-lucide-users class="tw-h-5 tw-w-5" /></x-slot:icon>
            {{ $master['organizations'] }}
        </x-admin-stat-card>
    </div>

    <div class="tw-grid tw-gap-5 xl:tw-grid-cols-[minmax(0,1fr)_320px]">
        <x-card :padded="false">
            <x-slot:header>
                <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3">
                    <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Recent Event') }}</h2>
                    <a href="{{ url('events') }}"
                        class="tw-inline-flex tw-h-9 tw-items-center tw-gap-1.5 tw-rounded-pill tw-border tw-border-neutral-200 tw-px-3 tw-text-xs tw-font-bold tw-text-ink-700 tw-transition hover:tw-bg-neutral-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-text-neutral-200 dark:hover:tw-bg-neutral-800">
                        {{ __('See all') }}
                        <x-lucide-arrow-up-right class="tw-h-3.5 tw-w-3.5" />
                    </a>
                </div>
            </x-slot:header>

            @if ($events->isEmpty())
                <x-empty-state :title="__('No events found')" :description="__('Published events will appear here.')" />
            @else
                <div class="tw-overflow-x-auto">
                    <table class="tw-min-w-full tw-text-left tw-text-sm">
                        <tbody>
                            @foreach ($events as $item)
                                <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                                    <td class="tw-px-4 tw-py-3">
                                        <img src="{{ url('images/upload/'.$item->image) }}" alt="{{ $item->name }}"
                                            loading="lazy" class="tw-h-12 tw-w-16 tw-rounded-stub tw-object-cover">
                                    </td>
                                    <td class="tw-px-4 tw-py-3">
                                        <p class="tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->name }}</p>
                                        <p class="tw-mt-0.5 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                            {{ $item->type == 'online' ? __('Online Event') : $item->address }}
                                        </p>
                                    </td>
                                    <td class="tw-px-4 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">
                                        <span class="tw-inline-flex tw-items-center tw-gap-1.5">
                                            <x-lucide-users class="tw-h-4 tw-w-4 tw-text-primary-600" />
                                            {{ $item->people.' allowed' }}
                                        </span>
                                    </td>
                                    <td class="tw-px-4 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">
                                        <span class="tw-inline-flex tw-items-center tw-gap-1.5">
                                            <x-lucide-ticket class="tw-h-4 tw-w-4 tw-text-accent-600" />
                                            {{ $item->avaliable.' '.__('Pcs left') }}
                                        </span>
                                    </td>
                                    <td class="tw-px-4 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">
                                        <span class="tw-inline-flex tw-items-center tw-gap-1.5">
                                            <x-lucide-calendar-days class="tw-h-4 tw-w-4 tw-text-primary-600" />
                                            {{ $item->start_time->format('Y-m-d') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>

        <x-card :padded="false">
            <x-slot:header>
                <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">
                    {{ \Illuminate\Support\Carbon::now()->format('F').' '.__('Event') }}
                </h2>
            </x-slot:header>

            <div class="tw-p-4">
                <input type="hidden" name="eventDate" id="eventDate" value="{{ json_encode($master['eventDate']) }}">
                <div id="home_calender"></div>

                <div class="home-upcoming-event tw-space-y-4">
                    @if ($monthEvent->isEmpty())
                        <x-empty-state :title="__('No events found')" :description="__('Nothing scheduled for this month yet.')" />
                    @else
                        @foreach ($monthEvent as $item)
                            <div class="tw-grid tw-grid-cols-[44px_minmax(0,1fr)] tw-gap-3">
                                <div class="tw-flex tw-h-12 tw-w-11 tw-flex-col tw-items-center tw-justify-center tw-rounded-stub tw-bg-primary-100 tw-text-primary-700 dark:tw-bg-primary-500/15 dark:tw-text-primary-300">
                                    <span class="tw-text-lg tw-font-extrabold tw-leading-none">{{ $item->start_time->format('d') }}</span>
                                    <span class="tw-text-2xs tw-font-bold tw-uppercase">{{ $item->start_time->format('D') }}</span>
                                </div>
                                <div>
                                    <p class="name tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->name }}</p>
                                    <p class="tw-mt-0.5 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                        {{ __('Ticket Sold') }}
                                        <span>{{ $item->sold_ticket }}/{{ $item->tickets }}</span>
                                    </p>
                                    <div class="progress tw-mt-2 tw-h-1.5 tw-overflow-hidden tw-rounded-pill tw-bg-neutral-100 dark:tw-bg-neutral-800" data-height="5">
                                        <div class="progress-bar tw-h-full tw-rounded-pill tw-bg-primary-500" role="progressbar"
                                            data-width="{{ $item->average }}%"
                                            style="width: {{ max(0, min(100, (float) $item->average)) }}%"
                                            aria-valuenow="{{ $item->average }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </x-card>
    </div>
@endsection
