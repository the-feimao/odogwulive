@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Revenue Report')" />

    <x-flash-messages />

    @php
        $organizers = App\Models\User::role('organization')->orderBy('id', 'DESC')->get();
        $customers = App\Models\AppUser::orderBy('id', 'DESC')->get();
    @endphp

    <x-card :padded="false">
        <x-slot:header>
            <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Revenue Report') }}</h2>
        </x-slot:header>

        <div class="tw-space-y-5 tw-p-5">
            <form method="post" action="{{ url('admin-report/revenue') }}" class="tw-grid tw-gap-4 sm:tw-grid-cols-2 lg:tw-grid-cols-4 lg:tw-items-end">
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
                <x-button type="submit">{{ __('Apply') }}</x-button>
            </form>

            <div class="tw-overflow-x-auto">
                <table class="tw-min-w-full tw-text-left tw-text-sm" id="revenue_table">
                    <thead>
                        <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                            <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                            <th class="tw-px-3 tw-py-3">{{ __('Order Id') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Customer') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Event Name') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Quantity') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Payment Gateway') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Total order tax') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Total Payment') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Organizer Commission') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Admin Revenue') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Created at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            @php $organizerCommission = $item->org_commission + $item->tax; @endphp
                            <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                                <td class="tw-px-3 tw-py-3"></td>
                                <td class="tw-px-3 tw-py-3 tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->order_id }}</td>
                                <td class="tw-px-3 tw-py-3">
                                    <p class="tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->customer->name.' '.$item->customer->last_name }}</p>
                                    <p class="tw-mt-0.5 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item->customer->email }}</p>
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->event->name }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->quantity.' tickets' }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->payment_type }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $currency.$item->tax }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $currency.$item->payment }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $currency.$organizerCommission }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $currency.($item->payment - $organizerCommission) }}</td>
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
