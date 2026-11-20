@extends('layouts.admin-auth')

@section('admin-auth-content')
    @php
        $loginSetting = \App\Models\Setting::find(1);
    @endphp

    <section class="tw-grid tw-min-h-screen tw-grid-cols-1 lg:tw-grid-cols-[minmax(360px,40%)_1fr]">
        <div class="tw-flex tw-items-center tw-justify-center tw-bg-neutral-0 tw-px-5 tw-py-10 sm:tw-px-10 dark:tw-bg-ink-950">
            <div class="tw-w-full tw-max-w-md">
                <img src="{{ url('images/upload/'.$loginSetting->logo) }}" alt="{{ $loginSetting->app_name }}"
                    width="80" class="tw-mb-8 tw-h-auto tw-w-20 tw-object-contain">

                <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                    {{ __('Welcome to ') }}<span class="tw-text-accent-600 dark:tw-text-accent-300">{{ $loginSetting->app_name }}</span>
                </h1>
                <p class="tw-mb-8 tw-mt-2 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                    {{ __('Sign in to manage events, orders and ticket scanning.') }}
                </p>

                @if (Session::has('error_msg'))
                    <x-alert tone="danger" class="tw-mb-5" dismissible>
                        <x-slot:icon><x-lucide-circle-alert class="tw-h-5 tw-w-5" /></x-slot:icon>
                        {{ Session::get('error_msg') }}
                    </x-alert>
                @endif

                <form method="POST" action="{{ url('admin/login') }}" class="needs-validation tw-space-y-5" novalidate="">
                    @csrf

                    <x-input name="email" type="email" label="{{ __('Email') }}" id="email" tabindex="1" required autofocus />

                    <x-input name="password" type="password" label="{{ __('Password') }}" id="password" tabindex="2" required />

                    <label for="remember-me" class="tw-flex tw-cursor-pointer tw-items-center tw-gap-2.5 tw-text-sm tw-font-medium tw-text-ink-800 dark:tw-text-neutral-100">
                        <input type="checkbox" name="remember" id="remember-me" tabindex="3"
                            class="tw-h-4 tw-w-4 tw-rounded-stub tw-border tw-border-neutral-300 tw-text-accent-600 focus:tw-ring-2 focus:tw-ring-accent-600/20 dark:tw-border-neutral-700 dark:tw-bg-ink-900">
                        {{ __('Remember Me') }}
                    </label>

                    <x-button type="submit" size="lg" full tabindex="4">
                        <x-slot:icon><x-lucide-lock class="tw-h-4 tw-w-4" /></x-slot:icon>
                        {{ __('Login') }}
                    </x-button>
                </form>
            </div>
        </div>

        <div class="tw-relative tw-hidden tw-items-end tw-bg-cover tw-bg-center tw-p-12 lg:tw-flex"
            data-background="{{ url('images/login-bg.jpg') }}"
            style="background-image: linear-gradient(180deg, rgba(13, 17, 32, 0.15) 0%, rgba(13, 17, 32, 0.82) 100%), url('{{ url('images/login-bg.jpg') }}');">
            <div class="tw-max-w-xl tw-text-white">
                <x-badge tone="primary" size="md">{{ __('Event management, simplified') }}</x-badge>
                <h2 class="tw-mt-5 tw-text-4xl tw-font-extrabold tw-leading-tight tw-tracking-tight">
                    {{ __('Welcome') }}
                </h2>
                <p class="tw-mt-3 tw-text-base tw-text-white/80">
                    {{ __('Create events, sell tickets and validate every guest at the gate.') }}
                </p>
            </div>
        </div>
    </section>
@endsection
