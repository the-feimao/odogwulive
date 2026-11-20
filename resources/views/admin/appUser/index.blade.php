@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Attendees')" />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('View Attendees') }}
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('Manage attendee accounts, details and access.') }}
            </p>
        </div>

        @can('app_user_create')
            <a href="{{ url('app-user/create') }}"
                class="tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                <x-lucide-plus class="tw-h-4 tw-w-4" />
                {{ __('Add New') }}
            </a>
        @endcan
    </div>

    <x-flash-messages />

    <x-card :padded="false">
        @if ($users->isEmpty())
            <x-empty-state :title="__('No attendees yet')" :description="__('Add an attendee or wait for customers to register.')" />
        @else
            <div class="tw-overflow-x-auto">
                <table id="report_table" class="tw-min-w-full tw-text-left tw-text-sm">
                    <thead>
                        <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                            <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                            <th class="tw-px-3 tw-py-3">{{ __('Image') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Name') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Email') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Status') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $item)
                            <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                                <td class="tw-px-3 tw-py-3">
                                    <span class="tw-inline-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-stub tw-bg-primary-100 tw-text-primary-700 dark:tw-bg-primary-500/20 dark:tw-text-primary-300">
                                        <x-lucide-user class="tw-h-4 tw-w-4" />
                                    </span>
                                </td>
                                <td class="tw-px-3 tw-py-3">
                                    <img src="{{ url('images/upload/'.$item->image) }}" alt="{{ $item->name }}"
                                        loading="lazy" width="40" height="40"
                                        class="tw-h-10 tw-w-10 tw-rounded-pill tw-object-cover">
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->name }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->email }}</td>
                                <td class="tw-px-3 tw-py-3">
                                    <x-badge :tone="$item->status == '1' ? 'success' : 'danger'">
                                        {{ $item->status == '1' ? __('Active') : __('Block') }}
                                    </x-badge>
                                </td>
                                <td class="tw-px-3 tw-py-3">
                                    <span class="tw-inline-flex tw-items-center tw-gap-2">
                                        <a href="{{ url('view-user/'.$item->id) }}" title="User Detail"
                                            aria-label="{{ __('View') }} {{ $item->name }}"
                                            class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-success-600 tw-transition hover:tw-bg-success-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-success-600 dark:tw-text-success-300 dark:hover:tw-bg-success-500/10">
                                            <x-lucide-eye class="tw-h-4 tw-w-4" />
                                        </a>
                                        @can('block_app_user')
                                            @if ($item->status == '0')
                                                <a href="{{ url('block-user/'.$item->id) }}" title="Unblock {{ $item->name }}"
                                                    aria-label="Unblock {{ $item->name }}"
                                                    class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-success-600 tw-transition hover:tw-bg-success-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-success-600 dark:tw-text-success-300 dark:hover:tw-bg-success-500/10">
                                                    <x-lucide-unlock class="tw-h-4 tw-w-4" />
                                                </a>
                                            @else
                                                <a href="{{ url('block-user/'.$item->id) }}" title="Block {{ $item->name }}"
                                                    aria-label="Block {{ $item->name }}"
                                                    class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-danger-600 tw-transition hover:tw-bg-danger-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-danger-600 dark:tw-text-danger-300 dark:hover:tw-bg-danger-500/10">
                                                    <x-lucide-ban class="tw-h-4 tw-w-4" />
                                                </a>
                                            @endif
                                        @endcan
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
@endsection
