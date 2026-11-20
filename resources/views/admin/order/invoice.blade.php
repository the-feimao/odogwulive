@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Order Detail')" :parent="__('Orders')" url="orders" />

    <div class="invoice invoice-print tw-space-y-5">
        <x-card :padded="false">
            <x-slot:header>
                <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3">
                    <div>
                        <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">
                            {{ __('Order') }} <span class="tw-font-mono">{{ $order->order_id }}</span>
                        </h2>
                        <p class="tw-mt-0.5 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                            {{ __('Invoice and ticket details for this order.') }}
                        </p>
                    </div>
                    <a href="{{ url('order-invoice-print/'.$order->id) }}" target="_blank"
                        class="btn btn-primary tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                        <x-lucide-printer id="print_invoice" class="tw-h-4 tw-w-4" />
                        {{ __('Print') }}
                    </a>
                </div>
            </x-slot:header>

            <div class="tw-space-y-6 tw-p-5">
                <div class="tw-grid tw-gap-6 md:tw-grid-cols-2">
                    <div>
                        <p class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Event') }}:</p>
                        <div class="tw-mt-2 tw-flex tw-items-center tw-gap-3">
                            <img src="{{ url('images/upload/'.$order->event->image) }}" alt="{{ $order->event->name }}"
                                class="tw-h-12 tw-w-12 tw-rounded-stub tw-object-cover" loading="lazy" width="50" height="50">
                            <div>
                                <p class="tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-50">{{ $order->event->name }}</p>
                                <p class="tw-mt-0.5 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                                    {{ $order->event->start_time->format('l').', '.$order->event->start_time->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @if (Auth::user()->hasRole('admin'))
                        <div class="md:tw-text-right">
                            <p class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Organizer') }}:</p>
                            <address class="tw-mt-2 tw-not-italic tw-text-sm tw-text-ink-800 dark:tw-text-neutral-200">
                                {{ $order->organization->first_name.' '.$order->organization->last_name }}<br>
                                {{ $order->organization->email }}<br>
                                {{ $order->organization->country }}
                            </address>
                        </div>
                    @endif
                </div>

                <div class="tw-grid tw-gap-6 md:tw-grid-cols-2">
                    <div>
                        <p class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Attendee') }}:</p>
                        <address class="tw-mt-2 tw-not-italic tw-text-sm tw-text-ink-800 dark:tw-text-neutral-200">
                            {{ $order->customer->name.' '.$order->customer->last_name }}<br>
                            {{ $order->customer->email }}
                        </address>
                    </div>
                    <div class="md:tw-text-right">
                        <p class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Order Date') }}:</p>
                        <p class="tw-mt-2 tw-text-sm tw-text-ink-800 dark:tw-text-neutral-200">{{ $order->created_at->format('d F, Y') }}</p>
                    </div>
                </div>

                <div>
                    <h3 class="tw-mb-3 tw-text-base tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Order Summary') }}</h3>
                    <div class="tw-overflow-x-auto">
                        <table class="tw-min-w-full tw-text-left tw-text-sm">
                            <thead>
                                <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                                    <th class="tw-px-3 tw-py-3">#</th>
                                    <th class="tw-px-3 tw-py-3 tw-text-center">{{ __('Ticket Name') }}</th>
                                    <th class="tw-px-3 tw-py-3 tw-text-center">{{ __('Ticket Number') }}</th>
                                    <th class="tw-px-3 tw-py-3 tw-text-center">{{ __('Price') }}</th>
                                    <th class="tw-px-3 tw-py-3 tw-text-center">{{ __('Code') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->ticket_data as $item)
                                    <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                                        <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $loop->iteration }}</td>
                                        <td class="tw-px-3 tw-py-3 tw-text-center tw-text-neutral-600 dark:tw-text-neutral-300">{{ $order->ticket->name }}</td>
                                        <td class="tw-px-3 tw-py-3 tw-text-center tw-font-mono tw-text-xs tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->ticket_number }}</td>
                                        <td class="tw-px-3 tw-py-3 tw-text-center tw-text-neutral-600 dark:tw-text-neutral-300">{{ $currency.$order->ticket->price }}</td>
                                        <td class="tw-px-3 tw-py-3 tw-text-center">
                                            <a href="{{ url('get-code/'.$item->id) }}" title="{{ __('Print Ticket') }}"
                                                aria-label="{{ __('Print ticket') }} {{ $item->ticket_number }}"
                                                class="tw-inline-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-stub tw-text-accent-600 tw-transition hover:tw-bg-accent-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:hover:tw-bg-accent-900/20">
                                                <x-lucide-printer class="tw-h-4 tw-w-4" />
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tw-grid tw-gap-6 md:tw-grid-cols-3">
                    <div class="md:tw-col-span-2">
                        <h3 class="tw-text-base tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Payment Method') }}</h3>
                        <address class="tw-mt-2 tw-not-italic tw-text-sm tw-text-ink-800 dark:tw-text-neutral-200">
                            <strong>{{ $order->payment_type }}</strong><br>
                            @if ($order->payment_type != 'FREE')
                                <span class="tw-mt-2 tw-inline-block"><x-badge :tone="$order->payment_status == 1 ? 'success' : 'warning'">{{ $order->payment_status == 1 ? 'Paid' : 'waiting' }}</x-badge></span><br>
                                {{ __('Token:') }} {{ $order->payment_token == null ? '-' : $order->payment_token }}
                            @endif
                        </address>
                    </div>

                    <div class="tw-space-y-2 md:tw-text-right">
                        <div class="tw-flex tw-items-center tw-justify-between tw-gap-4 tw-text-sm">
                            <span class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Subtotal') }}</span>
                            <span class="tw-text-ink-900 dark:tw-text-neutral-100">{{ $currency.($order->payment + $order->coupon_discount - $order->tax) }}</span>
                        </div>
                        <div class="tw-flex tw-items-center tw-justify-between tw-gap-4 tw-text-sm">
                            <span class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Coupon Discount') }}</span>
                            <span class="tw-text-ink-900 dark:tw-text-neutral-100">(-) {{ $currency.$order->coupon_discount }}</span>
                        </div>
                        @foreach ($order->tax_data as $item)
                            <div class="tw-flex tw-items-center tw-justify-between tw-gap-4 tw-text-sm">
                                <span class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->taxName }}</span>
                                <span class="tw-text-ink-900 dark:tw-text-neutral-100">(+) {{ $currency.$item->price }}</span>
                            </div>
                        @endforeach
                        <div class="tw-flex tw-items-center tw-justify-between tw-gap-4 tw-border-t tw-border-neutral-200 tw-pt-3 dark:tw-border-neutral-700">
                            <span class="tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Total') }}</span>
                            <span class="tw-text-xl tw-font-extrabold tw-text-primary-700 dark:tw-text-primary-300">{{ $currency.$order->payment }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </x-card>
    </div>
@endsection
