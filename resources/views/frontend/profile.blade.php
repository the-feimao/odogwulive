@extends('layouts.public')

@section('title', __('My profile').' — '.config('brand.name'))

@php
    $imageUrl = function ($image) {
        return $image ? url('images/upload/'.$image) : asset('web/bg/1.jpg');
    };
    $eventUrl = function ($event) {
        return url('/event/'.$event->id.'/'.\Illuminate\Support\Str::slug($event->name));
    };
    $blogUrl = function ($blog) {
        return url('/blog-detail/'.$blog->id.'/'.preg_replace('/\s+/', '-', $blog->title));
    };
    $savedEvents = $user->saved_event ?? collect();
    $savedBlogs = $user->saved_blog ?? collect();
    $following = $user->following ?? collect();
    $favoriteEvents = array_map('strval', array_filter(explode(',', (string) ($user->favorite ?? ''))));
    $favoriteBlogs = array_map('strval', array_filter(explode(',', (string) ($user->favorite_blog ?? ''))));
@endphp

@section('content')
    <section class="tw-relative tw-overflow-hidden tw-bg-ink-950">
        <img src="{{ asset('web/bg/1.jpg') }}" alt="" width="1600" height="700"
            class="tw-absolute tw-inset-0 tw-h-full tw-w-full tw-object-cover tw-opacity-20">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-r tw-from-ink-950 tw-via-ink-950/90 tw-to-ink-950/40"></div>

        <div class="tw-relative tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
            <nav aria-label="{{ __('Breadcrumb') }}" class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-text-neutral-400">
                <a href="{{ url('/') }}" class="focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500 hover:tw-text-neutral-50">{{ __('Home') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <span class="tw-text-neutral-200">{{ __('My profile') }}</span>
            </nav>

            <div class="tw-mt-6 tw-flex tw-flex-col tw-items-start tw-gap-5 sm:tw-flex-row sm:tw-items-center">
                <img src="{{ $imageUrl($user->image) }}" alt="{{ $user->name.' '.$user->last_name }}"
                    id="imagePreview" width="112" height="112"
                    class="tw-h-28 tw-w-28 tw-shrink-0 tw-rounded-pill tw-border-4 tw-border-primary-400/70 tw-object-cover tw-shadow-card-hover">
                <div>
                    <x-badge tone="primary">{{ __('Attendee') }}</x-badge>
                    <h1 class="tw-mt-2 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-neutral-50 md:tw-text-4xl">
                        {{ $user->name.' '.$user->last_name }}
                    </h1>
                    <div class="tw-mt-3 tw-flex tw-flex-wrap tw-items-center tw-gap-x-5 tw-gap-y-2 tw-text-sm tw-text-neutral-300">
                        <span class="tw-inline-flex tw-items-center tw-gap-1.5"><x-lucide-mail class="tw-h-4 tw-w-4" />{{ $user->email }}</span>
                        @if ($user->address)
                            <span class="tw-inline-flex tw-items-center tw-gap-1.5"><x-lucide-map-pin class="tw-h-4 tw-w-4" />{{ $user->address }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14"
        data-user-area
        data-profile-page
        data-image-base="{{ url('images/upload') }}"
        data-image-fallback="{{ asset('web/bg/1.jpg') }}"
        data-bio-endpoint="{{ url('/add-bio') }}"
        data-favorite-endpoint="{{ url('/add-favorite') }}"
        data-follow-endpoint="{{ url('/add-followList') }}">
        <p id="user-area-status" hidden
            class="tw-mb-5 tw-rounded-stub tw-border tw-border-primary-200 tw-bg-primary-50 tw-px-4 tw-py-3 tw-text-sm tw-font-semibold tw-text-primary-900 dark:tw-border-primary-500/30 dark:tw-bg-primary-500/10 dark:tw-text-primary-200"
            aria-live="polite"></p>

        <div class="tw-grid tw-gap-8 lg:tw-grid-cols-[280px_minmax(0,1fr)]">
            <aside class="tw-min-w-0 tw-space-y-6 lg:tw-sticky lg:tw-top-24 lg:tw-self-start">
                <x-card :title="__('Profile details')">
                    <form method="post" action="{{ url('upload-profile-image') }}" id="imageUploadForm" enctype="multipart/form-data">
                        @csrf
                        <input type="file" name="image" id="imgUpload" accept="image/*" class="tw-sr-only">
                        <button type="button" id="OpenImgUpload"
                            class="tw-inline-flex tw-h-9 tw-items-center tw-gap-2 tw-rounded-pill tw-border tw-border-neutral-200 tw-px-3 tw-text-xs tw-font-bold tw-text-ink-800 tw-transition-colors hover:tw-border-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600 dark:tw-border-neutral-700 dark:tw-text-neutral-100">
                            <x-lucide-camera class="tw-h-4 tw-w-4" />
                            {{ __('Change photo') }}
                        </button>
                    </form>

                    <dl class="tw-mt-5 tw-space-y-3 tw-border-t tw-border-neutral-100 tw-pt-5 tw-text-sm dark:tw-border-neutral-800">
                        <div class="tw-flex tw-justify-between tw-gap-3">
                            <dt class="tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Phone') }}</dt>
                            <dd class="tw-font-semibold tw-text-right tw-text-ink-950 dark:tw-text-neutral-100">{{ $user->phone ?: '—' }}</dd>
                        </div>
                        <div class="tw-flex tw-justify-between tw-gap-3">
                            <dt class="tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Language') }}</dt>
                            <dd class="tw-font-semibold tw-text-ink-950 dark:tw-text-neutral-100">{{ $user->language ?: 'English' }}</dd>
                        </div>
                    </dl>

                    <div class="bio-section tw-mt-5 tw-border-t tw-border-neutral-100 tw-pt-5 dark:tw-border-neutral-800" data-bio-section>
                        <p class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Bio') }}</p>
                        <p data-bio-display class="tw-mt-2 tw-text-sm tw-leading-relaxed tw-text-neutral-600 dark:tw-text-neutral-300">
                            {{ $user->bio ?: __('Add a short bio so other attendees can recognise you.') }}
                        </p>
                        <input type="text" name="bio" class="bio-control hide tw-mt-2 tw-h-10 tw-w-full tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-px-3 tw-text-sm tw-text-ink-900 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-accent-600/20 dark:tw-border-neutral-700 dark:tw-bg-ink-900 dark:tw-text-neutral-50"
                            placeholder="{{ __('Your Bio') }}" value="{{ $user->bio }}" hidden>
                        <button type="button" class="btn-bio tw-mt-3 tw-inline-flex tw-items-center tw-gap-1.5 tw-text-xs tw-font-bold tw-text-primary-700 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-primary-300">
                            <x-lucide-pencil class="tw-h-3.5 tw-w-3.5" />
                            {{ $user->bio ? __('Edit bio') : __('Add Bio') }}
                        </button>
                    </div>
                </x-card>

                <x-card :title="__('Account')">
                    <nav class="tw-space-y-1" aria-label="{{ __('Account links') }}">
                        <a href="{{ url('my-tickets') }}"
                            class="tw-flex tw-items-center tw-gap-2.5 tw-rounded-stub tw-px-3 tw-py-2 tw-text-sm tw-font-semibold tw-text-neutral-600 tw-transition-colors hover:tw-bg-neutral-100 hover:tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-neutral-300 dark:hover:tw-bg-neutral-800 dark:hover:tw-text-neutral-50">
                            <x-lucide-ticket class="tw-h-4 tw-w-4" />{{ __('My Tickets') }}
                        </a>
                        <a href="{{ url('update_profile') }}"
                            class="tw-flex tw-items-center tw-gap-2.5 tw-rounded-stub tw-px-3 tw-py-2 tw-text-sm tw-font-semibold tw-text-neutral-600 tw-transition-colors hover:tw-bg-neutral-100 hover:tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-neutral-300 dark:hover:tw-bg-neutral-800 dark:hover:tw-text-neutral-50">
                            <x-lucide-user-round class="tw-h-4 tw-w-4" />{{ __('Update Profile') }}
                        </a>
                        <a href="{{ url('change-password') }}"
                            class="tw-flex tw-items-center tw-gap-2.5 tw-rounded-stub tw-px-3 tw-py-2 tw-text-sm tw-font-semibold tw-text-neutral-600 tw-transition-colors hover:tw-bg-neutral-100 hover:tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-neutral-300 dark:hover:tw-bg-neutral-800 dark:hover:tw-text-neutral-50">
                            <x-lucide-key-round class="tw-h-4 tw-w-4" />{{ __('Change Password') }}
                        </a>
                        <form method="POST" action="{{ url('/user/logout') }}">
                            @csrf
                            <button type="submit"
                                class="tw-flex tw-w-full tw-items-center tw-gap-2.5 tw-rounded-stub tw-px-3 tw-py-2 tw-text-left tw-text-sm tw-font-semibold tw-text-neutral-600 tw-transition-colors hover:tw-bg-neutral-100 hover:tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-neutral-300 dark:hover:tw-bg-neutral-800 dark:hover:tw-text-neutral-50">
                                <x-lucide-log-out class="tw-h-4 tw-w-4" />{{ __('Log out') }}
                            </button>
                        </form>
                    </nav>
                </x-card>
            </aside>

            <x-card :padded="false" class="tw-min-w-0">
                <div class="tw-px-5 tw-pt-5">
                    <h2 class="tw-text-xl tw-font-extrabold tw-text-ink-950 dark:tw-text-neutral-50">{{ __('Your collection') }}</h2>
                    <p class="tw-mt-1 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                        {{ __('Events, articles and organisers you have saved.') }}
                    </p>
                </div>

                <div class="tw-px-5 tw-pb-5">
                    <div x-data="{ tab: 'events' }" class="ds-tabs">
                        <div role="tablist" aria-label="{{ __('Saved items') }}"
                            class="tw-flex tw-min-w-0 tw-gap-1 tw-overflow-x-auto tw-border-b tw-border-neutral-200 dark:tw-border-neutral-800">
                            <button type="button" id="saved-event-tab" role="tab" aria-controls="saved-event"
                                @click="tab = 'events'"
                                x-bind:aria-selected="tab === 'events' ? 'true' : 'false'"
                                x-bind:tabindex="tab === 'events' ? 0 : -1"
                                x-bind:class="tab === 'events'
                                    ? 'tw-border-accent-600 tw-text-ink-900 dark:tw-text-neutral-50'
                                    : 'tw-border-transparent tw-text-neutral-500 hover:tw-text-ink-800 dark:hover:tw-text-neutral-200'"
                                class="tw-whitespace-nowrap tw-border-b-2 tw-px-3 tw-py-2.5 tw-text-sm tw-font-semibold tw-transition focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                                {{ __('saved Events') }} <span class="tw-ml-1 tw-text-xs">{{ count($savedEvents) }}</span>
                            </button>
                            <button type="button" id="saved-blog-tab" role="tab" aria-controls="saved-blog"
                                @click="tab = 'blogs'"
                                x-bind:aria-selected="tab === 'blogs' ? 'true' : 'false'"
                                x-bind:tabindex="tab === 'blogs' ? 0 : -1"
                                x-bind:class="tab === 'blogs'
                                    ? 'tw-border-accent-600 tw-text-ink-900 dark:tw-text-neutral-50'
                                    : 'tw-border-transparent tw-text-neutral-500 hover:tw-text-ink-800 dark:hover:tw-text-neutral-200'"
                                class="tw-whitespace-nowrap tw-border-b-2 tw-px-3 tw-py-2.5 tw-text-sm tw-font-semibold tw-transition focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                                {{ __('Saved Blogs') }} <span class="tw-ml-1 tw-text-xs">{{ count($savedBlogs) }}</span>
                            </button>
                            <button type="button" id="following-tab" role="tab" aria-controls="following"
                                @click="tab = 'following'"
                                x-bind:aria-selected="tab === 'following' ? 'true' : 'false'"
                                x-bind:tabindex="tab === 'following' ? 0 : -1"
                                x-bind:class="tab === 'following'
                                    ? 'tw-border-accent-600 tw-text-ink-900 dark:tw-text-neutral-50'
                                    : 'tw-border-transparent tw-text-neutral-500 hover:tw-text-ink-800 dark:hover:tw-text-neutral-200'"
                                class="tw-whitespace-nowrap tw-border-b-2 tw-px-3 tw-py-2.5 tw-text-sm tw-font-semibold tw-transition focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                                {{ __('Following') }} <span class="tw-ml-1 tw-text-xs">{{ count($following) }}</span>
                            </button>
                        </div>

                        <div class="tw-pt-5">
                            <div id="saved-event" role="tabpanel" aria-labelledby="saved-event-tab" x-show="tab === 'events'">
                                @if ($savedEvents->isEmpty())
                                    <x-empty-state title="{{ __('No Saved Events found') }}"
                                        description="{{ __('Save an event to see it here.') }}">
                                        <x-slot:icon><x-lucide-calendar-days class="tw-h-6 tw-w-6" /></x-slot:icon>
                                        <x-slot:action>
                                            <a href="{{ url('/all-events') }}"
                                                class="tw-inline-flex tw-h-9 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 hover:tw-bg-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600">
                                                {{ __('Browse events') }}
                                            </a>
                                        </x-slot:action>
                                    </x-empty-state>
                                @else
                                    <div class="tw-grid tw-min-w-0 tw-gap-5 tw-grid-cols-1 sm:tw-grid-cols-2 xl:tw-grid-cols-3">
                                        @foreach ($savedEvents as $item)
                                            <article class="tw-group tw-min-w-0 tw-overflow-hidden tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-shadow-card tw-transition-shadow hover:tw-shadow-card-hover dark:tw-border-neutral-800 dark:tw-bg-ink-900">
                                                <div class="tw-relative tw-aspect-video tw-overflow-hidden">
                                                    <a href="{{ $eventUrl($item) }}" tabindex="-1" aria-hidden="true">
                                                        <img src="{{ $imageUrl($item->image) }}" alt="" width="600" height="340" loading="lazy"
                                                            class="tw-h-full tw-w-full tw-object-cover tw-transition-transform tw-duration-300 group-hover:tw-scale-105">
                                                    </a>
                                                    <button type="button" onclick="addFavorite({{ $item->id }},'event')"
                                                        data-favorite-id="{{ $item->id }}" data-favorite-type="event"
                                                        aria-pressed="{{ in_array((string) $item->id, $favoriteEvents, true) ? 'true' : 'false' }}"
                                                        aria-label="{{ __('Remove from saved events') }}"
                                                        class="tw-absolute tw-right-3 tw-top-3 tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-pill tw-border tw-border-neutral-200 tw-bg-neutral-0/95 tw-text-primary-600 tw-shadow-card focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600 dark:tw-border-neutral-700 dark:tw-bg-ink-900/95">
                                                        <x-lucide-heart class="tw-h-5 tw-w-5 tw-fill-current" />
                                                    </button>
                                                </div>
                                                <div class="tw-p-4">
                                                    <x-badge tone="primary">{{ $item->category?->name ?? __('Event') }}</x-badge>
                                                    <h3 class="tw-mt-2 tw-text-base tw-font-bold tw-leading-snug tw-text-ink-950 dark:tw-text-neutral-50">
                                                        <a href="{{ $eventUrl($item) }}" class="hover:tw-text-primary-700 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:hover:tw-text-primary-300">{{ $item->name }}</a>
                                                    </h3>
                                                    <div class="tw-mt-3 tw-space-y-1.5 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                                        <p class="tw-flex tw-items-center tw-gap-1.5"><x-lucide-calendar-days class="tw-h-3.5 tw-w-3.5" />{{ $item->start_time?->format('l, F j, Y') }}</p>
                                                        <p class="tw-flex tw-items-center tw-gap-1.5"><x-lucide-ticket class="tw-h-3.5 tw-w-3.5" />{{ max(0, (int) $item->available_ticket).' '.__('tickets left') }}</p>
                                                    </div>
                                                    <a href="{{ $eventUrl($item) }}"
                                                        class="tw-mt-4 tw-inline-flex tw-items-center tw-gap-1 tw-text-xs tw-font-bold tw-text-primary-700 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-primary-300">
                                                        {{ __('View event') }}<x-lucide-external-link class="tw-h-3.5 tw-w-3.5" />
                                                    </a>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div id="saved-blog" role="tabpanel" aria-labelledby="saved-blog-tab" x-show="tab === 'blogs'" x-cloak>
                                @if ($savedBlogs->isEmpty())
                                    <x-empty-state title="{{ __('No Saved Blog found') }}"
                                        description="{{ __('Save an article to read it later.') }}">
                                        <x-slot:icon><x-lucide-book-open class="tw-h-6 tw-w-6" /></x-slot:icon>
                                        <x-slot:action>
                                            <a href="{{ url('/all-blogs') }}"
                                                class="tw-inline-flex tw-h-9 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 hover:tw-bg-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600">
                                                {{ __('Browse articles') }}
                                            </a>
                                        </x-slot:action>
                                    </x-empty-state>
                                @else
                                    <div class="tw-grid tw-min-w-0 tw-gap-5 tw-grid-cols-1 sm:tw-grid-cols-2 xl:tw-grid-cols-3">
                                        @foreach ($savedBlogs as $item)
                                            <article class="tw-group tw-min-w-0 tw-overflow-hidden tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-shadow-card tw-transition-shadow hover:tw-shadow-card-hover dark:tw-border-neutral-800 dark:tw-bg-ink-900">
                                                <div class="tw-relative tw-aspect-video tw-overflow-hidden">
                                                    <a href="{{ $blogUrl($item) }}" tabindex="-1" aria-hidden="true">
                                                        <img src="{{ $imageUrl($item->image) }}" alt="" width="600" height="340" loading="lazy"
                                                            class="tw-h-full tw-w-full tw-object-cover tw-transition-transform tw-duration-300 group-hover:tw-scale-105">
                                                    </a>
                                                    <button type="button" onclick="addFavorite({{ $item->id }},'blog')"
                                                        data-favorite-id="{{ $item->id }}" data-favorite-type="blog"
                                                        aria-pressed="{{ in_array((string) $item->id, $favoriteBlogs, true) ? 'true' : 'false' }}"
                                                        aria-label="{{ __('Remove from saved blogs') }}"
                                                        class="tw-absolute tw-right-3 tw-top-3 tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-pill tw-border tw-border-neutral-200 tw-bg-neutral-0/95 tw-text-primary-600 tw-shadow-card focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600 dark:tw-border-neutral-700 dark:tw-bg-ink-900/95">
                                                        <x-lucide-heart class="tw-h-5 tw-w-5 tw-fill-current" />
                                                    </button>
                                                </div>
                                                <div class="tw-p-4">
                                                    <x-badge tone="accent">{{ $item->category?->name ?? __('Article') }}</x-badge>
                                                    <h3 class="tw-mt-2 tw-text-base tw-font-bold tw-leading-snug tw-text-ink-950 dark:tw-text-neutral-50">
                                                        <a href="{{ $blogUrl($item) }}" title="{{ $item->title }}"
                                                            class="hover:tw-text-primary-700 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:hover:tw-text-primary-300">{{ $item->title }}</a>
                                                    </h3>
                                                    <p class="tw-mt-3 tw-flex tw-items-center tw-gap-1.5 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                                        <x-lucide-calendar-days class="tw-h-3.5 tw-w-3.5" />{{ $item->created_at?->format('d M Y') }}
                                                    </p>
                                                    <a href="{{ $blogUrl($item) }}"
                                                        class="tw-mt-4 tw-inline-flex tw-items-center tw-gap-1 tw-text-xs tw-font-bold tw-text-primary-700 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-primary-300">
                                                        {{ __('Read article') }}<x-lucide-external-link class="tw-h-3.5 tw-w-3.5" />
                                                    </a>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div id="following" role="tabpanel" aria-labelledby="following-tab" x-show="tab === 'following'" x-cloak>
                                @if ($following->isEmpty())
                                    <x-empty-state title="{{ __('You are not following anyone yet') }}"
                                        description="{{ __('Follow an organiser to keep up with their events.') }}">
                                        <x-slot:icon><x-lucide-users class="tw-h-6 tw-w-6" /></x-slot:icon>
                                    </x-empty-state>
                                @else
                                    <div class="tw-space-y-3">
                                        @foreach ($following as $item)
                                            <div class="tw-flex tw-items-center tw-gap-4 tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-p-4 dark:tw-border-neutral-800 dark:tw-bg-ink-900">
                                                <img src="{{ $imageUrl($item->image) }}" alt="" width="64" height="64" loading="lazy"
                                                    class="tw-h-16 tw-w-16 tw-shrink-0 tw-rounded-pill tw-object-cover">
                                                <div class="tw-min-w-0 tw-flex-1">
                                                    <p class="tw-truncate tw-text-base tw-font-bold tw-text-ink-950 dark:tw-text-neutral-50">
                                                        {{ $item->first_name.' '.$item->last_name }}
                                                    </p>
                                                    <p class="tw-truncate tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">{{ $item->email }}</p>
                                                </div>
                                                <button type="button" onclick="follow({{ $item->id }})"
                                                    data-follow-id="{{ $item->id }}" aria-pressed="true"
                                                    class="tw-shrink-0 tw-inline-flex tw-h-9 tw-items-center tw-rounded-pill tw-border tw-border-neutral-200 tw-px-4 tw-text-xs tw-font-bold tw-text-ink-800 tw-transition-colors hover:tw-border-danger-300 hover:tw-text-danger-600 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-text-neutral-100">
                                                    {{ __('Unfollow') }}
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection

@push('scripts')
    @vite('resources/js/user-area.js')
@endpush
