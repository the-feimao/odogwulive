@extends('layouts.admin')

@section('admin-content')
    @php
        $money = fn ($value) => $currency.number_format((float) $value, 2);
    @endphp

    <x-admin-breadcrumb :title="__('Revenue Report')" />

    <x-flash-messages />

    <x-card :padded="false">
        <x-slot:header>
            <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3">
                <div>
                    <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Revenue Report') }}</h2>
                    <p class="tw-mt-0.5 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                        {{ __('Review settlement payments made to your organization.') }}
                    </p>
                </div>
                <x-badge tone="accent" size="sm">{{ $summary['payouts'].' '.__('records') }}</x-badge>
            </div>
        </x-slot:header>

        <div class="tw-space-y-5 tw-p-5">
            <form method="post" action="{{ url('organization-report/revenue') }}" class="tw-grid tw-gap-4 tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-50/70 tw-p-4 dark:tw-border-neutral-800 dark:tw-bg-neutral-950/40 sm:tw-grid-cols-2 lg:tw-max-w-lg lg:tw-items-end">
                @csrf
                <x-input name="duration" :label="__('Duration')" :value="$request->duration ?? ''"
                    :placeholder="__('Choose date')" class="date duration" />
                <x-button type="submit" class="tw-w-full">{{ __('Apply') }}</x-button>
            </form>

            <x-report-summary :columns="3" :items="[
                ['label' => __('Paid to you'), 'value' => $money($summary['paid']), 'hint' => __('Completed settlements')],
                ['label' => __('Awaiting payment'), 'value' => $money($summary['pending']), 'hint' => __('Pending settlements')],
                ['label' => __('Settlement records'), 'value' => $summary['payouts'], 'hint' => __('In selected duration')],
            ]" />

            <div class="tw-overflow-x-auto">
                <table id="report_table" class="tw-min-w-full tw-divide-y tw-divide-neutral-100 tw-text-left tw-text-sm dark:tw-divide-neutral-800">
                    <thead class="tw-bg-neutral-50/80 dark:tw-bg-neutral-950/40">
                        <tr class="tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">
                            <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-font-bold">{{ __('Organization Name') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-font-bold">{{ __('Payment Type') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-font-bold">{{ __('Payment Token') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-font-bold">{{ __('Payment Status') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-text-right tw-font-bold">{{ __('Payment') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-font-bold">{{ __('Payment on') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            <tr class="tw-transition hover:tw-bg-neutral-50/70 dark:hover:tw-bg-neutral-950/30">
                                <td class="tw-px-3 tw-py-4"></td>
                                <td class="tw-px-4 tw-py-4">
                                    <div class="tw-flex tw-items-center tw-gap-3">
                                        <span class="tw-flex tw-h-8 tw-w-8 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-stub tw-bg-accent-100 tw-text-xs tw-font-bold tw-text-accent-700 dark:tw-bg-accent-500/15 dark:tw-text-accent-300" aria-hidden="true">
                                            {{ strtoupper(substr((string) $item->user->first_name, 0, 1)) }}
                                        </span>
                                        <div class="tw-min-w-0">
                                            <p class="tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->user->first_name.' '.$item->user->last_name }}</p>
                                            <p class="tw-mt-0.5 tw-truncate tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item->user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="tw-px-4 tw-py-4">
                                    <x-badge tone="neutral" size="xs">{{ $item->payment_type }}</x-badge>
                                </td>
                                <td class="tw-px-4 tw-py-4">
                                    <span class="tw-font-mono tw-text-xs tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->payment_token == null ? '-' : $item->payment_token }}</span>
                                </td>
                                <td class="tw-px-4 tw-py-4">
                                    <x-badge :tone="$item->payment_status == 1 ? 'success' : 'warning'" size="xs">
                                        {{ $item->payment_status == 1 ? __('Compelete') : __('Waiting') }}
                                    </x-badge>
                                </td>
                                <td class="tw-px-4 tw-py-4 tw-text-right tw-tabular-nums tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ $money($item->payment) }}</td>
                                <td class="tw-whitespace-nowrap tw-px-4 tw-py-4 tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item->created_at->format('Y-m-d , h:i a') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-card>

    @include('admin.report.partials.payment-modal')
@endsection
