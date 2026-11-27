@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Scanner')" />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('View Scanner') }}
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('Manage scanner accounts, event assignments and ticket check-in progress.') }}
            </p>
            <p class="tw-mt-1 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                {{ __('Check-in counts refresh when this page reloads.') }}
            </p>
        </div>

        @can('scanner_create')
            <a href="{{ url('scanner/create') }}"
                class="tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                <x-lucide-plus class="tw-h-4 tw-w-4" />
                {{ __('Add New') }}
            </a>
        @endcan
    </div>

    <x-flash-messages />

    <x-card :padded="false">
        @if ($scanners->isEmpty())
            <x-empty-state :title="__('No scanners yet')" :description="__('Add a scanner to help check in attendees.')" />
        @else
            <div class="tw-overflow-x-auto">
                <table id="report_table" class="tw-min-w-full tw-text-left tw-text-sm">
                    <thead>
                        <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                            <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                            <th class="tw-px-3 tw-py-3">{{ __('Name') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Total events') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Checked in') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Remaining') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Status') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($scanners as $item)
                            <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                                <td class="tw-px-3 tw-py-3">
                                    <span class="tw-inline-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-stub tw-bg-accent-100 tw-text-accent-700 dark:tw-bg-accent-500/20 dark:tw-text-accent-300">
                                        <x-lucide-user-check class="tw-h-4 tw-w-4" />
                                    </span>
                                </td>
                                <td class="tw-px-3 tw-py-3">
                                    <div class="tw-flex tw-items-center tw-gap-3">
                                        <img src="{{ url('images/upload/'.$item->image) }}" alt="{{ $item->name }}"
                                            loading="lazy" width="40" height="40"
                                            class="tw-h-10 tw-w-10 tw-rounded-pill tw-object-cover">
                                        <div>
                                            <p class="tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->first_name.' '.$item->last_name }}</p>
                                            <p class="tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->total_event }}</td>
                                <td class="tw-px-3 tw-py-3 tw-font-semibold tw-text-success-700 dark:tw-text-success-300">{{ $item->checked_in }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->remaining }}</td>
                                <td class="tw-px-3 tw-py-3">
                                    <x-badge :tone="$item->status == 1 ? 'success' : 'danger'">
                                        {{ $item->status == 1 ? __('Active') : __('Inactive') }}
                                    </x-badge>
                                </td>
                                <td class="tw-px-3 tw-py-3">
                                    @if ($item->status == '0')
                                        <a href="{{ url('block-scanner/'.$item->id) }}" title="Unblock {{ $item->name }}"
                                            aria-label="Unblock {{ $item->name }}"
                                            class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-success-600 tw-transition hover:tw-bg-success-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-success-600 dark:tw-text-success-300 dark:hover:tw-bg-success-500/10">
                                            <x-lucide-unlock class="tw-h-4 tw-w-4" />
                                        </a>
                                    @else
                                        <a href="{{ url('block-scanner/'.$item->id) }}" title="Block {{ $item->name }}"
                                            aria-label="Block {{ $item->name }}"
                                            class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-danger-600 tw-transition hover:tw-bg-danger-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-danger-600 dark:tw-text-danger-300 dark:hover:tw-bg-danger-500/10">
                                            <x-lucide-ban class="tw-h-4 tw-w-4" />
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>

    <div class="tw-mt-8 tw-space-y-4">
        <div>
            <h2 class="tw-text-xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('Event check-in progress') }}
            </h2>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('An event can have multiple scanners. Tickets sold is the number of ticket records issued; checked-in tickets are scanned by any scanner assigned to that event.') }}
            </p>
        </div>

        <x-card :padded="false">
            @if ($events->isEmpty())
                <x-empty-state :title="__('No events yet')" :description="__('Create an event to start tracking ticket check-ins.')" />
            @else
                <div class="tw-overflow-x-auto">
                    <table class="tw-min-w-full tw-text-left tw-text-sm">
                        <thead>
                            <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                                <th class="tw-px-3 tw-py-3">{{ __('Event') }}</th>
                                <th class="tw-px-3 tw-py-3">{{ __('Date') }}</th>
                                <th class="tw-px-3 tw-py-3">{{ __('Scanners') }}</th>
                                <th class="tw-px-3 tw-py-3">{{ __('Tickets sold') }}</th>
                                <th class="tw-px-3 tw-py-3">{{ __('Checked in') }}</th>
                                <th class="tw-px-3 tw-py-3">{{ __('Remaining') }}</th>
                                <th class="tw-px-3 tw-py-3">{{ __('Progress') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($events as $event)
                                @php
                                    $progress = $event->tickets_sold > 0
                                        ? min(100, (int) round(($event->checked_in / $event->tickets_sold) * 100))
                                        : 0;
                                @endphp
                                <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                                    <td class="tw-px-3 tw-py-3">
                                        <div class="tw-flex tw-items-center tw-gap-3">
                                            <span class="tw-inline-flex tw-h-8 tw-w-8 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-stub tw-bg-primary-100 tw-text-primary-700 dark:tw-bg-primary-500/20 dark:tw-text-primary-300">
                                                <x-lucide-calendar-days class="tw-h-4 tw-w-4" />
                                            </span>
                                            <span class="tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $event->name }}</span>
                                        </div>
                                    </td>
                                    <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">
                                        {{ $event->start_time?->format('d M Y') }}
                                    </td>
                                    <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">
                                        @forelse ($event->assigned_scanners as $assignedScanner)
                                            <span class="tw-inline-flex tw-items-center tw-rounded-pill tw-bg-neutral-100 tw-px-2 tw-py-0.5 tw-text-xs tw-font-medium tw-text-neutral-700 dark:tw-bg-neutral-800 dark:tw-text-neutral-200">
                                                {{ $assignedScanner->name }}
                                            </span>
                                        @empty
                                            {{ __('Unassigned') }}
                                        @endforelse
                                    </td>
                                    <td class="tw-px-3 tw-py-3 tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $event->tickets_sold }}</td>
                                    <td class="tw-px-3 tw-py-3 tw-font-semibold tw-text-success-700 dark:tw-text-success-300">{{ $event->checked_in }}</td>
                                    <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $event->remaining }}</td>
                                    <td class="tw-px-3 tw-py-3">
                                        <div class="tw-flex tw-items-center tw-gap-2">
                                            <div class="tw-h-1.5 tw-w-20 tw-overflow-hidden tw-rounded-pill tw-bg-neutral-100 dark:tw-bg-neutral-800"
                                                role="progressbar" aria-label="{{ __('Check-in progress for').' '.$event->name }}"
                                                aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}">
                                                <div class="tw-h-full tw-rounded-pill tw-bg-success-500" style="width: {{ $progress }}%"></div>
                                            </div>
                                            <span class="tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $progress }}%</span>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-card>
    </div>
@endsection
