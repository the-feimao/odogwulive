@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Customer Detail')" />

    <x-flash-messages />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ $user->name.' '.$user->last_name }}
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('Attendee profile, orders and followed organizers.') }}
            </p>
        </div>
    </div>

    <div class="tw-grid tw-gap-5 lg:tw-grid-cols-[minmax(0,320px)_minmax(0,1fr)]">
        <div class="tw-space-y-5">
            <x-card :padded="false">
                <x-slot:header>
                    <div class="tw-flex tw-items-center tw-gap-3">
                        <img src="{{ url('images/upload/'.$user->image) }}" alt="{{ $user->name }}"
                            loading="lazy" width="56" height="56"
                            class="tw-h-14 tw-w-14 tw-rounded-pill tw-object-cover">
                        <div>
                            <h2 class="tw-text-base tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">
                                {{ $user->name.' '.$user->last_name }}
                            </h2>
                            <p class="tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">{{ $user->email }}</p>
                        </div>
                    </div>
                </x-slot:header>

                <div class="tw-p-5">
                    @if ($user->address)
                        <p class="tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">{{ $user->address }}</p>
                    @endif

                    <div class="tw-mt-4 tw-grid tw-grid-cols-3 tw-gap-3">
                        <div class="tw-rounded-stub tw-bg-warning-50 tw-p-3 tw-text-center dark:tw-bg-warning-500/10">
                            <p class="tw-text-xl tw-font-extrabold tw-text-warning-700 dark:tw-text-warning-300">{{ $user->pending }}</p>
                            <p class="tw-mt-0.5 tw-text-xs tw-font-semibold tw-text-warning-800 dark:tw-text-warning-200/80">{{ __('Pending Order') }}</p>
                        </div>
                        <div class="tw-rounded-stub tw-bg-success-50 tw-p-3 tw-text-center dark:tw-bg-success-500/10">
                            <p class="tw-text-xl tw-font-extrabold tw-text-success-700 dark:tw-text-success-300">{{ $user->complete }}</p>
                            <p class="tw-mt-0.5 tw-text-xs tw-font-semibold tw-text-success-800 dark:tw-text-success-200/80">{{ __('Complete Order') }}</p>
                        </div>
                        <div class="tw-rounded-stub tw-bg-danger-50 tw-p-3 tw-text-center dark:tw-bg-danger-500/10">
                            <p class="tw-text-xl tw-font-extrabold tw-text-danger-700 dark:tw-text-danger-300">{{ $user->cancel }}</p>
                            <p class="tw-mt-0.5 tw-text-xs tw-font-semibold tw-text-danger-800 dark:tw-text-danger-200/80">{{ __('Cancel Order') }}</p>
                        </div>
                    </div>
                </div>
            </x-card>

            <x-card :title="__('Following ('.count($user->following).')')">
                @if (count($user->following) == 0)
                    <x-empty-state :title="__('No Data found')" />
                @else
                    <ul class="tw-space-y-3">
                        @foreach ($user->following as $item)
                            <li class="tw-flex tw-items-center tw-gap-3">
                                <img src="{{ url('images/upload/'.$item->image) }}" alt="{{ $item->first_name }}"
                                    loading="lazy" width="40" height="40"
                                    class="tw-h-10 tw-w-10 tw-rounded-pill tw-object-cover">
                                <div class="tw-min-w-0">
                                    <p class="tw-truncate tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">
                                        {{ $item->first_name.' '.$item->last_name }}
                                    </p>
                                    <p class="tw-truncate tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item->email }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </x-card>
        </div>

        <x-card :padded="false">
            <x-slot:header>
                <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">
                    {{ __('All Orders').'('.($user->pending + $user->cancel + $user->complete).')' }}
                </h2>
            </x-slot:header>

            <div class="tw-p-5">
                <ul class="nav nav-tabs tw-flex tw-gap-1 tw-border-b tw-border-neutral-200 dark:tw-border-neutral-800" id="myTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a class="nav-link active tw-rounded-t-stub tw-border-b-2 tw-border-accent-600 tw-px-3 tw-py-2 tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-50"
                            id="upcoming-tab" data-toggle="tab" href="#upcoming" role="tab" aria-controls="upcoming" aria-selected="true">
                            {{ __('Upcoming') }}
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a class="nav-link tw-rounded-t-stub tw-border-b-2 tw-border-transparent tw-px-3 tw-py-2 tw-text-sm tw-font-semibold tw-text-neutral-500 dark:tw-text-neutral-400"
                            id="past-tab" data-toggle="tab" href="#past" role="tab" aria-controls="past" aria-selected="false">
                            {{ __('Past') }}
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="upcoming" role="tabpanel" aria-labelledby="upcoming-tab">
                        @if (count($user->upcoming) == 0)
                            <x-empty-state :title="__('No Orders found')" />
                        @else
                            <div class="tw-divide-y tw-divide-neutral-100 dark:tw-divide-neutral-800">
                                @foreach ($user->upcoming as $item)
                                    @php
                                        $tone = match ($item->order_status) {
                                            'Pending' => 'warning',
                                            'Complete' => 'success',
                                            'Cancel' => 'danger',
                                            default => 'neutral',
                                        };
                                    @endphp
                                    <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3 tw-py-4">
                                        <div class="tw-min-w-0">
                                            <a href="{{ url('order-invoice/'.$item->id) }}"
                                                class="tw-text-sm tw-font-bold tw-text-accent-600 tw-transition hover:tw-text-accent-500 dark:tw-text-accent-300">
                                                {{ $item->order_id }}
                                            </a>
                                            <p class="tw-mt-0.5 tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">
                                                {{ $item->event->name.' ('.$item->quantity.' Tickets)' }}
                                            </p>
                                            <p class="tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                                {{ $item->event->start_time->format('Y-m-d H:i a') }}
                                            </p>
                                        </div>
                                        <div class="tw-text-left sm:tw-text-right">
                                            <p class="tw-text-sm tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ $currency.$item->payment.'.00' }}</p>
                                            <x-badge :tone="$tone" class="tw-mt-1">{{ $item->order_status }}</x-badge>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="tab-pane fade" id="past" role="tabpanel" aria-labelledby="past-tab">
                        @if (count($user->past) == 0)
                            <x-empty-state :title="__('No Orders found')" />
                        @else
                            <div class="tw-divide-y tw-divide-neutral-100 dark:tw-divide-neutral-800">
                                @foreach ($user->past as $item)
                                    @php
                                        $tone = match ($item->order_status) {
                                            'Pending' => 'warning',
                                            'Complete' => 'success',
                                            'Cancel' => 'danger',
                                            default => 'neutral',
                                        };
                                    @endphp
                                    <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3 tw-py-4">
                                        <div class="tw-min-w-0">
                                            <a href="{{ url('order-invoice/'.$item->id) }}"
                                                class="tw-text-sm tw-font-bold tw-text-accent-600 tw-transition hover:tw-text-accent-500 dark:tw-text-accent-300">
                                                {{ $item->order_id }}
                                            </a>
                                            <p class="tw-mt-0.5 tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">
                                                {{ $item->event->name.' ('.$item->quantity.' Tickets)' }}
                                            </p>
                                            <p class="tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                                {{ $item->event->start_time->format('Y-m-d H:i a') }}
                                            </p>
                                        </div>
                                        <div class="tw-text-left sm:tw-text-right">
                                            <p class="tw-text-sm tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ $currency.$item->payment.'.00' }}</p>
                                            <x-badge :tone="$tone" class="tw-mt-1">{{ $item->order_status }}</x-badge>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </x-card>
    </div>
@endsection
