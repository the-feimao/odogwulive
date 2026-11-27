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
                        {{ __('Paid orders, organizer payouts and platform revenue.') }}
                    </p>
                </div>
                <x-badge tone="accent" size="sm">{{ $summary['orders'].' '.__('paid orders') }}</x-badge>
            </div>
        </x-slot:header>

        <div class="tw-space-y-5 tw-p-5">
            <form method="post" action="{{ url('admin-report/revenue') }}" class="tw-grid tw-gap-4 tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-50/70 tw-p-4 dark:tw-border-neutral-800 dark:tw-bg-neutral-950/40 sm:tw-grid-cols-2 lg:tw-grid-cols-4 lg:tw-items-end">
                @csrf
                <x-select name="organizer" :label="__('Organization')">
                    <option value="">{{ __('Select Organizer') }}</option>
                    @foreach ($organizers as $item)
                        <option value="{{ $item->id }}" {{ ($request->organizer ?? '') == $item->id ? 'selected' : '' }}>
                            {{ $item->first_name.' '.$item->last_name }}
                        </option>
                    @endforeach
                </x-select>
                <x-select name="customer" :label="__('Customer')">
                    <option value="">{{ __('Select Customer') }}</option>
                    @foreach ($customers as $item)
                        <option value="{{ $item->id }}" {{ ($request->customer ?? '') == $item->id ? 'selected' : '' }}>
                            {{ $item->name.' '.$item->last_name }}
                        </option>
                    @endforeach
                </x-select>
                <x-input name="duration" :label="__('Duration')" :value="$request->duration ?? ''"
                    :placeholder="__('Choose date')" class="date duration" />
                <x-button type="submit" class="tw-w-full">{{ __('Apply') }}</x-button>
            </form>

            <x-report-summary :columns="4" :items="[
                ['label' => __('Gross revenue'), 'value' => $money($summary['gross']), 'hint' => __('Total paid by customers')],
                ['label' => __('Organizer payout'), 'value' => $money($summary['organizer']), 'hint' => __('Commission plus tax')],
                ['label' => __('Platform revenue'), 'value' => $money($summary['platform']), 'hint' => __('Revenue retained by the platform')],
                ['label' => __('Collected tax'), 'value' => $money($summary['tax']), 'hint' => __('Included in organizer payouts')],
            ]" />

            <div class="tw-overflow-x-auto">
                <table class="tw-min-w-full tw-divide-y tw-divide-neutral-100 tw-text-left tw-text-sm dark:tw-divide-neutral-800" id="revenue_table">
                    <thead class="tw-bg-neutral-50/80 dark:tw-bg-neutral-950/40">
                        <tr class="tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">
                            <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-font-bold">{{ __('Order Id') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-font-bold">{{ __('Customer') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-font-bold">{{ __('Event Name') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-text-right tw-font-bold">{{ __('Quantity') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-font-bold">{{ __('Payment Gateway') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-text-right tw-font-bold">{{ __('Total order tax') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-text-right tw-font-bold">{{ __('Total Payment') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-text-right tw-font-bold">{{ __('Organizer Commission') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-text-right tw-font-bold">{{ __('Admin Revenue') }}</th>
                            <th class="tw-whitespace-nowrap tw-px-4 tw-py-3 tw-font-bold">{{ __('Created at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            @php $organizerCommission = $item->org_commission + $item->tax; @endphp
                            <tr class="tw-transition hover:tw-bg-neutral-50/70 dark:hover:tw-bg-neutral-950/30">
                                <td class="tw-px-3 tw-py-4"></td>
                                <td class="tw-px-4 tw-py-4">
                                    <span class="tw-font-mono tw-text-xs tw-font-semibold tw-text-accent-700 dark:tw-text-accent-300">{{ $item->order_id }}</span>
                                </td>
                                <td class="tw-px-4 tw-py-4">
                                    <div class="tw-flex tw-items-center tw-gap-3">
                                        <span class="tw-flex tw-h-8 tw-w-8 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-full tw-bg-primary-100 tw-text-xs tw-font-bold tw-text-primary-700 dark:tw-bg-primary-500/15 dark:tw-text-primary-300" aria-hidden="true">
                                            {{ strtoupper(substr((string) $item->customer->name, 0, 1)) }}
                                        </span>
                                        <div class="tw-min-w-0">
                                            <p class="tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->customer->name.' '.$item->customer->last_name }}</p>
                                            <p class="tw-mt-0.5 tw-truncate tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item->customer->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="tw-px-4 tw-py-4 tw-font-medium tw-text-neutral-700 dark:tw-text-neutral-200">{{ $item->event->name }}</td>
                                <td class="tw-px-4 tw-py-4 tw-text-right tw-tabular-nums tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->quantity }}</td>
                                <td class="tw-px-4 tw-py-4">
                                    <x-badge tone="neutral" size="xs">{{ $item->payment_type }}</x-badge>
                                </td>
                                <td class="tw-px-4 tw-py-4 tw-text-right tw-tabular-nums tw-text-neutral-600 dark:tw-text-neutral-300">{{ $money($item->tax) }}</td>
                                <td class="tw-px-4 tw-py-4 tw-text-right tw-tabular-nums tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-50">{{ $money($item->payment) }}</td>
                                <td class="tw-px-4 tw-py-4 tw-text-right tw-tabular-nums tw-text-accent-700 dark:tw-text-accent-300">{{ $money($organizerCommission) }}</td>
                                <td class="tw-px-4 tw-py-4 tw-text-right tw-tabular-nums tw-font-semibold tw-text-success-700 dark:tw-text-success-400">{{ $money($item->payment - $organizerCommission) }}</td>
                                <td class="tw-whitespace-nowrap tw-px-4 tw-py-4 tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    {{--
                        DataTables writes the legacy page/main totals here. The
                        visible totals are shown in the summary cards above.
                    --}}
                    <tfoot class="tw-hidden" aria-hidden="true">
                        <tr>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th>{{ __('Page Total:') }}<br><br>{{ __('Main Total:') }}</th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </x-card>
@endsection
