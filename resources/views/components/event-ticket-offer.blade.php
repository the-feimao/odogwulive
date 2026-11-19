@props([
    'ticket',
    'event',
    'currency' => null,
])

@php
    $isFree = strtolower((string) ($ticket->type ?? '')) === 'free' || (float) ($ticket->price ?? 0) === 0.0;
    $priceLabel = $isFree
        ? __('Free')
        : trim((string) $currency).number_format((float) $ticket->price);
    $available = (int) ($ticket->available_qty ?? 0);
    $soldOut = $available <= 0;
    $checkoutUrl = Auth::guard('appuser')->check()
        ? url('checkout/'.$ticket->id)
        : url('user/login');
@endphp

<x-ticket-card :ticket="$ticket" :event="$event" :price="$priceLabel" :code="$ticket->ticket_number" :status="null">
    <x-slot:action>
        <div class="tw-space-y-3">
            <div class="tw-grid tw-grid-cols-2 tw-gap-3 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                <div>
                    <p class="tw-font-semibold tw-uppercase tw-tracking-wide">{{ __('Sales start') }}</p>
                    <p class="tw-mt-0.5">{{ $ticket->start_time?->format('M j, g:i A') }}</p>
                </div>
                <div>
                    <p class="tw-font-semibold tw-uppercase tw-tracking-wide">{{ __('Sales end') }}</p>
                    <p class="tw-mt-0.5">{{ $ticket->end_time?->format('M j, g:i A') }}</p>
                </div>
            </div>

            @if ($soldOut)
                <p class="tw-rounded-stub tw-bg-danger-50 tw-px-3 tw-py-2 tw-text-center tw-text-sm tw-font-bold tw-text-danger-700 dark:tw-bg-danger-500/10 dark:tw-text-danger-300">
                    {{ __('Sold Out') }}
                </p>
            @else
                <p class="tw-text-xs tw-font-semibold tw-text-neutral-600 dark:tw-text-neutral-300">
                    {{ $available }} {{ $available === 1 ? __('ticket left') : __('tickets left') }}
                </p>
                <a href="{{ $checkoutUrl }}"
                    class="tw-inline-flex tw-h-10 tw-w-full tw-items-center tw-justify-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition-colors hover:tw-bg-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600">
                    {{ __('Book Now') }}
                    <x-lucide-arrow-right class="tw-h-4 tw-w-4" />
                </a>
            @endif
        </div>
    </x-slot:action>
</x-ticket-card>
