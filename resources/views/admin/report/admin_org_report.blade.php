@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Organization Report')" />

    <x-flash-messages />

    <x-card :padded="false">
        <x-slot:header>
            <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Organizer Report') }}</h2>
        </x-slot:header>

        <div class="tw-space-y-5 tw-p-5">
            <form method="post" action="{{ url('admin-report/customer') }}" class="tw-grid tw-gap-4 sm:tw-grid-cols-[minmax(0,240px)_auto] sm:tw-items-end">
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
                            <th class="tw-px-3 tw-py-3">{{ __('Organization Name') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Phone') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Total Events') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Tickets') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Registered at') }}</th>
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
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->phone ?: '-' }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->total_events }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->total_tickets.'/'.$item->sold_tickets }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </x-card>
@endsection
