@extends('layouts.admin')

@section('admin-content')
    @php
        $template = $data->first();
    @endphp

    <x-admin-breadcrumb :title="__('Notification Template')" />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('Notification Template') }}
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('Choose a template, then edit its email and app notification content.') }}
            </p>
        </div>

        @can('notification_template_create')
            <a href="{{ url('notification-template/create') }}"
                class="tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                <x-lucide-plus class="tw-h-4 tw-w-4" />
                {{ __('Add New') }}
            </a>
        @endcan
    </div>

    <x-flash-messages />

    <x-card :padded="false">
        @if (! $template)
            <x-empty-state :title="__('No templates yet')" :description="__('Add a notification template to customise outgoing messages.')" />
        @else
            <div class="tw-grid tw-gap-5 tw-p-5 lg:tw-grid-cols-[minmax(0,210px)_minmax(0,1fr)] xl:tw-grid-cols-[minmax(0,210px)_minmax(0,1fr)_minmax(0,190px)]">
                <div class="tw-space-y-2">
                    <h2 class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">
                        {{ __('Templates') }}
                    </h2>
                    @foreach ($data as $item)
                        <button type="button" onclick="notificationDetail({{ $item->id }});"
                            class="template-btn tw-flex tw-w-full tw-items-center tw-gap-2 tw-rounded-stub tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-px-3 tw-py-2 tw-left tw-text-left tw-text-sm tw-font-semibold tw-text-ink-800 tw-transition hover:tw-border-accent-300 hover:tw-bg-accent-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-bg-ink-900 dark:tw-text-neutral-100 dark:hover:tw-border-accent-500/40 dark:hover:tw-bg-accent-500/10">
                            <x-lucide-bell class="tw-h-4 tw-w-4 tw-shrink-0 tw-text-accent-600 dark:tw-text-accent-300" />
                            <span class="tw-truncate">{{ $item->title }}</span>
                        </button>
                    @endforeach
                </div>

                <form action="{{ url('notification-template/'.$template->id) }}" id="edit-template-form" method="post" class="tw-grid tw-gap-5">
                    @csrf
                    @method('PUT')

                    <x-input name="subject" :label="__('Subject')" :placeholder="__('Subject')" :value="$template->subject" />

                    <x-textarea name="mail_content" :label="__('Email Content')" :placeholder="__('Email Content')"
                        :value="$template->mail_content" rows="8" class="textarea_editor" />

                    <x-textarea name="message_content" id="message_content" :label="__('App notification message Content')"
                        :placeholder="__('App notification message Content')" :value="$template->message_content" rows="5" />

                    <div>
                        <x-button type="submit">{{ __('Submit') }}</x-button>
                    </div>
                </form>

                <div class="lg:tw-col-span-2 xl:tw-col-span-1">
                    <h2 class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">
                        {{ __('Email Placeholder') }}
                    </h2>
                    <div class="tw-mt-2 tw-flex tw-flex-wrap tw-gap-2">
                        <button type="button" title="Customer Name" data-toggle="tooltip" data-title="Customer Name"
                            class="tw-rounded-pill tw-bg-neutral-100 tw-px-3 tw-py-1.5 tw-font-mono tw-text-xs tw-text-neutral-700 tw-transition hover:tw-bg-accent-100 hover:tw-text-accent-800 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-bg-neutral-800 dark:tw-text-neutral-200 dark:hover:tw-bg-accent-500/20">@{{user_name}}</button>
                        <button type="button" title="Organizer Name" data-toggle="tooltip" data-title="Organizer Name"
                            class="tw-rounded-pill tw-bg-neutral-100 tw-px-3 tw-py-1.5 tw-font-mono tw-text-xs tw-text-neutral-700 tw-transition hover:tw-bg-accent-100 hover:tw-text-accent-800 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-bg-neutral-800 dark:tw-text-neutral-200 dark:hover:tw-bg-accent-500/20">@{{organizer_name}}</button>
                        <button type="button" title="Application Name" data-toggle="tooltip" data-title="Application Name"
                            class="tw-rounded-pill tw-bg-neutral-100 tw-px-3 tw-py-1.5 tw-font-mono tw-text-xs tw-text-neutral-700 tw-transition hover:tw-bg-accent-100 hover:tw-text-accent-800 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-bg-neutral-800 dark:tw-text-neutral-200 dark:hover:tw-bg-accent-500/20">@{{app_name}}</button>
                        <button type="button" title="Event Name" data-toggle="tooltip" data-title="Event Name"
                            class="tw-rounded-pill tw-bg-neutral-100 tw-px-3 tw-py-1.5 tw-font-mono tw-text-xs tw-text-neutral-700 tw-transition hover:tw-bg-accent-100 hover:tw-text-accent-800 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-bg-neutral-800 dark:tw-text-neutral-200 dark:hover:tw-bg-accent-500/20">@{{event_name}}</button>
                        <button type="button" title="Event date" data-toggle="tooltip" data-title="Event date"
                            class="tw-rounded-pill tw-bg-neutral-100 tw-px-3 tw-py-1.5 tw-font-mono tw-text-xs tw-text-neutral-700 tw-transition hover:tw-bg-accent-100 hover:tw-text-accent-800 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-bg-neutral-800 dark:tw-text-neutral-200 dark:hover:tw-bg-accent-500/20">@{{date}}</button>
                        <button type="button" title="Number of purchased tickets" data-toggle="tooltip" data-title="Number of purchased tickets"
                            class="tw-rounded-pill tw-bg-neutral-100 tw-px-3 tw-py-1.5 tw-font-mono tw-text-xs tw-text-neutral-700 tw-transition hover:tw-bg-accent-100 hover:tw-text-accent-800 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-bg-neutral-800 dark:tw-text-neutral-200 dark:hover:tw-bg-accent-500/20">@{{quantity}}</button>
                        <button type="button" title="Password" data-toggle="tooltip" data-title="Password"
                            class="tw-rounded-pill tw-bg-neutral-100 tw-px-3 tw-py-1.5 tw-font-mono tw-text-xs tw-text-neutral-700 tw-transition hover:tw-bg-accent-100 hover:tw-text-accent-800 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-bg-neutral-800 dark:tw-text-neutral-200 dark:hover:tw-bg-accent-500/20">@{{password}}</button>
                    </div>
                </div>
            </div>
        @endif
    </x-card>
@endsection
