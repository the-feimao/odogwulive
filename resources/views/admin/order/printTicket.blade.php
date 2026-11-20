@extends('layouts.admin')

@section('admin-content')
    @php
        $appName = App\Models\Setting::find(1)->app_name;
    @endphp

    <x-admin-breadcrumb :title="__('Ticket Detail')" :parent="__('Orders')" url="orders" />

    <div class="invoice invoice-print tw-space-y-5">
        <x-card :padded="false">
            <x-slot:header>
                <div class="ticket-header tw-text-center">
                    <h2 class="tw-text-xl tw-font-extrabold tw-tracking-tight tw-text-primary-700 dark:tw-text-primary-300">
                        {{ $appName }}
                    </h2>
                    <p class="tw-mt-1 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                        {{ __('Scannable ticket pass.') }}
                    </p>
                </div>
            </x-slot:header>

            <div class="ticket-header tw-grid tw-gap-6 tw-border-b tw-border-neutral-200 tw-p-5 dark:tw-border-neutral-800 md:tw-grid-cols-3">
                <div class="print-left md:tw-col-span-2">
                    <h3 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">
                        {{ $ticket->order->event->name }}
                    </h3>
                    <dl class="tw-mt-4 tw-grid tw-gap-x-4 tw-gap-y-2 tw-text-sm sm:tw-grid-cols-2">
                        <dt class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Start Date:') }}</dt>
                        <dd class="tw-text-ink-900 dark:tw-text-neutral-100">
                            {{ $ticket->order->event->start_time->format('d F Y').', '.$ticket->order->event->start_time->format('h:i a') }}
                        </dd>
                        <dt class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('End Date:') }}</dt>
                        <dd class="tw-text-ink-900 dark:tw-text-neutral-100">
                            {{ $ticket->order->event->end_time->format('d F Y').', '.$ticket->order->event->end_time->format('h:i a') }}
                        </dd>
                        <dt class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Address:') }}</dt>
                        <dd class="tw-text-ink-900 dark:tw-text-neutral-100">
                            {{ $ticket->order->event->type == 'online' ? 'Online Event' : $ticket->order->event->address }}
                        </dd>
                        <dt class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Organizer:') }}</dt>
                        <dd class="tw-text-ink-900 dark:tw-text-neutral-100">
                            {{ $ticket->order->organization->first_name.' '.$ticket->order->organization->last_name }}
                        </dd>
                    </dl>
                </div>

                <div class="print-left print-right md:tw-text-right">
                    <h3 class="tw-text-base tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">
                        {{ __('Ticket:') }} <span class="tw-font-mono">{{ $ticket->ticket_number }}</span>
                    </h3>
                    <p class="tw-mt-2 tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">
                        {{ $ticket->order->customer->name.' '.$ticket->order->customer->last_name }}
                    </p>
                    <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                        <span class="tw-font-semibold">{{ __('Payment method:') }}</span> {{ $ticket->order->payment_type }}
                    </p>
                    @if ($ticket->status == 1)
                        <img src="{{ url('images/scan.png') }}" alt="{{ __('Used') }}"
                            class="tw-mt-4 tw-h-24 tw-w-auto md:tw-ml-auto" loading="lazy">
                    @endif
                </div>

                <div class="tw-flex tw-items-center tw-justify-center tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-p-4 dark:tw-border-neutral-800 dark:tw-bg-ink-950 md:tw-justify-end"
                    role="img" aria-label="{{ __('QR code for').' '.$ticket->ticket_number }}">
                    {!! $ticket->qrCode !!}
                </div>
            </div>
        </x-card>
    </div>
@endsection
