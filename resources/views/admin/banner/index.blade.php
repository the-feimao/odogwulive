@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Banner')" />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('All Banner') }}
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('Banners shown across the public site.') }}
            </p>
        </div>

        @can('banner_create')
            <a href="{{ url('banner/create') }}"
                class="tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                <x-lucide-plus class="tw-h-4 tw-w-4" />
                {{ __('Add New') }}
            </a>
        @endcan
    </div>

    <x-flash-messages />

    <x-card :padded="false">
        @if ($banner->isEmpty())
            <x-empty-state :title="__('No banners yet')" :description="__('Add a banner to feature a campaign or event.')" />
        @else
            <div class="tw-overflow-x-auto">
                <table id="report_table" class="tw-min-w-full tw-text-left tw-text-sm">
                    <thead>
                        <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                            <th class="tw-w-10 tw-px-3 tw-py-3"></th>
                            <th class="tw-px-3 tw-py-3">{{ __('Image') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Title') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Status') }}</th>
                            @if (Gate::check('banner_edit') || Gate::check('banner_delete'))
                                <th class="tw-px-3 tw-py-3">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($banner as $item)
                            <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                                <td class="tw-px-3 tw-py-3"></td>
                                <td class="tw-px-3 tw-py-3">
                                    <img src="{{ url('images/upload/'.$item->image) }}" alt="{{ $item->title }}"
                                        loading="lazy" class="tw-h-12 tw-w-20 tw-rounded-stub tw-object-cover">
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->title }}</td>
                                <td class="tw-px-3 tw-py-3">
                                    <x-badge :tone="$item->status == '1' ? 'success' : 'danger'">
                                        {{ $item->status == '1' ? __('Active') : __('Inactive') }}
                                    </x-badge>
                                </td>
                                @if (Gate::check('banner_edit') || Gate::check('banner_delete'))
                                    <td class="tw-px-3 tw-py-3">
                                        <div class="tw-flex tw-items-center tw-gap-1">
                                            @can('banner_edit')
                                                <a href="{{ route('banner.edit', $item->id) }}" title="{{ __('Edit Banner') }}"
                                                    class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-accent-600 tw-transition hover:tw-bg-accent-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:hover:tw-bg-accent-900/20">
                                                    <x-lucide-pencil class="tw-h-4 tw-w-4" />
                                                    <span class="tw-sr-only">{{ __('Edit Banner') }}</span>
                                                </a>
                                            @endcan
                                            @can('banner_delete')
                                                <button type="button" onclick="deleteData('banner','{{ $item->id }}');" title="{{ __('Delete Banner') }}"
                                                    class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-danger-600 tw-transition hover:tw-bg-danger-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-danger-600 dark:hover:tw-bg-danger-900/20">
                                                    <x-lucide-trash-2 class="tw-h-4 tw-w-4" />
                                                    <span class="tw-sr-only">{{ __('Delete Banner') }}</span>
                                                </button>
                                            @endcan
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
@endsection
