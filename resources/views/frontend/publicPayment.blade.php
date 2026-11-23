@extends('layouts.public')

@section('title', __('Complete payment').' — '.config('brand.name'))

@push('head')
    <meta name="referrer" content="no-referrer">
@endpush

@section('content')
    @php
        $callbackUrl = route('frontend.publicPayment.callback', [
            'order' => $order->id,
            'token' => $token,
        ]);
    @endphp

    <div class="tw-mx-auto tw-max-w-xl tw-px-4 tw-py-12 md:tw-px-6">
        <x-card :title="__('Complete your payment')"
            :subtitle="__('You are paying for order :order.', ['order' => $order->order_id])">
            <dl class="tw-grid tw-gap-3 tw-text-sm">
                <div class="tw-flex tw-justify-between tw-gap-4">
                    <dt class="tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Event') }}</dt>
                    <dd class="tw-font-semibold tw-text-ink-950 dark:tw-text-neutral-50">{{ $order->event?->name ?: __('Event') }}</dd>
                </div>
                <div class="tw-flex tw-justify-between tw-gap-4">
                    <dt class="tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Tickets') }}</dt>
                    <dd class="tw-font-semibold tw-text-ink-950 dark:tw-text-neutral-50">{{ $order->quantity }}</dd>
                </div>
                <div class="tw-flex tw-justify-between tw-gap-4 tw-border-t tw-border-neutral-100 tw-pt-3 dark:tw-border-neutral-800">
                    <dt class="tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Amount due') }}</dt>
                    <dd class="tw-text-base tw-font-extrabold tw-text-ink-950 dark:tw-text-neutral-50">
                        {{ $currency }}{{ number_format($order->payment, 2) }}
                    </dd>
                </div>
            </dl>

            <form id="flutterwave-payment-form" method="POST" action="https://checkout.flutterwave.com/v3/hosted/pay" class="tw-mt-6">
                <input type="hidden" name="public_key" value="{{ $setting->ravePublicKey }}">
                <input type="hidden" name="customer[email]" value="{{ $order->customer?->email }}">
                <input type="hidden" name="customer[phone_number]" value="{{ $order->customer?->phone }}">
                <input type="hidden" name="customer[name]" value="{{ $order->customer?->name }}">
                <input type="hidden" name="tx_ref" value="order-{{ $order->id }}-{{ time() }}">
                <input type="hidden" name="amount" value="{{ $order->payment }}">
                <input type="hidden" name="currency" value="{{ $setting->currency ?? $currency }}">
                <input type="hidden" name="redirect_url" value="{{ $callbackUrl }}">
                <input type="hidden" name="meta[order_id]" value="{{ $order->id }}">

                <x-button type="submit" class="tw-w-full">
                    <x-lucide-credit-card class="tw-h-4 tw-w-4" />
                    {{ __('Pay with Flutterwave') }}
                </x-button>
            </form>

            <p class="tw-mt-4 tw-text-center tw-text-xs tw-leading-relaxed tw-text-neutral-500 dark:tw-text-neutral-400">
                {{ __('This payment link is temporary and can only be used for this order.') }}
            </p>
        </x-card>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('flutterwave-payment-form')?.submit();
    </script>
@endpush
