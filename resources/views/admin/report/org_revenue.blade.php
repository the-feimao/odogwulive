@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Revenue Report')" />

    <x-flash-messages />

    <x-card :padded="false">
        <x-slot:header>
            <div>
                <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Revenue Report') }}</h2>
                <p class="tw-mt-0.5 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                    {{ __('Review settlement payments made to your organization.') }}
                </p>
            </div>
        </x-slot:header>

        <div class="tw-space-y-5 tw-p-5">
            <form method="post" action="{{ url('organization-report/revenue') }}" class="tw-grid tw-gap-4 sm:tw-grid-cols-2 lg:tw-max-w-lg lg:tw-items-end">
                @csrf
                <x-input name="duration" :label="__('Duration')" :value="$request->duration ?? ''"
                    :placeholder="__('Choose date')" class="date duration" />
                <x-button type="submit">{{ __('Apply') }}</x-button>
            </form>

            <div class="tw-overflow-x-auto">
                <table id="report_table" class="tw-min-w-full tw-text-left tw-text-sm">
                    <thead>
                        <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                            <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                            <th class="tw-px-3 tw-py-3">{{ __('Organization Name') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Payment Type') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Payment Token') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Payment Status') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Payment') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Payment on') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                                <td class="tw-px-3 tw-py-3"></td>
                                <td class="tw-px-3 tw-py-3">
                                    <p class="tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->user->first_name.' '.$item->user->last_name }}</p>
                                    <p class="tw-mt-0.5 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item->user->email }}</p>
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->payment_type }}</td>
                                <td class="tw-px-3 tw-py-3 tw-font-mono tw-text-xs tw-text-neutral-600 dark:tw-text-neutral-300">
                                    {{ $item->payment_token == null ? '-' : $item->payment_token }}
                                </td>
                                <td class="tw-px-3 tw-py-3">
                                    <x-badge :tone="$item->payment_status == 1 ? 'success' : 'warning'">
                                        {{ $item->payment_status == 1 ? __('Compelete') : __('Waiting') }}
                                    </x-badge>
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $currency.$item->payment }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->created_at->format('Y-m-d , h:i a') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-card>

    @include('admin.report.partials.payment-modal')
@endsection
