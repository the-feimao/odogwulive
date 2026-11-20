@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Organizer Settlement Report')" />

    <x-flash-messages />

    <x-card :padded="false">
        <x-slot:header>
            <div>
                <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Organizer Settlement Report') }}</h2>
                <p class="tw-mt-0.5 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                    {{ __('Track commission balances and pay organizers.') }}
                </p>
            </div>
        </x-slot:header>

        <div class="tw-overflow-x-auto">
            <table id="settlement_report" class="tw-min-w-full tw-text-left tw-text-sm">
                <thead>
                    <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                        <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                        <th class="tw-px-3 tw-py-3">{{ __('Organization Name') }}</th>
                        <th class="tw-px-3 tw-py-3">{{ __('Total Orders') }}</th>
                        <th class="tw-px-3 tw-py-3">{{ __('Total Commission') }}</th>
                        <th class="tw-px-3 tw-py-3">{{ __('Paid Commission') }}</th>
                        <th class="tw-px-3 tw-py-3">{{ __('Remaining Commission') }}</th>
                        <th class="tw-px-3 tw-py-3">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data as $item)
                        <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                            <td class="tw-px-3 tw-py-3"></td>
                            <td class="tw-px-3 tw-py-3">
                                <p class="tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->first_name.' '.$item->last_name }}</p>
                                <p class="tw-mt-0.5 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item->email }}</p>
                            </td>
                            <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->total_orders }}</td>
                            <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $currency.$item->total_commission }}</td>
                            <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $currency.$item->pay_commission }}</td>
                            <td class="tw-px-3 tw-py-3 tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $currency.$item->organization_commission }}</td>
                            <td class="tw-px-3 tw-py-3">
                                <span class="tw-inline-flex tw-items-center tw-gap-2">
                                    <a href="{{ url('view-settlement/'.$item->id) }}" title="{{ __('View Settlement') }}"
                                        aria-label="{{ __('View Settlement') }}"
                                        class="btn-pay tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-accent-600 tw-transition hover:tw-bg-accent-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:hover:tw-bg-accent-900/20">
                                        <x-lucide-eye class="tw-h-4 tw-w-4" />
                                    </a>
                                    @if ($item->organization_commission > 0)
                                        <button type="button" {{ $item->organization_commission == 0 ? 'disabled' : '' }}
                                            data-id="{{ $item->id }}" data-payment="{{ $item->organization_commission }}"
                                            data-toggle="modal" data-target="#paymentModel"
                                            aria-label="{{ __('Pay') }} {{ $item->first_name }} {{ $item->last_name }}"
                                            class="btn-pay tw-inline-flex tw-h-9 tw-items-center tw-gap-1.5 tw-rounded-pill tw-bg-primary-500 tw-px-3 tw-text-xs tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 disabled:tw-cursor-not-allowed disabled:tw-opacity-50">
                                            <x-lucide-credit-card class="tw-h-3.5 tw-w-3.5" />
                                            {{ __('Pay') }}
                                        </button>
                                    @endif
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="tw-border-t tw-border-neutral-200 tw-text-xs tw-font-semibold tw-text-neutral-600 dark:tw-border-neutral-700 dark:tw-text-neutral-300">
                        <th class="tw-px-3 tw-py-3"></th>
                        <th class="tw-px-3 tw-py-3"></th>
                        <th class="tw-px-3 tw-py-3">{{ __('Page Total:') }}<br><br>{{ __('Main Total:') }}</th>
                        <th class="tw-px-3 tw-py-3"></th>
                        <th class="tw-px-3 tw-py-3"></th>
                        <th class="tw-px-3 tw-py-3"></th>
                        <th class="tw-px-3 tw-py-3"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </x-card>

    @include('admin.report.partials.payment-modal')
@endsection
