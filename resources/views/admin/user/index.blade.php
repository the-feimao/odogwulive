@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Users')" />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('View Users') }}
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('Manage staff and organization accounts and their roles.') }}
            </p>
        </div>

        @can('user_create')
            <a href="{{ url('users/create') }}"
                class="tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                <x-lucide-plus class="tw-h-4 tw-w-4" />
                {{ __('Add New') }}
            </a>
        @endcan
    </div>

    <x-flash-messages />

    <x-card :padded="false">
        @if ($users->isEmpty())
            <x-empty-state :title="__('No users yet')" :description="__('Create a user account to get started.')" />
        @else
            <div class="tw-overflow-x-auto">
                <table id="report_table" class="tw-min-w-full tw-text-left tw-text-sm">
                    <thead>
                        <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                            <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                            <th class="tw-px-3 tw-py-3">{{ __('User') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Phone') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Role') }}</th>
                            @if (Gate::check('user_edit') || Gate::check('user_delete'))
                                <th class="tw-px-3 tw-py-3">{{ __('Action') }}</th>
                            @endif
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
                                    <div class="tw-flex tw-items-center tw-gap-3">
                                        <img src="{{ url('images/upload/'.$item->image) }}" alt="{{ $item->first_name }}"
                                            loading="lazy" width="40" height="40"
                                            class="tw-h-10 tw-w-10 tw-rounded-pill tw-object-cover">
                                        <div class="tw-min-w-0">
                                            <p class="tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">
                                                {{ $item->first_name.' '.$item->last_name }}
                                            </p>
                                            <p class="tw-truncate tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                                {{ $item->email }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-text-neutral-600 dark:tw-text-neutral-300">{{ $item->phone }}</td>
                                <td class="tw-px-3 tw-py-3">
                                    <div class="tw-flex tw-flex-wrap tw-gap-1.5">
                                        @forelse ($item->roles as $role)
                                            <x-badge tone="primary">{{ $role->name }}</x-badge>
                                        @empty
                                            <x-badge tone="warning">{{ __('No Data') }}</x-badge>
                                        @endforelse
                                    </div>
                                </td>
                                @if (Gate::check('user_edit') || Gate::check('user_delete'))
                                    <td class="tw-px-3 tw-py-3">
                                        @if (!$item->hasRole('admin'))
                                            @can('user_edit')
                                                <a href="{{ route('users.edit', $item->id) }}" title="Edit User"
                                                    aria-label="{{ __('Edit') }} {{ $item->first_name }}"
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
