@props([
    'ticket' => null,
    'event' => null,
    'title' => null,
    'price' => null,
    'code' => null,
    'status' => null,
])

@php
    $title = $title ?? ($ticket->name ?? $ticket->title ?? 'Admission ticket');
    $price = $price ?? ($ticket->price ?? null);
    $code = $code ?? ($ticket->ticket_number ?? $ticket->code ?? null);
    $status = $status ?? ($ticket->status ?? null);
    $eventTitle = $event ? ($event->name ?? $event->title ?? null) : null;
@endphp

<article {{ $attributes->merge(['class' => 'ds-ticket-card tw-overflow-hidden tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-shadow-card dark:tw-border-neutral-800 dark:tw-bg-ink-900']) }}>
    <div class="tw-p-5">
        <div class="tw-flex tw-items-start tw-justify-between tw-gap-3">
            <div>
                @if ($eventTitle)
                    <p class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ $eventTitle }}</p>
                @endif
                <h3 class="tw-mt-1 tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ $title }}</h3>
            </div>

            @if ($status)
                <x-badge :tone="strtolower((string) $status) === 'used' || strtolower((string) $status) === 'valid' ? 'success' : 'neutral'">
                    {{ ucfirst((string) $status) }}
                </x-badge>
            @endif
        </div>

        <div class="tw-mt-4 tw-flex tw-items-end tw-justify-between tw-gap-3">
            <div>
                <p class="tw-text-2xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">Price</p>
                <p class="tw-text-xl tw-font-bold tw-text-primary-700 dark:tw-text-primary-400">{{ $price ?? 'Free' }}</p>
            </div>

            @if (isset($qr))
                <div class="tw-h-16 tw-w-16 tw-rounded-stub tw-bg-neutral-0 tw-p-1 dark:tw-bg-neutral-100">{{ $qr }}</div>
            @endif
        </div>

        @if (isset($action))
            <div class="tw-mt-4">{{ $action }}</div>
        @endif
    </div>

    <div class="ds-ticket-divider tw-mx-5"></div>

    <div class="tw-flex tw-items-center tw-justify-between tw-gap-3 tw-p-5 tw-pt-4">
        <div>
            <p class="tw-text-2xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">Ticket code</p>
            <p class="tw-mt-0.5 tw-font-mono tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-50">{{ $code ?? '—' }}</p>
        </div>
        <x-lucide-ticket class="tw-h-5 tw-w-5 tw-text-neutral-400 dark:tw-text-neutral-500" />
    </div>
</article>
