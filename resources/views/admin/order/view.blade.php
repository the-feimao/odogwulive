@extends('layouts.admin')

@section('admin-content')
    @php
        $orderTone = match ($order->order_status) {
            'Pending' => 'warning',
            'Cancel' => 'danger',
            default => 'success',
        };
        $paymentTone = $order->payment_status == 1 ? 'success' : 'warning';
        $paymentLabel = $order->payment_status == 1 ? 'Complete' : 'Waiting';
    @endphp

    <x-admin-breadcrumb :title="__('Order Detail')" :parent="__('Orders')" url="orders" />

    <div>
        <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
            {{ __('Order Detail') }}
        </h1>
    </div>

    <x-flash-messages />

    <x-card class="order-detail" :padded="false">
        <x-slot:header>
            <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('Order : ') }} <span class="tw-font-mono">{{ $order->order_id }}</span>
            </h2>
        </x-slot:header>

        <div class="tw-grid tw-gap-6 tw-p-5 lg:tw-grid-cols-4">
            <div class="lg:tw-col-span-1">
                <div class="event-detail tw-overflow-hidden tw-rounded-card tw-border tw-border-neutral-200 dark:tw-border-neutral-800">
                    <img src="{{ url('images/upload/'.$order->event->image) }}" alt="{{ $order->event->name }}"
                        class="tw-h-40 tw-w-full tw-object-cover" loading="lazy" width="320" height="160">
                    <div class="tw-space-y-2 tw-p-4">
                        <h3 class="tw-text-base tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ $order->event->name }}</h3>
                        <p class="tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('By') }}: {{ $order->organization->name }}</p>
                        <div>
                            <p class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('event on : ') }}</p>
                            <p class="tw-mt-1 tw-text-sm tw-text-ink-800 dark:tw-text-neutral-200">
                                {{ $order->event->start_time->format('l').', '.$order->event->start_time->format('Y-m-d h:i a').' to '.$order->event->start_time->format('Y-m-d h:i a') }}
                            </p>
                        </div>
                        <div>
                            <p class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Ticket Number: ') }}</p>
                            <p class="tw-mt-1 tw-font-mono tw-text-sm tw-text-ink-800 dark:tw-text-neutral-200">{{ $order->ticket->ticket_number }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <dl class="tw-grid tw-gap-x-4 tw-gap-y-2 tw-text-sm sm:tw-grid-cols-2 lg:tw-col-span-3">
                <dt class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Total tickets') }} :</dt>
                <dd class="tw-text-ink-900 dark:tw-text-neutral-100">{{ $order->quantity.' ticket' }}</dd>

                <dt class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Order Status') }} :</dt>
                <dd><x-badge :tone="$orderTone">{{ $order->order_status }}</x-badge></dd>

                <dt class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Payment Status') }} :</dt>
                <dd><x-badge :tone="$paymentTone">{{ $paymentLabel }}</x-badge></dd>

                <dt class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Payment Gateway') }} :</dt>
                <dd class="tw-text-ink-900 dark:tw-text-neutral-100">{{ $order->payment_type }}</dd>

                <dt class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Payment Token') }} :</dt>
                <dd class="tw-break-all tw-font-mono tw-text-xs tw-text-ink-900 dark:tw-text-neutral-100">
                    {{ $order->payment_type == 'LOCAL' || $order->payment_type == 'FREE' ? '-' : $order->payment_token }}
                </dd>

                @if ($order->payment_type == 'FREE')
                    <dt class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Sub Total') }} :</dt>
                    <dd class="tw-text-ink-900 dark:tw-text-neutral-100">{{ __('FREE') }}</dd>
                    <dt class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Coupon Discount') }} :</dt>
                    <dd class="tw-text-ink-900 dark:tw-text-neutral-100">{{ __('FREE') }}</dd>
                    <dt class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Tax') }} :</dt>
                    <dd class="tw-text-ink-900 dark:tw-text-neutral-100">{{ __('FREE') }}</dd>
                    <dt class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Final Payment') }} :</dt>
                    <dd class="tw-text-ink-900 dark:tw-text-neutral-100">{{ __('FREE') }}</dd>
                @else
                    <dt class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Sub Total') }} :</dt>
                    <dd class="tw-text-ink-900 dark:tw-text-neutral-100">{{ $currency.($order->payment + $order->coupon_discount - $order->tax) }}</dd>
                    <dt class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Coupon Discount') }} :</dt>
                    <dd class="tw-text-ink-900 dark:tw-text-neutral-100">(-) {{ $currency.$order->coupon_discount }}</dd>
                    <dt class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Tax') }} :</dt>
                    <dd class="tw-text-ink-900 dark:tw-text-neutral-100">(+) {{ $currency.$order->tax }}</dd>
                    <dt class="tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">{{ __('Final Payment') }} :</dt>
                    <dd class="tw-text-lg tw-font-extrabold tw-text-primary-700 dark:tw-text-primary-300">{{ $currency.$order->payment }}</dd>
                @endif
            </dl>
        </div>
    </x-card>
@endsection
