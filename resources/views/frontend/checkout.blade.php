@extends('layouts.public')

@section('title', __('Checkout').' — '.$data->name.' — '.config('brand.name'))

@php
    $setting = App\Models\PaymentSetting::find(1);
    $isFree = $data->type === 'free';
    $eventImage = $data->event->image
        ? url('images/upload/'.$data->event->image)
        : asset('web/bg/1.jpg');
    $eventUrl = url('/event/'.$data->event->id.'/'.Illuminate\Support\Str::slug($data->event->name));
    $locationLabel = $data->event->type === 'online'
        ? __('Online event')
        : ($data->event->address ?: __('Venue to be announced'));

    $paymentPrice = $data->price;
    if ($data->currency_code === 'USD' || $data->currency_code === 'EUR') {
        $paymentPrice = $data->price * 100;
    }

    $gatewayAmount = $isFree ? 0 : $paymentPrice + $data->tax_total;
    $localChecked = $isFree || $setting->cod == 1
        || ($setting->flutterwave == 0 && $setting->stripe == 0 && $setting->paypal == 0 && $setting->razor == 0);
@endphp

@push('head')
    @if (! $isFree)
        @if ($setting->razor == 1)
            <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
        @endif
        @if ($setting->paypal == 1 && $setting->paypalClientId)
            <script src="https://www.paypal.com/sdk/js?client-id={{ $setting->paypalClientId }}&currency={{ $data->currency_code }}" data-namespace="paypal_sdk"></script>
        @endif
    @endif
@endpush

@section('content')
    <section class="tw-relative tw-overflow-hidden tw-bg-ink-950">
        <img src="{{ $eventImage }}" alt="" width="1600" height="900"
            class="tw-absolute tw-inset-0 tw-h-full tw-w-full tw-object-cover tw-opacity-25">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-r tw-from-ink-950 tw-via-ink-950/90 tw-to-ink-950/40"></div>
        <div class="tw-relative tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
            <nav aria-label="{{ __('Breadcrumb') }}" class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-text-neutral-400">
                <a href="{{ url('/') }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ __('Home') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <a href="{{ $eventUrl }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ $data->event->name }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <span class="tw-text-neutral-200">{{ __('Checkout') }}</span>
            </nav>

            <div class="tw-mt-5 tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-5">
                <div>
                    <x-badge tone="primary">{{ __('Secure checkout') }}</x-badge>
                    <h1 class="tw-mt-3 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-neutral-50 md:tw-text-4xl">
                        {{ __('Complete your order') }}
                    </h1>
                    <p class="tw-mt-2 tw-max-w-2xl tw-text-sm tw-text-neutral-300">
                        {{ $data->name }} · {{ $data->event->start_time?->format('l, F j, Y · g:i A') }}
                    </p>
                </div>

                <ol class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-xs tw-font-bold tw-text-neutral-400">
                    <li class="tw-flex tw-items-center tw-gap-1.5">
                        <span class="tw-flex tw-h-6 tw-w-6 tw-items-center tw-justify-center tw-rounded-pill tw-bg-primary-500 tw-text-ink-950"><x-lucide-check class="tw-h-3.5 tw-w-3.5" /></span>
                        {{ __('Ticket') }}
                    </li>
                    <li class="tw-h-px tw-w-6 tw-bg-neutral-700"></li>
                    <li class="tw-flex tw-items-center tw-gap-1.5 tw-text-neutral-50">
                        <span class="tw-flex tw-h-6 tw-w-6 tw-items-center tw-justify-center tw-rounded-pill tw-bg-primary-500 tw-text-ink-950">2</span>
                        {{ __('Checkout') }}
                    </li>
                    <li class="tw-h-px tw-w-6 tw-bg-neutral-700"></li>
                    <li class="tw-flex tw-items-center tw-gap-1.5">
                        <span class="tw-flex tw-h-6 tw-w-6 tw-items-center tw-justify-center tw-rounded-pill tw-border tw-border-neutral-700">3</span>
                        {{ __('Tickets') }}
                    </li>
                </ol>
            </div>
        </div>
    </section>

    <div class="tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
        <form id="checkout-form" action="{{ url('createOrder') }}" method="post"
            data-currency-symbol="{{ $currency ?? '' }}" class="tw-grid tw-gap-8 lg:tw-grid-cols-[minmax(0,1fr)_360px]">
            @csrf

            <input type="hidden" id="razor_key" name="razor_key" value="{{ $setting->razorPublishKey }}">
            <input type="hidden" id="stripePublicKey" name="stripePublicKey" value="{{ $setting->stripePublicKey }}">
            <input type="hidden" value="{{ $data->ticket_per_order }}" name="tpo" id="tpo">
            <input type="hidden" value="{{ $data->available_qty }}" name="available" id="available">
            <input type="hidden" name="price" id="ticket_price" value="{{ $data->price }}">
            <input type="hidden" name="tax" id="tax_total" value="{{ $isFree ? 0 : $data->tax_total }}">
            <input type="hidden" name="payment" id="payment" value="{{ $gatewayAmount }}">
            <input type="hidden" name="stripe_payment" id="stripe_payment" value="{{ $gatewayAmount }}">
            <input type="hidden" name="currency_code" id="currency_code" value="{{ $data->currency_code }}">
            <input type="hidden" name="payment_token" id="payment_token">
            <input type="hidden" name="ticket_id" id="ticket_id" value="{{ $data->id }}">
            <input type="hidden" name="coupon_id" id="coupon_id" value="">
            <input type="hidden" name="coupon_discount" id="coupon_discount" value="0">

            <div class="tw-space-y-6">
                <x-card :title="__('Choose your quantity')" :subtitle="__('You can order up to the ticket limit for this event.')">
                    <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-5">
                        <div class="tw-flex tw-items-center tw-gap-3">
                            <div class="tw-flex tw-h-11 tw-w-11 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-pill tw-bg-primary-100 tw-text-primary-900 dark:tw-bg-primary-500/20 dark:tw-text-primary-300">
                                <x-lucide-ticket class="tw-h-5 tw-w-5" />
                            </div>
                            <div>
                                <p class="tw-text-base tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">{{ $data->name }}</p>
                                <p class="tw-mt-0.5 tw-font-mono tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $data->ticket_number }}</p>
                            </div>
                        </div>

                        <div>
                            <p class="tw-mb-2 tw-text-center tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Quantity') }}</p>
                            <div class="tw-flex tw-h-11 tw-items-center tw-overflow-hidden tw-rounded-pill tw-border tw-border-neutral-300 dark:tw-border-neutral-700">
                                <button type="button" data-qty-action="dec"
                                    class="tw-flex tw-h-full tw-w-11 tw-items-center tw-justify-center tw-text-neutral-600 tw-transition-colors hover:tw-bg-neutral-100 disabled:tw-cursor-not-allowed disabled:tw-opacity-40 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-inset focus-visible:tw-ring-primary-600 dark:tw-text-neutral-300 dark:hover:tw-bg-neutral-800">
                                    <x-lucide-minus class="tw-h-4 tw-w-4" />
                                    <span class="tw-sr-only">{{ __('Decrease quantity') }}</span>
                                </button>
                                <input type="number" readonly name="quantity" id="quantity" value="1"
                                    class="tw-h-full tw-w-14 tw-border-0 tw-bg-transparent tw-text-center tw-text-base tw-font-bold tw-text-ink-950 focus:tw-outline-none dark:tw-text-neutral-50">
                                <button type="button" data-qty-action="inc"
                                    class="tw-flex tw-h-full tw-w-11 tw-items-center tw-justify-center tw-text-neutral-600 tw-transition-colors hover:tw-bg-neutral-100 disabled:tw-cursor-not-allowed disabled:tw-opacity-40 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-inset focus-visible:tw-ring-primary-600 dark:tw-text-neutral-300 dark:hover:tw-bg-neutral-800">
                                    <x-lucide-plus class="tw-h-4 tw-w-4" />
                                    <span class="tw-sr-only">{{ __('Increase quantity') }}</span>
                                </button>
                            </div>
                            <p class="tw-mt-2 tw-text-center tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                {{ $data->available_qty }} {{ __('tickets left') }}
                            </p>
                        </div>
                    </div>

                    <div class="tw-mt-5 tw-grid tw-gap-3 tw-border-t tw-border-neutral-100 tw-pt-5 tw-text-sm md:tw-grid-cols-3 dark:tw-border-neutral-800">
                        <div>
                            <p class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Sales start') }}</p>
                            <p class="tw-mt-1 tw-text-ink-950 dark:tw-text-neutral-200">{{ $data->start_time->format('M j, g:i A') }}</p>
                        </div>
                        <div>
                            <p class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Sales end') }}</p>
                            <p class="tw-mt-1 tw-text-ink-950 dark:tw-text-neutral-200">{{ $data->end_time->format('M j, g:i A') }}</p>
                        </div>
                        <div>
                            <p class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Ticket type') }}</p>
                            <p class="tw-mt-1 tw-text-ink-950 dark:tw-text-neutral-200">{{ $isFree ? __('Free') : __('Paid') }}</p>
                        </div>
                    </div>

                    @if ($data->description)
                        <p class="tw-mt-4 tw-text-sm tw-leading-relaxed tw-text-neutral-600 dark:tw-text-neutral-300">{{ $data->description }}</p>
                    @endif
                </x-card>

                <x-card :title="__('Choose payment method')" :subtitle="__('Select how you would like to pay for this order.')">
                    <div class="tw-grid tw-gap-3 sm:tw-grid-cols-2">
                        @if ($isFree)
                            <label class="tw-flex tw-cursor-pointer tw-items-center tw-gap-3 tw-rounded-card tw-border tw-border-neutral-200 tw-p-4 tw-transition-colors hover:tw-border-primary-400 focus-within:tw-ring-2 focus-within:tw-ring-primary-600 dark:tw-border-neutral-700">
                                <input type="radio" name="payment_type" value="FREE" checked class="tw-peer tw-sr-only">
                                <span class="tw-flex tw-h-5 tw-w-5 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-pill tw-border-2 tw-border-neutral-300 tw-text-transparent peer-checked:tw-border-primary-600 peer-checked:tw-bg-primary-500 peer-checked:tw-text-ink-950 dark:tw-border-neutral-600">
                                    <x-lucide-check class="tw-h-3 tw-w-3" />
                                </span>
                                <span class="tw-text-sm tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">{{ __('FREE') }}</span>
                            </label>
                        @else
                            @if ($setting->paypal == 1)
                                <label class="tw-flex tw-cursor-pointer tw-items-center tw-gap-3 tw-rounded-card tw-border tw-border-neutral-200 tw-p-4 tw-transition-colors hover:tw-border-primary-400 focus-within:tw-ring-2 focus-within:tw-ring-primary-600 dark:tw-border-neutral-700">
                                    <input type="radio" name="payment_type" value="PAYPAL" class="tw-peer tw-sr-only">
                                    <span class="tw-flex tw-h-5 tw-w-5 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-pill tw-border-2 tw-border-neutral-300 tw-text-transparent peer-checked:tw-border-primary-600 peer-checked:tw-bg-primary-500 peer-checked:tw-text-ink-950 dark:tw-border-neutral-600">
                                        <x-lucide-check class="tw-h-3 tw-w-3" />
                                    </span>
                                    <span class="tw-text-sm tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">{{ __('PayPal') }}</span>
                                </label>
                            @endif

                            @if ($setting->razor == 1)
                                <label class="tw-flex tw-cursor-pointer tw-items-center tw-gap-3 tw-rounded-card tw-border tw-border-neutral-200 tw-p-4 tw-transition-colors hover:tw-border-primary-400 focus-within:tw-ring-2 focus-within:tw-ring-primary-600 dark:tw-border-neutral-700">
                                    <input type="radio" name="payment_type" value="RAZOR" class="tw-peer tw-sr-only">
                                    <span class="tw-flex tw-h-5 tw-w-5 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-pill tw-border-2 tw-border-neutral-300 tw-text-transparent peer-checked:tw-border-primary-600 peer-checked:tw-bg-primary-500 peer-checked:tw-text-ink-950 dark:tw-border-neutral-600">
                                        <x-lucide-check class="tw-h-3 tw-w-3" />
                                    </span>
                                    <span class="tw-text-sm tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">{{ __('Razorpay') }}</span>
                                </label>
                            @endif

                            @if ($setting->stripe == 1)
                                <label class="tw-flex tw-cursor-pointer tw-items-center tw-gap-3 tw-rounded-card tw-border tw-border-neutral-200 tw-p-4 tw-transition-colors hover:tw-border-primary-400 focus-within:tw-ring-2 focus-within:tw-ring-primary-600 dark:tw-border-neutral-700">
                                    <input type="radio" name="payment_type" value="STRIPE" class="tw-peer tw-sr-only">
                                    <span class="tw-flex tw-h-5 tw-w-5 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-pill tw-border-2 tw-border-neutral-300 tw-text-transparent peer-checked:tw-border-primary-600 peer-checked:tw-bg-primary-500 peer-checked:tw-text-ink-950 dark:tw-border-neutral-600">
                                        <x-lucide-check class="tw-h-3 tw-w-3" />
                                    </span>
                                    <span class="tw-text-sm tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">{{ __('Stripe') }}</span>
                                </label>
                            @endif

                            @if ($setting->flutterwave == 1)
                                <label class="tw-flex tw-cursor-pointer tw-items-center tw-gap-3 tw-rounded-card tw-border tw-border-neutral-200 tw-p-4 tw-transition-colors hover:tw-border-primary-400 focus-within:tw-ring-2 focus-within:tw-ring-primary-600 dark:tw-border-neutral-700">
                                    <input type="radio" name="payment_type" value="FLUTTERWAVE" class="tw-peer tw-sr-only">
                                    <span class="tw-flex tw-h-5 tw-w-5 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-pill tw-border-2 tw-border-neutral-300 tw-text-transparent peer-checked:tw-border-primary-600 peer-checked:tw-bg-primary-500 peer-checked:tw-text-ink-950 dark:tw-border-neutral-600">
                                        <x-lucide-check class="tw-h-3 tw-w-3" />
                                    </span>
                                    <span class="tw-text-sm tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">{{ __('Flutterwave') }}</span>
                                </label>
                            @endif

                            @if ($setting->cod == 1 || ($setting->flutterwave == 0 && $setting->stripe == 0 && $setting->paypal == 0 && $setting->razor == 0))
                                <label class="tw-flex tw-cursor-pointer tw-items-center tw-gap-3 tw-rounded-card tw-border tw-border-neutral-200 tw-p-4 tw-transition-colors hover:tw-border-primary-400 focus-within:tw-ring-2 focus-within:tw-ring-primary-600 dark:tw-border-neutral-700">
                                    <input type="radio" name="payment_type" value="LOCAL" checked class="tw-peer tw-sr-only">
                                    <span class="tw-flex tw-h-5 tw-w-5 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-pill tw-border-2 tw-border-neutral-300 tw-text-transparent peer-checked:tw-border-primary-600 peer-checked:tw-bg-primary-500 peer-checked:tw-text-ink-950 dark:tw-border-neutral-600">
                                        <x-lucide-check class="tw-h-3 tw-w-3" />
                                    </span>
                                    <span class="tw-text-sm tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">{{ __('Cash Transaction') }}</span>
                                </label>
                            @endif
                        @endif
                    </div>

                    <p id="checkout-payment-status" hidden
                        class="tw-mt-4 tw-rounded-stub tw-bg-danger-50 tw-px-3 tw-py-2 tw-text-sm tw-font-semibold tw-text-danger-700 dark:tw-bg-danger-500/10 dark:tw-text-danger-300"></p>

                    <div id="paypal-button-section" hidden class="tw-mt-5 tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-50 tw-p-4 dark:tw-border-neutral-700 dark:tw-bg-ink-900">
                        <div id="paypal-button-container"></div>
                    </div>

                    <div id="stripe-form-section" hidden class="tw-mt-5 tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-50 tw-p-4 dark:tw-border-neutral-700 dark:tw-bg-ink-900">
                        <div id="stripe-form"></div>
                    </div>
                </x-card>
            </div>

            <aside class="tw-space-y-6 lg:tw-sticky lg:tw-top-24 lg:tw-self-start">
                <x-card :title="__('Order summary')">
                    <img src="{{ $eventImage }}" alt="{{ $data->event->name }}" width="400" height="240" loading="lazy"
                        class="tw-aspect-video tw-w-full tw-rounded-stub tw-object-cover">
                    <div class="tw-mt-4">
                        <p class="tw-text-base tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">{{ $data->event->name }}</p>
                        <p class="tw-mt-1 tw-flex tw-items-center tw-gap-1.5 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                            <x-lucide-calendar-days class="tw-h-4 tw-w-4" />
                            {{ $data->event->start_time?->format('l, F j, Y · g:i A') }}
                        </p>
                        <p class="tw-mt-1 tw-flex tw-items-center tw-gap-1.5 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                            <x-lucide-map-pin class="tw-h-4 tw-w-4" />
                            {{ $locationLabel }}
                        </p>
                    </div>

                    <div class="tw-mt-5 tw-space-y-3 tw-border-t tw-border-neutral-100 tw-pt-5 tw-text-sm dark:tw-border-neutral-800">
                        <div class="tw-flex tw-justify-between tw-gap-3">
                            <span class="tw-text-neutral-500 dark:tw-text-neutral-400">{{ $data->name }} × <span data-checkout-qty>1</span></span>
                            <span class="tw-font-semibold tw-text-ink-950 dark:tw-text-neutral-100" data-checkout-subtotal>
                                {{ $isFree ? $currency.'0' : $currency.number_format($data->price) }}
                            </span>
                        </div>

                        @if (! $isFree)
                            <input type="hidden" name="tax_data" value="{{ $data->tax->toJson() }}">
                            @forelse ($data->tax as $item)
                                <div class="tw-flex tw-justify-between tw-gap-3">
                                    <span class="tw-text-neutral-500 dark:tw-text-neutral-400">+ {{ $item->name }}</span>
                                    <span class="tw-font-semibold tw-text-ink-950 dark:tw-text-neutral-100">{{ $currency.$item->price }}</span>
                                </div>
                            @empty
                                <div class="tw-flex tw-justify-between tw-gap-3">
                                    <span class="tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Tax') }}</span>
                                    <span class="tw-font-semibold tw-text-ink-950 dark:tw-text-neutral-100" data-checkout-tax>{{ $currency.'0' }}</span>
                                </div>
                            @endforelse
                        @else
                            <div class="tw-flex tw-justify-between tw-gap-3">
                                <span class="tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Tax') }}</span>
                                <span class="tw-font-semibold tw-text-ink-950 dark:tw-text-neutral-100" data-checkout-tax>{{ $currency.'0' }}</span>
                            </div>
                        @endif

                        <div class="tw-flex tw-justify-between tw-gap-3 tw-border-t tw-border-neutral-100 tw-pt-4 tw-text-base tw-font-extrabold tw-text-ink-950 dark:tw-border-neutral-800 dark:tw-text-neutral-50">
                            <span>{{ __('Total') }}</span>
                            <span data-checkout-total>
                                {{ $isFree ? $currency.'0.00' : $currency.number_format($data->price + $data->tax_total) }}
                            </span>
                        </div>
                    </div>

                    <div class="tw-mt-5 tw-rounded-card tw-border tw-border-dashed tw-border-neutral-300 tw-p-4 dark:tw-border-neutral-700">
                        <label for="coupon_code" class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">
                            {{ __('Coupon code') }}
                        </label>
                        <div class="tw-mt-2 tw-flex tw-gap-2">
                            <input id="coupon_code" type="text" disabled placeholder="{{ __('Enter a code') }}"
                                class="tw-h-10 tw-min-w-0 tw-flex-1 tw-rounded-stub tw-border tw-border-neutral-300 tw-bg-neutral-100 tw-px-3 tw-text-sm tw-text-neutral-500 focus:tw-outline-none dark:tw-border-neutral-700 dark:tw-bg-ink-950">
                            <button type="button" disabled
                                class="tw-h-10 tw-rounded-pill tw-bg-neutral-200 tw-px-4 tw-text-sm tw-font-bold tw-text-neutral-500 dark:tw-bg-neutral-800 dark:tw-text-neutral-400">
                                {{ __('Apply') }}
                            </button>
                        </div>
                        <p class="tw-mt-2 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                            {{ __('Coupon codes are applied from the mobile app.') }}
                        </p>
                    </div>
                </x-card>

                <div class="tw-rounded-card tw-border tw-border-primary-100 tw-bg-primary-50/70 tw-p-5 dark:tw-border-primary-500/20 dark:tw-bg-primary-500/10">
                    <p class="tw-flex tw-items-center tw-gap-2 tw-text-sm tw-font-bold tw-text-primary-900 dark:tw-text-primary-200">
                        <x-lucide-shield-check class="tw-h-4 tw-w-4" />
                        {{ __('Your order is protected') }}
                    </p>
                    <p class="tw-mt-2 tw-text-sm tw-leading-relaxed tw-text-primary-900/80 dark:tw-text-primary-200/80">
                        {{ __('Tickets are issued to your account after the order is confirmed.') }}
                    </p>
                </div>

                <button type="submit" id="form_submit"
                    class="tw-inline-flex tw-h-12 tw-w-full tw-items-center tw-justify-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-text-base tw-font-bold tw-text-ink-950 tw-shadow-card tw-transition-colors hover:tw-bg-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600 disabled:tw-opacity-60">
                    <x-lucide-lock class="tw-h-4 tw-w-4" />
                    {{ __('Place Order') }}
                </button>

                <a href="{{ $eventUrl }}"
                    class="tw-flex tw-items-center tw-justify-center tw-gap-1.5 tw-text-sm tw-font-bold tw-text-neutral-500 hover:tw-text-ink-950 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-neutral-400 dark:hover:tw-text-neutral-50">
                    <x-lucide-arrow-left class="tw-h-4 tw-w-4" />
                    {{ __('Back to event') }}
                </a>
            </aside>
        </form>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/checkout.js')
@endpush
