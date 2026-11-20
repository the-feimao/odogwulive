@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Tax')" />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('View Tax') }}
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('Manage tax charges applied to ticket bills.') }}
            </p>
        </div>

        @can('tax_create')
            <a href="{{ url('tax/create') }}"
                class="tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                <x-lucide-plus class="tw-h-4 tw-w-4" />
                {{ __('Add New') }}
            </a>
        @endcan
    </div>

    <x-flash-messages />

    <x-card :padded="false">
        @if ($taxes->isEmpty())
            <x-empty-state :title="__('No taxes yet')" :description="__('Add a tax charge to make it available on bills.')" />
        @else
            <div class="tw-overflow-x-auto">
                <table id="report_table" class="tw-min-w-full tw-text-left tw-text-sm">
                    <thead>
                        <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                            <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                            <th class="tw-px-3 tw-py-3">{{ __('name') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Charges') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Allow in all bills') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Status') }}</th>
                            @if (Gate::check('tax_edit') || Gate::check('tax_delete'))
                                <th class="tw-px-3 tw-py-3">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($taxes as $item)
                            <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                                <td class="tw-px-3 tw-py-3"></td>
                                <td class="tw-px-3 tw-py-3 tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->name }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $currency.$item->price }}</td>
                                <td class="tw-px-3 tw-py-3">
                                    <x-badge :tone="$item->allow_all_bill == 1 ? 'success' : 'danger'">
                                        {{ $item->allow_all_bill == 1 ? __('Allow') : __('Deny') }}
                                    </x-badge>
                                </td>
                                <td class="tw-px-3 tw-py-3">
                                    <x-badge :tone="$item->status == 1 ? 'success' : 'danger'">
                                        {{ $item->status == 1 ? __('Active') : __('Inactive') }}
                                    </x-badge>
                                </td>
                                @if (Gate::check('tax_edit') || Gate::check('tax_delete'))
                                    <td class="tw-px-3 tw-py-3">
                                        <span class="tw-inline-flex tw-items-center tw-gap-2">
                                            @can('tax_edit')
                                                <a href="{{ route('tax.edit', $item->id) }}" aria-label="{{ __('Edit') }} {{ $item->name }}"
                                                    class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-accent-600 tw-transition hover:tw-bg-accent-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:hover:tw-bg-accent-900/20">
                                                    <x-lucide-pencil class="tw-h-4 tw-w-4" />
                                                </a>
                                            @endcan
                                            @can('tax_delete')
                                                <span aria-label="{{ __('Delete') }} {{ $item->name }}"
                                                    class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-danger-600">
                                                    <x-lucide-trash-2 class="tw-h-4 tw-w-4" />
                                                </span>
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
