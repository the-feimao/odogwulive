@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Orders Report')" />

    <x-flash-messages />

    <x-card :padded="false">
        <x-slot:header>
            <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Order List') }}</h2>
        </x-slot:header>

        <div class="tw-space-y-5 tw-p-5">
            <form method="post" action="{{ url('organization-report/orders') }}" class="tw-grid tw-gap-4 sm:tw-grid-cols-2 lg:tw-grid-cols-[minmax(0,240px)_minmax(0,240px)_auto] lg:tw-items-end">
                @csrf
                <x-select name="customer" :label="__('Customer')">
                    <option value="">{{ __('Select Customer') }}</option>
                    @foreach ($customers as $item)
                        <option value="{{ $item->id }}" {{ isset($request->customer) && $request->customer == $item->id ? 'selected' : '' }}>
                            {{ $item->name.' '.$item->last_name }}
                        </option>
                    @endforeach
                </x-select>
                <x-input name="duration" :label="__('Duration')" :value="isset($request->duration) ? $request->duration : ''"
                    :placeholder="__('Choose date')" class="date duration" />
                <x-button type="submit">{{ __('Apply') }}</x-button>
            </form>

            <div class="tw-overflow-x-auto">
                <table class="tw-min-w-full tw-text-left tw-text-sm" id="org_order_report">
                    <thead>
                        <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                            <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                            <th class="tw-px-3 tw-py-3">{{ __('Order Id') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Customer') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Event Name') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Sold Ticket') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Payment Gateway') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Payment Status') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Payment') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Tax') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Organization commission') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Created at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                                <td class="tw-px-3 tw-py-3"></td>
                                <td class="tw-px-3 tw-py-3">
                                    <a href="{{ url('order-invoice/'.$item->id) }}" class="tw-font-semibold tw-text-accent-600 hover:tw-underline dark:tw-text-accent-300">{{ $item->order_id }}</a>
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->customer->name.' '.$item->customer->last_name }}</td>
                                <td class="tw-px-3 tw-py-3">
                                    <p class="tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->event->name }}</p>
                                    <p class="tw-mt-0.5 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item->event->start_time->format('Y-m-d , h:i a') }}</p>
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->quantity.' ticket' }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->payment_type }}</td>
                                <td class="tw-px-3 tw-py-3">
                                    <x-badge :tone="$item->payment_status == '1' ? 'success' : 'warning'">
                                        {{ $item->payment_status == '1' ? __('Completed') : __('pending') }}
                                    </x-badge>
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $currency.$item->payment }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $currency.$item->tax }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $currency.($item->org_commission + $item->tax) }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="tw-border-t tw-border-neutral-200 tw-text-xs tw-font-semibold tw-text-neutral-600 dark:tw-border-neutral-700 dark:tw-text-neutral-300">
                            <th></th>
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
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </x-card>
@endsection
