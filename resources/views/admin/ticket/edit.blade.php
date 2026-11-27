@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Edit Ticket')" :parent="__('Ticket')"
        :url="$event->id.'/'.preg_replace('/\s+/', '-', $event->name).'/tickets'" />

    <x-flash-messages />

    @if ($capacityExceeded)
        <x-alert tone="warning" :title="__('Ticket quantity exceeds venue capacity')">
            {{ __('Excluding this ticket, the event allows :capacity people but other tickets total :allocated. Reduce quantities or increase capacity.', ['capacity' => $eventCapacity, 'allocated' => $allocatedTickets]) }}
        </x-alert>
    @else
        <p class="tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
            {{ __('Venue capacity: :capacity people · :remaining ticket(s) left to allocate excluding this ticket.', ['capacity' => $eventCapacity, 'remaining' => $remainingCapacity]) }}
        </p>
    @endif

    <x-card :title="__('Edit Ticket')" :subtitle="__('Update the ticket details and sales window.')">
        <form method="post" class="ticket-form tw-grid tw-gap-6" action="{{ url('ticket/update/'.$ticket->id) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="event_id" value="{{ $event->id }}">

            <fieldset class="ds-field tw-flex tw-flex-col tw-gap-2">
                <legend class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">{{ __('Type') }}</legend>
                <div class="tw-flex tw-flex-wrap tw-gap-3">
                    <label class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-pill tw-border tw-border-neutral-200 tw-px-3 tw-py-2 tw-text-sm tw-font-semibold tw-text-ink-800 tw-transition hover:tw-border-accent-400 dark:tw-border-neutral-700 dark:tw-text-neutral-100">
                        <input type="radio" name="type" value="paid" class="tw-h-4 tw-w-4 tw-accent-primary-500"
                            {{ $ticket->type == 'free' ? '' : 'checked' }}>
                        {{ __('Paid') }}
                    </label>
                    <label class="tw-inline-flex tw-items-center tw-gap-2 tw-rounded-pill tw-border tw-border-neutral-200 tw-px-3 tw-py-2 tw-text-sm tw-font-semibold tw-text-ink-800 tw-transition hover:tw-border-accent-400 dark:tw-border-neutral-700 dark:tw-text-neutral-100">
                        <input type="radio" name="type" value="free" class="tw-h-4 tw-w-4 tw-accent-primary-500"
                            {{ $ticket->type == 'free' ? 'checked' : '' }}>
                        {{ __('Free') }}
                    </label>
                </div>
            </fieldset>

            <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                <x-input name="name" :label="__('Name')" :placeholder="__('Name')" :value="$ticket->name" />
                <x-input name="quantity" type="number" min="1" :label="__('Quantity')" :placeholder="__('Quantity')" :value="$ticket->quantity" />
            </div>

            <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                <x-input name="price" id="price" type="number" min="1" :label="__('Price')" :placeholder="__('Price')"
                    :value="$ticket->price" :disabled="$ticket->type == 'free'" />
                <x-input name="ticket_per_order" id="ticket_per_order" type="number" min="1"
                    :label="__('Maximum ticket per order')" :placeholder="__('Maximum ticket per order')" :value="$ticket->ticket_per_order" />
            </div>

            <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                <x-input name="start_time" id="start_time" class="date" :label="__('Start Time')" :placeholder="__('Choose Start time')"
                    :value="$ticket->start_time" />
                <x-input name="end_time" id="end_time" class="date" :label="__('End Time')" :placeholder="__('Choose End time')"
                    :value="$ticket->end_time" />
            </div>

            <x-textarea name="description" :label="__('Description')" :placeholder="__('Description')" :value="$ticket->description" />

            <x-select name="status" :label="__('status')" class="select2">
                <option value="1" {{ old('status', $ticket->status) == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                <option value="0" {{ old('status', $ticket->status) == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
            </x-select>

            <div>
                <x-button type="submit">{{ __('Submit') }}</x-button>
            </div>
        </form>
    </x-card>
@endsection
