@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Roles')" />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('View Roles') }}
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('Review roles and the permissions assigned to each one.') }}
            </p>
        </div>

        @can('role_create')
            <a href="{{ url('roles/create') }}"
                class="tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                <x-lucide-plus class="tw-h-4 tw-w-4" />
                {{ __('Add New') }}
            </a>
        @endcan
    </div>

    <x-flash-messages />

    <x-card :padded="false">
        @if ($roles->isEmpty())
            <x-empty-state :title="__('No roles yet')" :description="__('Create a role to group permissions together.')" />
        @else
            <div class="tw-overflow-x-auto">
                <table id="report_table" class="tw-min-w-full tw-text-left tw-text-sm">
                    <thead>
                        <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                            <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                            <th class="tw-px-3 tw-py-3">{{ __('Title') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Permissions') }}</th>
                            @if (Gate::check('role_edit') || Gate::check('role_delete'))
                                <th class="tw-px-3 tw-py-3">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $item)
                            <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                                <td class="tw-px-3 tw-py-3">
                                    <span class="tw-inline-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-stub tw-bg-primary-100 tw-text-primary-700 dark:tw-bg-primary-500/20 dark:tw-text-primary-300">
                                        <x-lucide-shield class="tw-h-4 tw-w-4" />
                                    </span>
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->name }}</td>
                                <td class="tw-px-3 tw-py-3">
                                    <div class="tw-flex tw-flex-wrap tw-gap-1.5">
                                        @forelse ($item->permissions as $permission)
                                            <x-badge tone="success">{{ $permission->name }}</x-badge>
                                        @empty
                                            <x-badge tone="warning">
                                                {{ $item->name == 'admin' ? __('All') : __('No Data') }}
                                            </x-badge>
                                        @endforelse
                                    </div>
                                </td>
                                @if (Gate::check('role_edit') || Gate::check('role_delete'))
                                    <td class="tw-px-3 tw-py-3">
                                        @if ($item->name != 'admin')
                                            @can('role_edit')
                                                <a href="{{ route('roles.edit', $item->id) }}" aria-label="{{ __('Edit') }} {{ $item->name }}"
                                                    class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-accent-600 tw-transition hover:tw-bg-accent-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:hover:tw-bg-accent-900/20">
                                                    <x-lucide-pencil class="tw-h-4 tw-w-4" />
                                                </a>
                                            @endcan
                                        @endif
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
