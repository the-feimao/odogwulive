@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('View Orders')" />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('Order List') }}
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('Review sales, update statuses and open invoices.') }}
            </p>
        </div>
    </div>

    <x-flash-messages />

    <x-card :padded="false">
        <div class="tw-overflow-x-auto">
            <table id="report_table" class="tw-min-w-full tw-text-left tw-text-sm">
                <thead>
                    <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                        <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                        <th class="tw-px-3 tw-py-3">{{ __('Order Id') }}</th>
                        <th class="tw-px-3 tw-py-3">{{ __('Customer Name') }}</th>
                        <th class="tw-px-3 tw-py-3">{{ __('Event Name') }}</th>
                        <th class="tw-px-3 tw-py-3">{{ __('Date') }}</th>
                        <th class="tw-px-3 tw-py-3">{{ __('Sold Ticket') }}</th>
                        <th class="tw-px-3 tw-py-3">{{ __('Payment') }}</th>
                        <th class="tw-px-3 tw-py-3">{{ __('Payment Gateway') }}</th>
                        <th class="tw-px-3 tw-py-3">{{ __('Order Status') }}</th>
                        <th class="tw-px-3 tw-py-3">{{ __('Payment Status') }}</th>
                        <th class="tw-px-3 tw-py-3">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $item)
                        <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                            <td class="tw-px-3 tw-py-3"></td>
                            <td class="tw-px-3 tw-py-3 tw-font-mono tw-text-xs tw-text-neutral-600 dark:tw-text-neutral-300">
                                {{ $item->order_id }}
                            </td>
                            <td class="tw-px-3 tw-py-3 tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">
                                {{ $item->customer->name.' '.$item->customer->last_name }}
                            </td>
                            <td class="tw-px-3 tw-py-3">
                                <p class="tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->event->name }}</p>
                                <p class="tw-mt-0.5 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item->event->start_time }}</p>
                            </td>
                            <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">
                                <p>{{ $item->created_at->format('Y-m-d') }}</p>
                                <p class="tw-mt-0.5 tw-text-xs">{{ $item->created_at->format('h:i a') }}</p>
                            </td>
                            <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->quantity.' ticket' }}</td>
                            <td class="tw-px-3 tw-py-3 tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $currency.$item->payment }}</td>
                            <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->payment_type }}</td>
                            <td class="tw-px-3 tw-py-3">
                                <select name="order_status" id="status-{{ $item->id }}"
                                    class="form-control p-2 tw-h-9 tw-w-full tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-px-2 tw-text-xs tw-text-ink-900 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-accent-600/20 dark:tw-border-neutral-700 dark:tw-bg-ink-900 dark:tw-text-neutral-50"
                                    onchange="changeOrderStatus({{ $item->id }})"
                                    {{ $item->order_status == "Complete" || $item->order_status == "Cancel" ? 'disabled':'' }}>
                                    <option value="Pending" {{ $item->order_status == "Pending" ? 'selected':''}}> {{ __('Pending') }} </option>
                                    <option value="Complete" {{ $item->order_status == "Complete" ? 'selected':''}}> {{ __('Complete') }} </option>
                                    <option value="Cancel" {{ $item->order_status == "Cancel" ? 'selected':''}}> {{ __('Cancel') }} </option>
                                </select>
                            </td>
                            <td class="tw-px-3 tw-py-3">
                                <select name="payment_status" id="payment-{{ $item->id }}"
                                    class="form-control p-2 tw-h-9 tw-w-full tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-px-2 tw-text-xs tw-text-ink-900 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-accent-600/20 dark:tw-border-neutral-700 dark:tw-bg-ink-900 dark:tw-text-neutral-50"
                                    onchange="changePaymentStatus({{ $item->id }})"
                                    {{ $item->payment_status == 1 ? 'disabled':'' }}>
                                    <option value="0" {{ $item->payment_status == 0 ? 'selected':''}}> {{ __('Pending') }} </option>
                                    <option value="1" {{ $item->payment_status == 1 ? 'selected':''}}> {{ __('Complete') }} </option>
                                </select>
                            </td>
                            <td class="tw-px-3 tw-py-3">
                                <a href="{{ url('order-invoice/'.$item->id) }}" title="{{ __('View Invoice') }}"
                                    aria-label="{{ __('View invoice') }} {{ $item->order_id }}"
                                    class="btn-icon text-primary tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-accent-600 tw-transition hover:tw-bg-accent-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:hover:tw-bg-accent-900/20">
                                    <x-lucide-eye class="tw-h-4 tw-w-4" />
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
@endsection
