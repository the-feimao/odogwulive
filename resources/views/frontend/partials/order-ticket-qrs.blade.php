@if (! $paid)
    <div class="tw-flex tw-items-start tw-gap-3 tw-rounded-card tw-border tw-border-warning-200 tw-bg-warning-50 tw-p-4 dark:tw-border-warning-500/30 dark:tw-bg-warning-500/10">
        <span class="tw-mt-0.5 tw-shrink-0 tw-text-warning-700 dark:tw-text-warning-300"><x-lucide-ticket class="tw-h-5 tw-w-5" /></span>
        <div>
            <p class="tw-text-sm tw-font-bold tw-text-warning-900 dark:tw-text-warning-100">{{ __('Payment still pending') }}</p>
            <p class="tw-mt-1 tw-text-sm tw-leading-relaxed tw-text-warning-800 dark:tw-text-warning-200">{{ __('Your QR codes will appear here as soon as this order is confirmed.') }}</p>
        </div>
    </div>
@elseif ($children->isEmpty())
    <div class="tw-flex tw-items-start tw-gap-3 tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-50 tw-p-4 dark:tw-border-neutral-800 dark:tw-bg-ink-950">
        <span class="tw-mt-0.5 tw-shrink-0 tw-text-neutral-400"><x-lucide-ticket class="tw-h-5 tw-w-5" /></span>
        <div>
            <p class="tw-text-sm tw-font-bold tw-text-ink-900 dark:tw-text-neutral-100">{{ __('No ticket passes yet') }}</p>
            <p class="tw-mt-1 tw-text-sm tw-leading-relaxed tw-text-neutral-600 dark:tw-text-neutral-400">{{ __('There are no tickets attached to this order.') }}</p>
        </div>
    </div>
@else
    <div class="tw-space-y-4">
        @foreach ($children as $child)
            @php($scanned = (int) $child->status === 1)
            <article class="ds-qr-pass" data-ticket-qr="{{ $child->ticket_number }}">
                <div class="ds-qr-pass__stub">
                    <span class="tw-text-sm tw-font-extrabold tw-uppercase tw-tracking-wide tw-text-ink-950">{{ __('Scan at gate') }}</span>

                    <div class="ds-qr-pass__code" role="img" aria-label="{{ __('QR code for').' '.$child->ticket_number }}">
                        {!! $child->qrCode !!}
                    </div>

                    <span class="tw-max-w-full tw-truncate tw-font-mono tw-text-xs tw-font-bold tw-tabular-nums tw-text-ink-700">
                        {{ __('No.').' '.$child->ticket_number }}
                    </span>
                </div>

                <div class="ds-qr-pass__detail tw-flex tw-flex-col tw-justify-between tw-gap-5 tw-p-6 md:tw-p-7">
                    <div class="tw-flex tw-items-start tw-justify-between tw-gap-3">
                        <div class="tw-min-w-0">
                            <h4 class="tw-truncate tw-text-xl tw-font-extrabold tw-tracking-tight tw-text-ink-950 dark:tw-text-neutral-50 md:tw-text-2xl">
                                {{ $order->event?->name ?? __('Event') }}
                            </h4>
                            <p class="tw-mt-1 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                                {{ $order->ticket?->name ?? __('Admission ticket') }}
                            </p>
                        </div>

                        <span class="tw-inline-flex tw-shrink-0 tw-items-center tw-gap-1.5 tw-rounded-pill tw-px-2.5 tw-py-1 tw-text-xs tw-font-bold {{ $scanned ? 'tw-bg-neutral-100 tw-text-neutral-600 dark:tw-bg-neutral-800 dark:tw-text-neutral-300' : 'tw-bg-success-100 tw-text-success-700 dark:tw-bg-success-500/20 dark:tw-text-success-200' }}">
                            @if ($scanned)
                                <x-lucide-check class="tw-h-3.5 tw-w-3.5" />{{ __('Used') }}
                            @else
                                <span class="tw-h-1.5 tw-w-1.5 tw-rounded-pill tw-bg-current" aria-hidden="true"></span>{{ __('Ready') }}
                            @endif
                        </span>
                    </div>

                    <dl class="tw-grid tw-gap-x-6 tw-gap-y-3 sm:tw-grid-cols-2">
                        <div>
                            <dt class="tw-text-2xs tw-font-semibold tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Order') }}</dt>
                            <dd class="tw-mt-0.5 tw-font-mono tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $order->order_id }}</dd>
                        </div>
                        <div>
                            <dt class="tw-text-2xs tw-font-semibold tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Event date') }}</dt>
                            <dd class="tw-mt-0.5 tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">
                                {{ $order->event?->start_time?->format('d M Y') ?? __('To be announced') }}
                            </dd>
                        </div>
                    </dl>

                    <p class="tw-text-xs tw-leading-relaxed tw-text-neutral-500 dark:tw-text-neutral-400">
                        {{ __('One ticket, one scan. Keep this code ready at the entrance.') }}
                    </p>
                </div>
            </article>
        @endforeach
    </div>
@endif
