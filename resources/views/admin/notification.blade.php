@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Notification')" />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('Notification') }}
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('Recent order activity for your account.') }}
            </p>
        </div>
    </div>

    <x-flash-messages />

    <x-card :padded="false">
        <x-slot:header>
            <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Activity') }}</h2>
        </x-slot:header>

        <div class="activities tw-divide-y tw-divide-neutral-100 dark:tw-divide-neutral-800">
            @if (count($notification) == 0)
                <x-empty-state :title="__('No Notification found')" :description="__('New activity will appear here.')" />
            @else
                @foreach ($notification as $item)
                    @if ($item->actor != null)
                        <div class="activity tw-flex tw-items-start tw-gap-4 tw-p-5">
                            <img src="{{ url('images/upload/'.$item->actor->image) }}" alt="{{ $item->actor->name }}"
                                loading="lazy" class="tw-h-10 tw-w-10 tw-shrink-0 tw-rounded-pill tw-object-cover">

                            <div class="tw-min-w-0 tw-flex-1">
                                <div class="tw-flex tw-flex-wrap items-start tw-justify-between tw-gap-3">
                                    <div class="tw-min-w-0">
                                        <p class="tw-text-xs tw-font-semibold tw-text-primary-700 dark:tw-text-primary-300">
                                            {{ $item->created_at->diffForHumans() }}
                                        </p>
                                        <p class="tw-mt-0.5 tw-truncate tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">
                                            {{ $item->actor->name.' '.$item->actor->last_name }}
                                        </p>
                                    </div>

                                    <x-dropdown :label="__('Options')" align="right" class="tw-shrink-0">
                                        <a href="{{ url('orders/'.$item->order_id) }}" role="menuitem"
                                            class="tw-flex tw-items-center tw-gap-2 tw-rounded-stub tw-px-3 tw-py-2 tw-text-sm tw-text-ink-700 tw-transition hover:tw-bg-neutral-100 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-neutral-200 dark:hover:tw-bg-neutral-800">
                                            <x-lucide-eye class="tw-h-4 tw-w-4" />
                                            {{ __('View') }}
                                        </a>
                                        <div class="tw-my-1 tw-border-t tw-border-neutral-100 dark:tw-border-neutral-800"></div>
                                        <a href="{{ url('delete-notification/'.$item->id) }}" role="menuitem"
                                            class="tw-flex tw-items-center tw-gap-2 tw-rounded-stub tw-px-3 tw-py-2 tw-text-sm tw-font-semibold tw-text-danger-600 tw-transition hover:tw-bg-danger-100 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-danger-600 dark:tw-text-danger-300 dark:hover:tw-bg-danger-500/10">
                                            <x-lucide-archive class="tw-h-4 tw-w-4" />
                                            {{ __('Archive') }}
                                        </a>
                                    </x-dropdown>
                                </div>

                                <p class="tw-mt-2 tw-text-sm tw-leading-relaxed tw-text-neutral-600 dark:tw-text-neutral-300">
                                    {{ $item->message }}
                                </p>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif
        </div>
    </x-card>
@endsection
