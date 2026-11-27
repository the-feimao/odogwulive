@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Events')" />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('All Events') }}
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('Manage events, tickets and assigned scanners.') }}
            </p>
        </div>

        @can('event_create')
            <a href="{{ url('events/create') }}"
                class="add-button tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                <x-lucide-plus class="tw-h-4 tw-w-4" />
                {{ __('Add New') }}
            </a>
        @endcan
    </div>

    <x-flash-messages />

    <x-card :padded="false">
        <div class="tw-overflow-x-auto">
            <table id="report_table" class="tw-min-w-full tw-text-left tw-text-sm">
                <thead>
                    <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                        <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                        <th class="tw-px-3 tw-py-3">{{ __('Image') }}</th>
                        <th class="tw-px-3 tw-py-3">{{ __('Name') }}</th>
                        <th class="tw-px-3 tw-py-3">{{ __('Number of People') }}</th>
                        <th class="tw-px-3 tw-py-3">{{ __('Category') }}</th>
                        @if (Auth::user()->hasRole('admin'))
                            <th class="tw-px-3 tw-py-3">{{ __('Organization') }}</th>
                        @endif
                        @if (Auth::user()->hasRole('organization'))
                            <th class="tw-px-3 tw-py-3">{{ __('Scanners') }}</th>
                        @endif
                        <th class="tw-px-3 tw-py-3">{{ __('Status') }}</th>
                        @if (Gate::check('event_edit') || Gate::check('event_delete'))
                            <th class="tw-px-3 tw-py-3">{{ __('Action') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($events as $item)
                        <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                            <td class="tw-px-3 tw-py-3"></td>
                            <td class="tw-px-3 tw-py-3">
                                <img class="table-img tw-h-12 tw-w-12 tw-rounded-stub tw-object-cover"
                                    src="{{ url('images/upload/'.$item->image) }}" alt="{{ $item->name }}"
                                    loading="lazy" width="48" height="48">
                            </td>
                            <td class="tw-px-3 tw-py-3">
                                <p class="tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->name }}</p>
                                <p class="tw-mt-0.5 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item->address }}</p>
                                <p class="tw-mt-0.5 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                    {{ Carbon\Carbon::parse($item->start_time)->format('l').', '.$item->start_time->format('Y-m-d h:i a') }}
                                </p>
                            </td>
                            <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->people }}</td>
                            <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->category->name }}</td>
                            @if (Auth::user()->hasRole('admin'))
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">
                                    {{ $item->organization->first_name.' '.$item->organization->last_name }}
                                </td>
                            @endif
                            @if (Auth::user()->hasRole('organization'))
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">
                                    {{ $item->scanners->map(fn ($scanner) => trim($scanner->first_name.' '.$scanner->last_name) ?: ($scanner->name ?: $scanner->email))->implode(', ') }}
                                </td>
                            @endif
                            <td class="tw-px-3 tw-py-3">
                                <x-badge :tone="$item->status == '1' ? 'success' : 'warning'">
                                    {{ $item->status == '1' ? __('Publish') : __('Draft') }}
                                </x-badge>
                            </td>
                            @if (Gate::check('event_edit') || Gate::check('event_delete'))
                                <td class="tw-px-3 tw-py-3">
                                    <span class="tw-inline-flex tw-items-center tw-gap-2">
                                        <a href="{{ route('events.show', $item->id) }}" title="{{ __('View Event') }}"
                                            aria-label="{{ __('View') }} {{ $item->name }}"
                                            class="btn-icon tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-accent-600 tw-transition hover:tw-bg-accent-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:hover:tw-bg-accent-900/20">
                                            <x-lucide-eye class="tw-h-4 tw-w-4" />
                                        </a>
                                        <a href="{{ url('event-gallery/'.$item->id) }}" title="{{ __('Event Gallery') }}"
                                            aria-label="{{ __('Gallery') }} {{ $item->name }}"
                                            class="btn-icon tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-accent-600 tw-transition hover:tw-bg-accent-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:hover:tw-bg-accent-900/20">
                                            <x-lucide-images class="tw-h-4 tw-w-4" />
                                        </a>
                                        @can('event_edit')
                                            <a href="{{ route('events.edit', $item->id) }}" title="{{ __('Edit Event') }}"
                                                aria-label="{{ __('Edit') }} {{ $item->name }}"
                                                class="btn-icon tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-accent-600 tw-transition hover:tw-bg-accent-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:hover:tw-bg-accent-900/20">
                                                <x-lucide-pencil class="tw-h-4 tw-w-4" />
                                            </a>
                                        @endcan
                                        @can('event_delete')
                                            <a href="javascript:void(0)" onclick="deleteData('events','{{ $item->id }}');"
                                                title="{{ __('Delete Event') }}" aria-label="{{ __('Delete') }} {{ $item->name }}"
                                                class="btn-icon tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-border tw-border-danger-200 tw-text-danger-600 tw-transition hover:tw-bg-danger-100 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-danger-600 dark:tw-border-danger-500/30 dark:tw-text-danger-300 dark:hover:tw-bg-danger-500/10">
                                                <x-lucide-trash-2 class="tw-h-4 tw-w-4" />
                                            </a>
                                        @endcan
                                    </span>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
@endsection
