@extends('layouts.public')

@section('title', __('My Tickets').' — '.config('brand.name'))

@php
    $upcoming = $ticket['upcoming'] ?? collect();
    $past = $ticket['past'] ?? collect();
    $first = $upcoming->first() ?? $past->first();
    $review = $first ? App\Models\Review::where('order_id', $first->id)->first() : null;
    $showReview = $first && ((! $review && $first->order_status === 'Complete') || $first->order_status === 'Cancel');
    $isFree = $first && $first->ticket?->type === 'free';
    $currencySymbol = trim((string) ($currency ?? ''));

    $imageUrl = function ($image) {
        return $image ? url('images/upload/'.$image) : asset('web/bg/1.jpg');
    };
    $money = function ($value) use ($currencySymbol) {
        return $currencySymbol.number_format((float) $value);
    };

    $statusClasses = [
        'Pending' => 'tw-bg-warning-100 tw-text-warning-700 dark:tw-bg-warning-500/20 dark:tw-text-warning-200',
        'Complete' => 'tw-bg-success-100 tw-text-success-700 dark:tw-bg-success-500/20 dark:tw-text-success-200',
        'Cancel' => 'tw-bg-danger-100 tw-text-danger-700 dark:tw-bg-danger-500/20 dark:tw-text-danger-200',
    ];
    $statusClass = $statusClasses[$first?->order_status] ?? $statusClasses['Pending'];
    $paidClass = 'tw-bg-success-100 tw-text-success-700 dark:tw-bg-success-500/20 dark:tw-text-success-200';
    $waitingClass = 'tw-bg-warning-100 tw-text-warning-700 dark:tw-bg-warning-500/20 dark:tw-text-warning-200';
    $badgeBase = 'tw-inline-flex tw-items-center tw-rounded-pill tw-px-2.5 tw-py-1 tw-text-xs tw-font-bold';
@endphp

@section('content')
    <section class="tw-relative tw-overflow-hidden tw-bg-ink-950">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-br tw-from-ink-950 tw-via-ink-900 tw-to-primary-900/60"></div>
        <div class="tw-relative tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
            <nav aria-label="{{ __('Breadcrumb') }}" class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-text-neutral-400">
                <a href="{{ url('/') }}" class="focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500 hover:tw-text-neutral-50">{{ __('Home') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <a href="{{ url('user/profile') }}" class="focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500 hover:tw-text-neutral-50">{{ __('My profile') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <span class="tw-text-neutral-200">{{ __('My Tickets') }}</span>
            </nav>

            <div class="tw-mt-6 tw-max-w-2xl">
                <x-badge tone="primary"><x-slot:icon><x-lucide-ticket class="tw-h-3.5 tw-w-3.5" /></x-slot:icon>{{ __('Your orders') }}</x-badge>
                <h1 class="tw-mt-3 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-neutral-50 md:tw-text-4xl">
                    {{ __('My Tickets') }}
                </h1>
                <p class="tw-mt-2 tw-text-sm tw-leading-relaxed tw-text-neutral-300">
                    {{ __('Review an order, check payment details or print your invoice.') }}
                </p>
            </div>
        </div>
    </section>

    <div class="tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14"
        data-user-area
        data-tickets-page
        data-currency-symbol="{{ $currencySymbol }}"
        data-image-base="{{ url('images/upload') }}"
        data-image-fallback="{{ asset('web/bg/1.jpg') }}"
        data-order-endpoint="{{ url('/getOrder') }}"
        data-invoice-base="{{ url('/my-tickets') }}"
        data-qr-base="{{ url('/my-tickets') }}">
        <p id="user-area-status" hidden
            class="tw-mb-5 tw-rounded-stub tw-border tw-border-primary-200 tw-bg-primary-50 tw-px-4 tw-py-3 tw-text-sm tw-font-semibold tw-text-primary-900 dark:tw-border-primary-500/30 dark:tw-bg-primary-500/10 dark:tw-text-primary-200"
            aria-live="polite"></p>

        @if (! $first)
            <x-empty-state title="{{ __('No Tickets found') }}"
                description="{{ __('When you book an event, your tickets will appear here.') }}">
                <x-slot:icon><x-lucide-ticket class="tw-h-6 tw-w-6" /></x-slot:icon>
                <x-slot:action>
                    <a href="{{ url('/all-events') }}"
                        class="tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-5 tw-text-sm tw-font-bold tw-text-ink-950 hover:tw-bg-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600">
                        {{ __('Browse events') }}
                    </a>
                </x-slot:action>
            </x-empty-state>
        @else
            <div class="tw-grid tw-gap-8 lg:tw-grid-cols-[320px_minmax(0,1fr)]">
                <aside class="lg:tw-sticky lg:tw-top-24 lg:tw-self-start">
                    <x-card :title="__('Your orders')" :subtitle="__('Choose an order to see its details.')">
                        @if ($upcoming->isNotEmpty())
                            <div class="tw-mb-5">
                                <h2 class="tw-flex tw-items-center tw-gap-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">
                                    {{ __('Upcoming Events') }}
                                    <span class="tw-inline-flex tw-h-5 tw-min-w-5 tw-items-center tw-justify-center tw-rounded-pill tw-bg-primary-100 tw-px-1.5 tw-text-2xs tw-text-primary-900 dark:tw-bg-primary-500/20 dark:tw-text-primary-200">{{ count($upcoming) }}</span>
                                </h2>
                                <div class="tw-mt-3 tw-space-y-2">
                                    @foreach ($upcoming as $item)
                                        <button type="button" id="order-{{ $item->id }}" data-order-id="{{ $item->id }}"
                                            class="event-data tw-flex tw-w-full tw-items-center tw-gap-3 tw-rounded-card tw-border tw-p-2.5 tw-text-left tw-transition-colors focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600 {{ $loop->first && $upcoming->contains($first) ? 'active tw-border-primary-400 tw-bg-primary-50 dark:tw-bg-primary-500/10' : 'tw-border-neutral-200 hover:tw-border-primary-300 dark:tw-border-neutral-800' }}">
                                            <img src="{{ $imageUrl($item->event?->image) }}" alt="" width="56" height="56" loading="lazy"
                                                class="tw-h-14 tw-w-14 tw-shrink-0 tw-rounded-stub tw-object-cover">
                                            <span class="tw-min-w-0 tw-flex-1">
                                                <span class="tw-flex tw-items-center tw-gap-1.5">
                                                    <span class="tw-inline-block tw-h-1.5 tw-w-1.5 tw-shrink-0 tw-rounded-pill {{ $statusClasses[$item->order_status] ?? $statusClasses['Pending'] }}"></span>
                                                    <span class="tw-truncate tw-text-2xs tw-font-bold tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item->order_id }}</span>
                                                </span>
                                                <span class="tw-mt-0.5 tw-block tw-truncate tw-text-sm tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">{{ $item->event?->name }}</span>
                                                <span class="tw-mt-0.5 tw-block tw-truncate tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                                    {{ $item->event?->start_time?->format('D, d M Y') }} · {{ $item->quantity }} {{ __('tickets') }}
                                                </span>
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @if ($past->isNotEmpty())
                            <div>
                                <h2 class="tw-flex tw-items-center tw-gap-2 tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">
                                    {{ __('Past Events') }}
                                    <span class="tw-inline-flex tw-h-5 tw-min-w-5 tw-items-center tw-justify-center tw-rounded-pill tw-bg-neutral-100 tw-px-1.5 tw-text-2xs tw-text-neutral-600 dark:tw-bg-neutral-800 dark:tw-text-neutral-300">{{ count($past) }}</span>
                                </h2>
                                <div class="tw-mt-3 tw-space-y-2">
                                    @foreach ($past as $item)
                                        <button type="button" id="order-{{ $item->id }}" data-order-id="{{ $item->id }}"
                                            class="event-data tw-flex tw-w-full tw-items-center tw-gap-3 tw-rounded-card tw-border tw-p-2.5 tw-text-left tw-transition-colors focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600 {{ $upcoming->isEmpty() && $loop->first ? 'active tw-border-primary-400 tw-bg-primary-50 dark:tw-bg-primary-500/10' : 'tw-border-neutral-200 hover:tw-border-primary-300 dark:tw-border-neutral-800' }}">
                                            <img src="{{ $imageUrl($item->event?->image) }}" alt="" width="56" height="56" loading="lazy"
                                                class="tw-h-14 tw-w-14 tw-shrink-0 tw-rounded-stub tw-object-cover">
                                            <span class="tw-min-w-0 tw-flex-1">
                                                <span class="tw-flex tw-items-center tw-gap-1.5">
                                                    <span class="tw-inline-block tw-h-1.5 tw-w-1.5 tw-shrink-0 tw-rounded-pill {{ $statusClasses[$item->order_status] ?? $statusClasses['Pending'] }}"></span>
                                                    <span class="tw-truncate tw-text-2xs tw-font-bold tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item->order_id }}</span>
                                                </span>
                                                <span class="tw-mt-0.5 tw-block tw-truncate tw-text-sm tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">{{ $item->event?->name }}</span>
                                                <span class="tw-mt-0.5 tw-block tw-truncate tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                                    {{ $item->event?->start_time?->format('D, d M Y') }} · {{ $item->quantity }} {{ __('tickets') }}
                                                </span>
                                            </span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </x-card>
                </aside>

                <x-card :padded="false">
                    <div class="single-order" data-order-detail>
                        <header class="tw-relative tw-overflow-hidden tw-bg-ink-950 tw-p-5 md:tw-p-6">
                            <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-r tw-from-ink-950 tw-via-ink-950/90 tw-to-primary-900/50"></div>
                            <div class="tw-relative">
                                <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3">
                                    <p data-detail="order-id" class="tw-font-mono tw-text-xs tw-font-bold tw-text-primary-300">{{ $first->order_id }}</p>
                                    <span data-detail="status" class="{{ $badgeBase }} {{ $statusClass }}">{{ $first->order_status }}</span>
                                </div>
                                <h2 data-detail="time" class="tw-mt-2 tw-text-xl tw-font-extrabold tw-leading-snug tw-text-neutral-50 md:tw-text-2xl">
                                    {{ $first->event?->start_time?->format('D').', '.$first->event?->start_time?->format('d M Y').' at '.$first->event?->start_time?->format('h:i a') }}
                                </h2>

                                <div data-detail="review-block" @if (! $review) hidden @endif class="tw-mt-3 tw-flex tw-items-center tw-gap-2">
                                    <span class="tw-text-xs tw-font-semibold tw-text-neutral-400">{{ __('Your rating') }}</span>
                                    <span class="tw-inline-flex tw-items-center tw-gap-0.5" aria-label="{{ __('Rating') }}">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <span data-review-star class="{{ $review && $review->rate >= $i ? 'tw-fill-current tw-text-accent-400' : 'tw-text-neutral-600' }}">
                                                <x-lucide-star class="tw-h-4 tw-w-4" />
                                            </span>
                                        @endfor
                                    </span>
                                </div>
                            </div>
                        </header>

                        <div class="tw-grid tw-gap-5 tw-p-5 md:tw-grid-cols-[150px_minmax(0,1fr)_auto] md:tw-p-6">
                            <img data-detail="event-image" src="{{ $imageUrl($first->event?->image) }}" alt="{{ $first->event?->name }}"
                                width="300" height="200" loading="lazy" class="tw-aspect-[3/2] tw-w-full tw-rounded-stub tw-object-cover">

                            <div class="tw-min-w-0">
                                <h3 data-detail="event-name" class="tw-text-lg tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">
                                    {{ $first->event?->name }}
                                </h3>
                                <p class="tw-mt-1 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                                    {{ __('By') }}: <span data-detail="organizer">{{ $first->organization?->first_name.' '.$first->organization?->last_name }}</span>
                                </p>
                                <p class="tw-mt-3 tw-text-sm tw-leading-relaxed tw-text-neutral-600 dark:tw-text-neutral-300">
                                    <span data-detail="start">{{ $first->event?->start_time?->format('d M Y').', '.$first->event?->start_time?->format('h:i a') }}</span>
                                    {{ __('to') }}
                                    <span data-detail="end">{{ $first->event?->end_time?->format('d M Y').', '.$first->event?->end_time?->format('h:i a') }}</span>
                                </p>
                                <p data-detail="location" class="tw-mt-1 tw-flex tw-items-center tw-gap-1.5 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                                    <x-lucide-map-pin class="tw-h-4 tw-w-4" />
                                    {{ $first->event?->type === 'online' ? __('Online Event') : ($first->event?->address ?: __('Venue to be announced')) }}
                                </p>
                            </div>

                            <div class="tw-flex tw-flex-wrap tw-items-start tw-gap-2 md:tw-flex-col">
                                <button type="button" onclick="viewPayment()"
                                    class="tw-inline-flex tw-h-9 tw-items-center tw-gap-2 tw-rounded-pill tw-border tw-border-neutral-200 tw-px-3 tw-text-xs tw-font-bold tw-text-ink-800 tw-transition-colors hover:tw-border-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600 dark:tw-border-neutral-700 dark:tw-text-neutral-100">
                                    <x-lucide-credit-card class="tw-h-4 tw-w-4" />{{ __('Payment') }}
                                </button>
                                <button type="button" data-detail="review-action" class="open-addReview tw-inline-flex tw-h-9 tw-items-center tw-gap-2 tw-rounded-pill tw-border tw-border-neutral-200 tw-px-3 tw-text-xs tw-font-bold tw-text-ink-800 tw-transition-colors hover:tw-border-accent-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-text-neutral-100"
                                    data-toggle="modal" data-target="#reviewModel" data-id="{{ $first->id }}" data-order="{{ $first->order_id }}"
                                    @if (! $showReview) hidden @endif>
                                    <x-lucide-star class="tw-h-4 tw-w-4" />{{ __('Review') }}
                                </button>
                                <a href="{{ url('my-tickets/'.$first->id.'/invoice-print') }}" target="_blank" rel="noopener" id="print-btn" data-detail="invoice-link"
                                    class="tw-inline-flex tw-h-9 tw-items-center tw-gap-2 tw-rounded-pill tw-border tw-border-neutral-200 tw-px-3 tw-text-xs tw-font-bold tw-text-ink-800 tw-transition-colors hover:tw-border-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600 dark:tw-border-neutral-700 dark:tw-text-neutral-100">
                                    <x-lucide-printer class="tw-h-4 tw-w-4" />{{ __('Invoice') }}
                                </a>
                            </div>
                        </div>

                        <div id="payment-data" class="payment-data tw-grid tw-gap-3 tw-border-t tw-border-neutral-100 tw-bg-neutral-50 tw-p-5 tw-text-sm sm:tw-grid-cols-3 dark:tw-border-neutral-800 dark:tw-bg-ink-950" hidden>
                            <div>
                                <p class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Payment Method') }}</p>
                                <p data-detail="payment-method" class="tw-mt-1 tw-font-semibold tw-text-ink-950 dark:tw-text-neutral-100">{{ $first->payment_type }}</p>
                            </div>
                            <div>
                                <p class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Payment Token') }}</p>
                                <p data-detail="payment-token" class="tw-mt-1 tw-break-all tw-font-mono tw-text-xs tw-text-ink-950 dark:tw-text-neutral-100">{{ $first->payment_token ?: '-' }}</p>
                            </div>
                            <div>
                                <p class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Status') }}</p>
                                <span data-detail="payment-status" class="{{ $badgeBase }} {{ $first->payment_status == 1 ? $paidClass : $waitingClass }}">{{ $first->payment_status == 1 ? __('Paid') : __('Waiting') }}</span>
                            </div>
                        </div>

                        <div class="order-bottom tw-space-y-3 tw-border-t tw-border-neutral-100 tw-p-5 tw-text-sm dark:tw-border-neutral-800 md:tw-p-6">
                            <div class="tw-flex tw-items-start tw-justify-between tw-gap-4">
                                <div>
                                    <p data-detail="ticket-name" class="tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">{{ $first->ticket?->name }}</p>
                                    <p class="tw-mt-0.5 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                        <span data-detail="ticket-quantity">{{ $first->quantity }}</span> × {{ $first->ticket?->ticket_number }}
                                    </p>
                                </div>
                                <span data-detail="subtotal" class="tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">
                                    {{ $isFree ? __('FREE') : $money($first->ticket->price * $first->quantity) }}
                                </span>
                            </div>
                            <div class="tw-flex tw-justify-between tw-gap-4 tw-text-neutral-500 dark:tw-text-neutral-400">
                                <span>{{ __('Coupon discount') }}</span>
                                <span data-detail="discount">{{ $isFree ? '0.00' : '(-) '.$money($first->coupon_discount) }}</span>
                            </div>
                            <div class="tw-flex tw-justify-between tw-gap-4 tw-text-neutral-500 dark:tw-text-neutral-400">
                                <span>{{ __('Tax') }}</span>
                                <span data-detail="tax">{{ $isFree ? '0.00' : '(+) '.$money($first->tax) }}</span>
                            </div>
                            <div class="tw-flex tw-justify-between tw-gap-4 tw-border-t tw-border-neutral-100 tw-pt-3 tw-text-base tw-font-extrabold tw-text-ink-950 dark:tw-border-neutral-800 dark:tw-text-neutral-50">
                                <span>{{ __('Total') }}</span>
                                <span data-detail="total">{{ $isFree ? __('FREE') : $money($first->payment) }}</span>
                            </div>
                        </div>

                        <section class="tw-border-t tw-border-neutral-100 tw-p-5 dark:tw-border-neutral-800 md:tw-p-6"
                            aria-labelledby="qr-tickets-heading">
                            <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-3">
                                <div>
                                    <h3 id="qr-tickets-heading" class="tw-text-lg tw-font-extrabold tw-tracking-tight tw-text-ink-950 dark:tw-text-neutral-50">
                                        {{ __('Your QR tickets') }}
                                    </h3>
                                    <p class="tw-mt-1 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                                        {{ __('Each ticket has its own code. Show one code per person at the entrance.') }}
                                    </p>
                                </div>

                                <div class="tw-flex tw-items-center tw-gap-2">
                                    <span data-detail="ticket-count"
                                        class="tw-inline-flex tw-h-8 tw-items-center tw-rounded-pill tw-bg-primary-100 tw-px-3 tw-text-xs tw-font-bold tw-text-primary-900 dark:tw-bg-primary-500/15 dark:tw-text-primary-200">
                                        {{ $firstChildren->count() }} {{ $firstChildren->count() === 1 ? __('ticket') : __('tickets') }}
                                    </span>
                                    <a href="{{ url('my-tickets/'.$first->id.'/invoice-print') }}" target="_blank" rel="noopener"
                                        data-detail="tickets-print-link"
                                        class="tw-inline-flex tw-h-8 tw-items-center tw-gap-2 tw-rounded-pill tw-border tw-border-neutral-200 tw-px-3 tw-text-xs tw-font-bold tw-text-ink-800 tw-transition-colors hover:tw-border-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600 dark:tw-border-neutral-700 dark:tw-text-neutral-100">
                                        <x-lucide-printer class="tw-h-3.5 tw-w-3.5" />{{ __('Print tickets') }}
                                    </a>
                                </div>
                            </div>

                            <div class="tw-mt-5" data-order-qr-content>
                                @include('frontend.partials.order-ticket-qrs', [
                                    'order' => $first,
                                    'children' => $firstChildren,
                                    'paid' => $firstPaid,
                                ])
                            </div>
                        </section>
                    </div>
                </x-card>
            </div>
        @endif
    </div>

    <div id="reviewModel" role="dialog" aria-modal="true" aria-labelledby="exampleModalLongTitle" data-review-modal hidden
        class="tw-fixed tw-inset-0 tw-z-50 tw-hidden tw-items-center tw-justify-center tw-bg-ink-950/70 tw-p-4">
        <div data-review-close class="tw-absolute tw-inset-0" aria-hidden="true"></div>
        <div class="tw-relative tw-w-full tw-max-w-lg tw-max-h-[90vh] tw-overflow-y-auto tw-rounded-card tw-bg-neutral-0 tw-shadow-card-hover dark:tw-bg-ink-900">
            <div class="tw-flex tw-items-start tw-justify-between tw-gap-4 tw-border-b tw-border-neutral-100 tw-px-5 tw-py-4 dark:tw-border-neutral-800">
                <h2 id="exampleModalLongTitle" class="tw-text-lg tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">{{ __('Add Review') }}</h2>
                <button type="button" data-review-close aria-label="{{ __('Close') }}"
                    class="tw-rounded-pill tw-p-1 tw-text-neutral-500 hover:tw-bg-neutral-100 hover:tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:hover:tw-bg-neutral-800 dark:hover:tw-text-neutral-50">
                    <x-lucide-x class="tw-h-5 tw-w-5" />
                </button>
            </div>

            <form method="post" action="{{ url('add-review') }}">
                @csrf
                <div class="tw-space-y-5 tw-p-5">
                    <div>
                        <input type="hidden" name="order_id" id="order_id" value="{{ $first?->id }}">
                        <p class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">{{ __('Rate') }}</p>
                        <input type="hidden" name="rate" required>
                        <div class="tw-mt-2 tw-flex tw-items-center tw-gap-1" role="group" aria-label="{{ __('Rating') }}">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" id="rate-{{ $i }}" data-rate="{{ $i }}" onclick="addRate('{{ $i }}')" aria-pressed="false"
                                    aria-label="{{ $i }} {{ __('star') }}"
                                    class="tw-flex tw-h-10 tw-w-10 tw-items-center tw-justify-center tw-rounded-pill tw-bg-neutral-100 tw-text-neutral-400 tw-transition-colors focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-bg-neutral-800">
                                    <x-lucide-star class="tw-h-5 tw-w-5" />
                                </button>
                            @endfor
                        </div>
                        @error('rate')
                            <p class="tw-mt-1 tw-text-xs tw-font-medium tw-text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="review-message" class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">{{ __('Message') }}</label>
                        <textarea id="review-message" name="message" required rows="4" placeholder="{{ __('Message') }}"
                            class="tw-mt-2 tw-w-full tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-p-3 tw-text-sm tw-text-ink-900 tw-placeholder-neutral-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-accent-600/20 dark:tw-border-neutral-700 dark:tw-bg-ink-950 dark:tw-text-neutral-50">{{ old('message') }}</textarea>
                        @error('message')
                            <p class="tw-mt-1 tw-text-xs tw-font-medium tw-text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="tw-flex tw-justify-end tw-gap-2 tw-border-t tw-border-neutral-100 tw-px-5 tw-py-4 dark:tw-border-neutral-800">
                    <button type="button" data-review-close
                        class="tw-inline-flex tw-h-10 tw-items-center tw-rounded-pill tw-border tw-border-neutral-200 tw-px-4 tw-text-sm tw-font-bold tw-text-neutral-700 hover:tw-bg-neutral-100 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-text-neutral-200 dark:hover:tw-bg-neutral-800">
                        {{ __('Close') }}
                    </button>
                    <x-button type="submit">{{ __('Add') }}</x-button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/user-area.js')
@endpush
