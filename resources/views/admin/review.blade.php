@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Reviews')" />

    <x-flash-messages />

    <x-card :padded="false">
        <x-slot:header>
            <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Reviews') }}</h2>
        </x-slot:header>

        @if ($data->isEmpty())
            <x-empty-state :title="__('No reviews yet')" :description="__('Customer reviews will appear here.')" />
        @else
            <div class="tw-overflow-x-auto">
                <table class="tw-min-w-full tw-text-left tw-text-sm" id="report_table">
                    <thead>
                        <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                            <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                            <th class="tw-px-3 tw-py-3">{{ __('Customer') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Event Name') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Message') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Rate') }}</th>
                            @if (Auth::user()->hasRole('admin'))
                                <th class="tw-px-3 tw-py-3">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $item)
                            <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                                <td class="tw-px-3 tw-py-3"></td>
                                <td class="tw-px-3 tw-py-3">
                                    <span class="tw-inline-flex tw-items-center tw-gap-3">
                                        <img src="{{ url('images/upload/'.$item->reviewer?->image) }}" alt="{{ $item->reviewer?->name }}"
                                            loading="lazy" class="tw-h-9 tw-w-9 tw-rounded-pill tw-object-cover">
                                        <span>
                                            <span class="tw-block tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">
                                                {{ trim(($item->reviewer?->name ?? '').' '.($item->reviewer?->last_name ?? '')) }}
                                            </span>
                                            <span class="tw-block tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item->reviewer?->email }}</span>
                                        </span>
                                    </span>
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->eventName }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->message }}</td>
                                <td class="tw-px-3 tw-py-3">
                                    <span class="tw-inline-flex tw-items-center tw-gap-0.5" aria-label="{{ $item->rate }} / 5">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <x-lucide-star class="tw-h-4 tw-w-4 {{ $item->rate >= $i ? 'tw-fill-current tw-text-warning-500' : 'tw-text-neutral-300 dark:tw-text-neutral-700' }}" />
                                        @endfor
                                    </span>
                                </td>
                                @if (Auth::user()->hasRole('admin'))
                                    <td class="tw-px-3 tw-py-3">
                                        <span class="tw-inline-flex tw-items-center tw-gap-2">
                                            @if ($item->status == 0)
                                                <a href="{{ url('change-review-status/'.$item->id) }}"
                                                    class="tw-inline-flex tw-h-9 tw-items-center tw-rounded-pill tw-bg-primary-500 tw-px-3 tw-text-xs tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                                                    {{ __('Publish') }}
                                                </a>
                                            @endif
                                            <a href="{{ url('delete-review/'.$item->id) }}"
                                                class="tw-inline-flex tw-h-9 tw-items-center tw-rounded-pill tw-bg-danger-500 tw-px-3 tw-text-xs tw-font-bold tw-text-neutral-0 tw-transition hover:tw-bg-danger-600 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-danger-600">
                                                {{ __('Delete') }}
                                            </a>
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
