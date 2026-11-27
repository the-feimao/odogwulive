@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Tickets')" :parent="__('Event')" :url="'events/'.$event->id" />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('View Tickets') }}
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ $event->name }}
            </p>
        </div>

        @can('ticket_create')
            <a href="{{ url($event->id.'/ticket/create') }}"
                class="tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                <x-lucide-plus class="tw-h-4 tw-w-4" />
                {{ __('Add New') }}
            </a>
        @endcan
    </div>

    <x-flash-messages />

    @if ($capacityExceeded)
        <x-alert tone="warning" :title="__('Ticket quantity exceeds venue capacity')">
            {{ __('This event allows :capacity people but has :allocated tickets. Reduce ticket quantities or increase the event capacity.', ['capacity' => $eventCapacity, 'allocated' => $allocatedTickets]) }}
        </x-alert>
    @else
        <p class="tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
            {{ __('Venue capacity: :capacity people · :remaining ticket(s) still available to allocate.', ['capacity' => $eventCapacity, 'remaining' => $remainingCapacity]) }}
        </p>
    @endif

    <x-card :padded="false">
        @if ($ticket->isEmpty())
            <x-empty-state :title="__('No tickets yet')" :description="__('Add a ticket to start selling for this event.')" />
        @else
            <div class="tw-overflow-x-auto">
                <table id="report_table" class="tw-min-w-full tw-text-left tw-text-sm">
                    <thead>
                        <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                            <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                            <th class="tw-px-3 tw-py-3">{{ __('Ticket Number') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Name') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Quantity') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Price') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Sales End') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Status') }}</th>
                            @if (Gate::check('ticket_edit') || Gate::check('ticket_delete'))
                                <th class="tw-px-3 tw-py-3">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($ticket as $item)
                            <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                                <td class="tw-px-3 tw-py-3">
                                    <span class="tw-inline-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-stub tw-bg-accent-100 tw-text-accent-700 dark:tw-bg-accent-500/20 dark:tw-text-accent-300">
                                        <x-lucide-tag class="tw-h-4 tw-w-4" />
                                    </span>
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-font-mono tw-text-xs tw-text-neutral-600 dark:tw-text-neutral-300">
                                    {{ $item->ticket_number }}
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->name }}</td>
                                <td class="tw-px-3 tw-py-3">{{ $item->quantity }}</td>
                                <td class="tw-px-3 tw-py-3">
                                    {{ $item->type == 'paid' ? $currency.$item->price : __('FREE') }}
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">
                                    {{ Carbon\Carbon::parse($item->start_time)->format('l').', '.$item->end_time }}
                                </td>
                                <td class="tw-px-3 tw-py-3">
                                    <x-badge :tone="$item->status == '1' ? 'success' : 'warning'">
                                        {{ $item->status == '1' ? __('Active') : __('Inactive') }}
                                    </x-badge>
                                </td>
                                @if (Gate::check('ticket_edit') || Gate::check('ticket_delete'))
                                    <td class="tw-px-3 tw-py-3">
                                        <span class="tw-inline-flex tw-items-center tw-gap-2">
                                            @can('ticket_edit')
                                                <a href="{{ url('ticket/edit/'.$item->id) }}" title="Edit Ticket"
                                                    aria-label="{{ __('Edit') }} {{ $item->name }}"
                                                    class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-accent-600 tw-transition hover:tw-bg-accent-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:hover:tw-bg-accent-900/20">
                                                    <x-lucide-pencil class="tw-h-4 tw-w-4" />
                                                </a>
                                            @endcan
                                            @can('ticket_delete')
                                                <a href="javascript:void(0)" onclick="deleteData('deleteTickets','{{ $item->id }}');"
                                                    title="Delete Ticket" aria-label="{{ __('Delete') }} {{ $item->name }}"
                                                    class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-border tw-border-danger-200 tw-text-danger-600 tw-transition hover:tw-bg-danger-100 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-danger-600 dark:tw-border-danger-500/30 dark:tw-text-danger-300 dark:hover:tw-bg-danger-500/10">
                                                    <x-lucide-trash-2 class="tw-h-4 tw-w-4" />
                                                </a>
                                            @endcan
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
@endsection
