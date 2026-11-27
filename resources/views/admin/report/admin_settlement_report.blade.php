@extends('layouts.admin')

@section('admin-content')
    @php
        $statusLabels = [
            'missing' => __('Details needed'),
            'ready' => __('Ready to pay'),
            'paid' => __('Last payout paid'),
            'failed' => __('Last payout failed'),
        ];
        $statusTones = [
            'missing' => 'warning',
            'ready' => 'accent',
            'paid' => 'success',
            'failed' => 'danger',
        ];
        $money = fn ($value) => $currency.number_format((float) $value, 2);
    @endphp

    <x-admin-breadcrumb :title="__('Organizer Settlement Report')" />

    <x-flash-messages />

    <div class="tw-space-y-5">
        <x-alert tone="info" :title="__('Paystack transfers use saved bank details')">
            {{ __('Payout amounts are recalculated from paid orders on the server. Organizers add and verify their own bank account on the profile page.') }}
        </x-alert>

        <x-report-summary :columns="4" :items="[
            ['label' => __('Organizations'), 'value' => $summary['organizations'], 'hint' => __('With settlement activity')],
            ['label' => __('Outstanding commission'), 'value' => $money($summary['outstanding']), 'hint' => __('Payable from paid orders'), 'badge' => $summary['ready'].' '.__('ready'), 'badgeTone' => 'accent'],
            ['label' => __('Paid commission'), 'value' => $money($summary['paid_commission']), 'hint' => __('Already settled with organizers')],
            ['label' => __('Total commission'), 'value' => $money($summary['total_commission']), 'hint' => __('Lifetime organizer earnings')],
        ]" />

        <x-card :padded="false">
            <x-slot:header>
                <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3">
                    <div>
                        <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Organizer settlements') }}</h2>
                        <p class="tw-mt-0.5 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                            {{ __('Commission balances, payout accounts and payment actions.') }}
                        </p>
                    </div>
                    <x-badge tone="neutral" size="sm">{{ $summary['organizations'].' '.__('rows') }}</x-badge>
                </div>
            </x-slot:header>

            <div class="tw-overflow-x-auto">
                <table id="settlement_report" class="tw-min-w-full tw-divide-y tw-divide-neutral-100 tw-text-left tw-text-sm dark:tw-divide-neutral-800">
                    <thead class="tw-bg-neutral-50/80 dark:tw-bg-neutral-950/40">
                        <tr class="tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">
                            <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-font-bold">{{ __('Organization Name') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-text-right tw-font-bold">{{ __('Total Orders') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-text-right tw-font-bold">{{ __('Total Commission') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-text-right tw-font-bold">{{ __('Paid Commission') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-text-right tw-font-bold">{{ __('Remaining Commission') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-font-bold">{{ __('Payout Account') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-font-bold">{{ __('Paystack Status') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-text-right tw-font-bold">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            <tr class="tw-transition hover:tw-bg-neutral-50/70 dark:hover:tw-bg-neutral-950/30">
                                <td class="tw-px-3 tw-py-4"></td>
                                <td class="tw-px-4 tw-py-4">
                                    <div class="tw-flex tw-items-center tw-gap-3">
                                        <span class="tw-flex tw-h-9 tw-w-9 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-stub tw-bg-accent-100 tw-text-sm tw-font-bold tw-text-accent-700 dark:tw-bg-accent-500/15 dark:tw-text-accent-300" aria-hidden="true">
                                            {{ strtoupper(substr((string) $item->first_name, 0, 1)) }}
                                        </span>
                                        <div class="tw-min-w-0">
                                            <p class="tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->first_name.' '.$item->last_name }}</p>
                                            <p class="tw-mt-0.5 tw-truncate tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="tw-px-4 tw-py-4 tw-text-right tw-tabular-nums tw-font-medium tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->total_orders }}</td>
                                <td class="tw-px-4 tw-py-4 tw-text-right tw-tabular-nums tw-text-neutral-600 dark:tw-text-neutral-300">{{ $money($item->total_commission) }}</td>
                                <td class="tw-px-4 tw-py-4 tw-text-right tw-tabular-nums tw-text-neutral-600 dark:tw-text-neutral-300">{{ $money($item->pay_commission) }}</td>
                                <td class="tw-px-4 tw-py-4 tw-text-right tw-tabular-nums tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ $money($item->organization_commission) }}</td>
                                <td class="tw-px-4 tw-py-4">
                                    @if ($item->hasPayoutDetails())
                                        <div class="tw-flex tw-items-start tw-gap-2">
                                            <span class="tw-flex tw-h-8 tw-w-8 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-stub tw-bg-neutral-100 tw-text-neutral-500 dark:tw-bg-neutral-800 dark:tw-text-neutral-400">
                                                <x-lucide-building-2 class="tw-h-4 tw-w-4" />
                                            </span>
                                            <div class="tw-min-w-0">
                                                <p class="tw-font-medium tw-text-ink-800 dark:tw-text-neutral-100">{{ $item->bank_name }}</p>
                                                <p class="tw-mt-0.5 tw-font-mono tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                                    {{ $item->maskedAccountNumber() }}
                                                    @if ($item->payout_verified_at)
                                                        <span class="tw-ml-1 tw-font-semibold tw-text-success-600 dark:tw-text-success-500">{{ __('Verified') }}</span>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="tw-inline-flex tw-items-center tw-gap-1.5 tw-text-xs tw-text-neutral-400 dark:tw-text-neutral-500">
                                            <x-lucide-circle-alert class="tw-h-3.5 tw-w-3.5" />
                                            {{ __('Not provided') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="tw-px-4 tw-py-4">
                                    <div class="tw-flex tw-flex-col tw-items-start tw-gap-1">
                                        <x-badge :tone="$statusTones[$item->payout_status] ?? 'neutral'" size="xs">
                                            {{ $statusLabels[$item->payout_status] ?? __('Unknown') }}
                                        </x-badge>
                                        @if ($item->latest_payout?->failure_reason)
                                            <p class="tw-max-w-[220px] tw-truncate tw-text-2xs tw-text-danger-600 dark:tw-text-danger-500"
                                                title="{{ $item->latest_payout->failure_reason }}">
                                                {{ $item->latest_payout->failure_reason }}
                                            </p>
                                        @endif
                                    </div>
                                </td>
                                <td class="tw-px-4 tw-py-4">
                                    <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-end tw-gap-2">
                                        <a href="{{ url('view-settlement/'.$item->id) }}" title="{{ __('View Settlement') }}"
                                            aria-label="{{ __('View Settlement') }}"
                                            class="btn-pay tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-accent-600 tw-transition hover:tw-bg-accent-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:hover:tw-bg-accent-900/20">
                                            <x-lucide-eye class="tw-h-4 tw-w-4" />
                                        </a>

                                        @if ($item->organization_commission > 0)
                                            <button type="button"
                                                data-id="{{ $item->id }}" data-payment="{{ $item->organization_commission }}"
                                                data-toggle="modal" data-target="#paymentModel"
                                                aria-label="{{ __('Pay') }} {{ $item->first_name }} {{ $item->last_name }}"
                                                class="btn-pay tw-inline-flex tw-h-9 tw-items-center tw-gap-1.5 tw-rounded-pill tw-border tw-border-neutral-200 tw-px-3 tw-text-xs tw-font-bold tw-text-ink-700 tw-transition hover:tw-bg-neutral-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-text-neutral-200 dark:hover:tw-bg-neutral-800">
                                                <x-lucide-credit-card class="tw-h-3.5 tw-w-3.5" />
                                                {{ __('Other methods') }}
                                            </button>

                                            @if ($item->hasPayoutDetails())
                                                <form method="post" action="{{ url('pay-to-organization-paystack') }}">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $item->id }}">
                                                    <button type="submit"
                                                        aria-label="{{ __('Pay with Paystack') }} {{ $item->first_name }} {{ $item->last_name }}"
                                                        class="tw-inline-flex tw-h-9 tw-items-center tw-gap-1.5 tw-rounded-pill tw-bg-primary-500 tw-px-3 tw-text-xs tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                                                        <x-lucide-banknote class="tw-h-3.5 tw-w-3.5" />
                                                        {{ __('Pay with Paystack') }}
                                                    </button>
                                                </form>
                                            @else
                                                <span class="tw-inline-flex tw-h-9 tw-items-center tw-gap-1.5 tw-rounded-pill tw-bg-neutral-100 tw-px-3 tw-text-xs tw-font-semibold tw-text-neutral-400 dark:tw-bg-neutral-800 dark:tw-text-neutral-500"
                                                    title="{{ __('The organization must add payout details first.') }}">
                                                    <x-lucide-circle-alert class="tw-h-3.5 tw-w-3.5" />
                                                    {{ __('Paystack unavailable') }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    {{--
                        DataTables still fills these cells and the legacy labels
                        remain in the DOM, but the visible totals now live in the
                        summary cards above the table.
                    --}}
                    <tfoot class="tw-hidden" aria-hidden="true">
                        <tr>
                            <th></th>
                            <th></th>
                            <th>{{ __('Page Total:') }}<br><br>{{ __('Main Total:') }}</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-card>
    </div>

    @include('admin.report.partials.payment-modal')
@endsection
