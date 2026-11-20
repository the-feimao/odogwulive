@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Feedback')" />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('All Feedback') }}
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('Reviews submitted by organizers.') }}
            </p>
        </div>

        @can('feedback_create')
            <a href="{{ url('feedback/create') }}"
                class="tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                <x-lucide-plus class="tw-h-4 tw-w-4" />
                {{ __('Add New') }}
            </a>
        @endcan
    </div>

    <x-flash-messages />

    <x-card :padded="false">
        @if ($feedback->isEmpty())
            <x-empty-state :title="__('No feedback yet')" :description="__('Submitted feedback will appear here.')" />
        @else
            <div class="tw-overflow-x-auto">
                <table class="tw-min-w-full tw-text-left tw-text-sm" id="report_table">
                    <thead>
                        <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                            <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                            <th class="tw-px-3 tw-py-3">{{ __('Organizer') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Message') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Rate') }}</th>
                            @if (Gate::check('feedback_edit') || Gate::check('feedback_delete'))
                                <th class="tw-px-3 tw-py-3">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($feedback as $item)
                            <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                                <td class="tw-px-3 tw-py-3"></td>
                                <td class="tw-px-3 tw-py-3">
                                    <span class="tw-inline-flex tw-items-center tw-gap-3">
                                        <img src="{{ url('images/upload/'.$item->user->image) }}" alt="{{ $item->user->name }}"
                                            loading="lazy" class="tw-h-10 tw-w-10 tw-rounded-pill tw-object-cover">
                                        <span class="tw-min-w-0">
                                            <span class="tw-block tw-truncate tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">
                                                {{ $item->user->name }}
                                            </span>
                                            <span class="tw-block tw-truncate tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                                {{ $item->email }}
                                            </span>
                                        </span>
                                    </span>
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">
                                    {{ $item->message }}
                                </td>
                                <td class="tw-px-3 tw-py-3">
                                    <span class="tw-inline-flex tw-items-center tw-gap-0.5" aria-label="{{ $item->rate }} / 5">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <x-lucide-star class="tw-h-4 tw-w-4 {{ $item->rate >= $i ? 'tw-fill-current tw-text-warning-500' : 'tw-text-neutral-300 dark:tw-text-neutral-700' }}" />
                                        @endfor
                                    </span>
                                </td>
                                @if (Gate::check('feedback_edit') || Gate::check('feedback_delete'))
                                    <td class="tw-px-3 tw-py-3">
                                        <span class="tw-inline-flex tw-items-center tw-gap-2">
                                            @can('feedback_edit')
                                                <a href="{{ route('feedback.edit', $item->id) }}" title="{{ __('Edit Feedback') }}"
                                                    class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-border tw-border-neutral-200 tw-text-ink-700 tw-transition hover:tw-bg-neutral-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-text-neutral-200 dark:hover:tw-bg-neutral-800">
                                                    <x-lucide-pencil class="tw-h-4 tw-w-4" />
                                                </a>
                                            @endcan
                                            @can('feedback_delete')
                                                <a href="javascript:void(0)" onclick="deleteData('feedback','{{ $item->id }}');" title="{{ __('Delete Feedback') }}"
                                                    class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-border tw-border-danger-200 tw-text-danger-600 tw-transition hover:tw-bg-danger-100 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-danger-600 dark:tw-border-danger-500/30 dark:tw-text-danger-300 dark:hover:tw-bg-danger-500/10">
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
        @endif
    </x-card>
@endsection
