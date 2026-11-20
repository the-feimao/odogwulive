@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Customers Report')" />

    <x-flash-messages />

    <x-card :padded="false">
        <x-slot:header>
            <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Customers Report') }}</h2>
        </x-slot:header>

        <div class="tw-space-y-5 tw-p-5">
            <form method="post" action="{{ url('organization-report/customer') }}" class="tw-grid tw-gap-4 sm:tw-grid-cols-[minmax(0,240px)_auto] sm:tw-items-end">
                @csrf
                <x-input name="duration" :label="__('Duration')" :value="isset($request->duration) ? $request->duration : ''"
                    :placeholder="__('Choose date')" class="date duration" />
                <x-button type="submit">{{ __('Apply') }}</x-button>
            </form>

            <div class="tw-overflow-x-auto">
                <table class="tw-min-w-full tw-text-left tw-text-sm" id="report_table">
                    <thead>
                        <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                            <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                            <th class="tw-px-3 tw-py-3">{{ __('Customer Name') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Email') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('phone') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Buy total tickets') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Registered at') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                                <td class="tw-px-3 tw-py-3"></td>
                                <td class="tw-px-3 tw-py-3">
                                    <span class="tw-inline-flex tw-items-center tw-gap-3">
                                        <img src="{{ url('images/upload/'.$item->image) }}" alt="{{ $item->name }}"
                                            loading="lazy" class="tw-h-9 tw-w-9 tw-rounded-pill tw-object-cover">
                                        <span class="tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">
                                            {{ $item->name.' '.$item->last_name }}
                                        </span>
                                    </span>
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->email }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->phone ?: '-' }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->buy_tickets }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-card>
@endsection
