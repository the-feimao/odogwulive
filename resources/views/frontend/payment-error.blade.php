@extends('layouts.public')

@section('title', $title.' — '.config('brand.name'))

@section('content')
    <div class="tw-mx-auto tw-max-w-xl tw-px-4 tw-py-12 md:tw-px-6">
        <x-card :title="$title">
            <p class="tw-text-sm tw-leading-relaxed tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('We could not open the Paystack payment page for order :order. The order is still pending and no payment has been confirmed.', ['order' => $order->order_id]) }}
            </p>
            <p class="tw-mt-3 tw-text-sm tw-leading-relaxed tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('This usually means the payment provider is temporarily unreachable. Please try again in a moment.') }}
            </p>

            <div class="tw-mt-6 tw-flex tw-flex-wrap tw-items-center tw-gap-3">
                <a href="{{ url('/pay?order='.$order->id) }}"
                    class="tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-5 tw-text-sm tw-font-bold tw-text-ink-950 hover:tw-bg-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600">
                    <x-lucide-refresh-cw class="tw-h-4 tw-w-4" />
                    {{ __('Try payment again') }}
                </a>
                <a href="{{ url('my-tickets') }}"
                    class="tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-border tw-border-neutral-300 tw-px-5 tw-text-sm tw-font-bold tw-text-ink-800 hover:tw-border-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-text-neutral-100">
                    <x-lucide-arrow-left class="tw-h-4 tw-w-4" />
                    {{ __('Back to my tickets') }}
                </a>
            </div>
        </x-card>
    </div>
@endsection
