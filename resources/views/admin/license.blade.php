@extends('layouts.admin-license')

@section('admin-license-content')
    <x-admin-breadcrumb :title="__('License Setting')" />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('License Setting') }}
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('Activate or review the license attached to this installation.') }}
            </p>
        </div>

        @if ($setting->license_status == 1)
            <x-badge tone="success">
                <x-slot:icon><x-lucide-shield-check class="tw-h-3.5 tw-w-3.5" /></x-slot:icon>
                {{ __('Active') }}
            </x-badge>
        @else
            <x-badge tone="warning">
                <x-slot:icon><x-lucide-circle-alert class="tw-h-3.5 tw-w-3.5" /></x-slot:icon>
                {{ __('Inactive') }}
            </x-badge>
        @endif
    </div>

    @if ($errors->any())
        <x-alert tone="danger" title="{{ __('Please check the form') }}" dismissible>
            <x-slot:icon><x-lucide-circle-alert class="tw-h-5 tw-w-5" /></x-slot:icon>
            <ul class="tw-list-disc tw-space-y-1 tw-pl-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </x-alert>
    @endif

    @if (session('status'))
        <x-alert tone="danger" dismissible>
            <x-slot:icon><x-lucide-circle-alert class="tw-h-5 tw-w-5" /></x-slot:icon>
            {{ session('status') }}
        </x-alert>
    @endif

    <x-card :title="__('License details')" :subtitle="__('Enter the key and client name supplied for this installation.')">
        <form method="post" action="{{ url('save-license-setting') }}" class="tw-grid tw-gap-5 sm:tw-grid-cols-2">
            @csrf

            <div class="ds-field tw-flex tw-flex-col tw-gap-1.5">
                <label for="license_key" class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
                    {{ __('License Key') }}
                </label>
                <input id="license_key" type="text" name="license_key"
                    placeholder="{{ __('License Key') }}" value="{{ $setting->license_key }}"
                    @if ($setting->license_status == 1) disabled @endif
                    @if ($errors->has('license_key')) aria-invalid="true" aria-describedby="license_key-error" @endif
                    class="tw-h-10 tw-w-full tw-rounded-card tw-border tw-bg-neutral-0 tw-px-3 tw-text-sm tw-text-ink-900 tw-placeholder-neutral-400 tw-transition focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-accent-600/20 disabled:tw-cursor-not-allowed disabled:tw-opacity-60 dark:tw-bg-ink-900 dark:tw-text-neutral-50 dark:tw-placeholder-neutral-500 {{ $errors->has('license_key') ? 'tw-border-danger-600' : 'tw-border-neutral-200 hover:tw-border-neutral-300 focus:tw-border-accent-600 dark:tw-border-neutral-700' }}">
                @error('license_key')
                    <p id="license_key-error" class="tw-text-xs tw-font-medium tw-text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="ds-field tw-flex tw-flex-col tw-gap-1.5">
                <label for="license_name" class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
                    {{ __('Client Name') }}
                </label>
                <input id="license_name" type="text" name="license_name"
                    placeholder="{{ __('Client Name') }}" value="{{ $setting->license_name }}"
                    @if ($setting->license_status == 1) disabled @endif
                    @if ($errors->has('license_name')) aria-invalid="true" aria-describedby="license_name-error" @endif
                    class="tw-h-10 tw-w-full tw-rounded-card tw-border tw-bg-neutral-0 tw-px-3 tw-text-sm tw-text-ink-900 tw-placeholder-neutral-400 tw-transition focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-accent-600/20 disabled:tw-cursor-not-allowed disabled:tw-opacity-60 dark:tw-bg-ink-900 dark:tw-text-neutral-50 dark:tw-placeholder-neutral-500 {{ $errors->has('license_name') ? 'tw-border-danger-600' : 'tw-border-neutral-200 hover:tw-border-neutral-300 focus:tw-border-accent-600 dark:tw-border-neutral-700' }}">
                @error('license_name')
                    <p id="license_name-error" class="tw-text-xs tw-font-medium tw-text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="sm:tw-col-span-2">
                <x-button type="submit" :disabled="$setting->license_status == 1">
                    <x-slot:icon><x-lucide-key-round class="tw-h-4 tw-w-4" /></x-slot:icon>
                    {{ __('Submit') }}
                </x-button>
            </div>
        </form>
    </x-card>
@endsection
