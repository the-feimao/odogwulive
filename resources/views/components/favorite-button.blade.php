@props([
    'id',
    'type' => 'event',
])

@php
    $user = Auth::guard('appuser')->user();
    $favoriteField = $type === 'blog' ? 'favorite_blog' : 'favorite';
    $favorites = $user
        ? array_map('strval', array_filter(explode(',', (string) ($user->{$favoriteField} ?? ''))))
        : [];
    $active = in_array((string) $id, $favorites, true);
    $endpoint = url('/add-favorite/'.$id.'/'.$type);
    $noun = $type === 'blog' ? __('blog') : __('event');
@endphp

@if ($user)
    <button type="button" x-data="{ busy: false, active: {{ $active ? 'true' : 'false' }} }" :disabled="busy"
        :aria-pressed="active ? 'true' : 'false'"
        aria-label="{{ $active ? __('Remove from saved').' '.$noun : __('Save').' '.$noun }}"
        @click="busy = true; fetch('{{ $endpoint }}', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' }).then(function (response) { return response.json(); }).then(function (data) { if (data.success) { window.location.reload(); } else { busy = false; } }).catch(function () { busy = false; })"
        class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-pill tw-border tw-border-neutral-200 tw-bg-neutral-0/95 tw-text-neutral-600 tw-shadow-card tw-transition-colors hover:tw-border-primary-400 hover:tw-text-primary-700 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600 disabled:tw-opacity-60 dark:tw-border-neutral-700 dark:tw-bg-ink-900/95 dark:tw-text-neutral-300">
        <x-lucide-heart class="{{ $active ? 'tw-h-5 tw-w-5 tw-fill-current tw-text-primary-600' : 'tw-h-5 tw-w-5' }}" />
    </button>
@else
    <a href="{{ url('user/login') }}" aria-label="{{ __('Log in to save').' '.$noun }}"
        class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-pill tw-border tw-border-neutral-200 tw-bg-neutral-0/95 tw-text-neutral-600 tw-shadow-card tw-transition-colors hover:tw-border-primary-400 hover:tw-text-primary-700 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600 dark:tw-border-neutral-700 dark:tw-bg-ink-900/95 dark:tw-text-neutral-300">
        <x-lucide-heart class="tw-h-5 tw-w-5" />
    </a>
@endif
