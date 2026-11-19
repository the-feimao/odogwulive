@extends('layouts.public')

@section('title', __('Change Password').' — '.config('brand.name'))

@section('content')
    <section class="tw-relative tw-overflow-hidden tw-bg-ink-950">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-br tw-from-ink-950 tw-via-ink-900 tw-to-accent-900/60"></div>
        <div class="tw-relative tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
            <nav aria-label="{{ __('Breadcrumb') }}" class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-text-neutral-400">
                <a href="{{ url('/') }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ __('Home') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <a href="{{ url('user/profile') }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ __('My profile') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <span class="tw-text-neutral-200">{{ __('Change Password') }}</span>
            </nav>
            <h1 class="tw-mt-6 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-neutral-50 md:tw-text-4xl">
                {{ __('Change Password') }}
            </h1>
            <p class="tw-mt-2 tw-max-w-2xl tw-text-sm tw-text-neutral-300">
                {{ __('Use at least six characters and choose a password you have not used before.') }}
            </p>
        </div>
    </section>

    <div class="tw-mx-auto tw-max-w-2xl tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
        @if (session('error_msg'))
            <x-alert tone="danger" class="tw-mb-5" :title="__('Password not changed')">
                <x-slot:icon><x-lucide-circle-alert class="tw-h-5 tw-w-5" /></x-slot:icon>
                {{ session('error_msg') }}
            </x-alert>
        @endif

        <x-card :title="__('Account security')" :subtitle="__('Enter your current password, then choose a new one.')">
            <form action="{{ url('change-password') }}" method="post" data-qa="form-login" name="login">
                @csrf
                <div class="tw-space-y-5">
                    <x-input name="old_password" :label="__('Current Password')" type="password" required autocomplete="current-password" placeholder="{{ __('Your Current Password') }}" />
                    <x-input name="password" :label="__('New Password')" type="password" required autocomplete="new-password" placeholder="{{ __('New password') }}" />
                    <x-input name="password_confirmation" :label="__('Confirm Password')" type="password" required autocomplete="new-password" placeholder="{{ __('Confirm password') }}" />
                </div>

                <div class="tw-mt-7 tw-flex tw-flex-wrap tw-items-center tw-gap-3">
                    <x-button type="submit" data-qa="login-button">{{ __('Change') }}</x-button>
                    <a href="{{ url('user/profile') }}"
                        class="tw-inline-flex tw-h-10 tw-items-center tw-gap-1.5 tw-rounded-pill tw-px-4 tw-text-sm tw-font-bold tw-text-neutral-600 hover:tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-neutral-300 dark:hover:tw-text-neutral-50">
                        <x-lucide-arrow-left class="tw-h-4 tw-w-4" />{{ __('Back to profile') }}
                    </a>
                </div>
            </form>
        </x-card>
    </div>
@endsection
