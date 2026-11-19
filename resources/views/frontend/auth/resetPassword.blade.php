@extends('layouts.public')

@section('title', __('Reset password').' — '.config('brand.name'))

@section('content')
    <section class="tw-relative tw-overflow-hidden tw-bg-ink-950">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-br tw-from-ink-950 tw-via-ink-900 tw-to-primary-900/60"></div>
        <div class="tw-relative tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
            <nav aria-label="{{ __('Breadcrumb') }}" class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-text-neutral-400">
                <a href="{{ url('/') }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ __('Home') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <a href="{{ url('user/login') }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ __('Log in') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <span class="tw-text-neutral-200">{{ __('Reset password') }}</span>
            </nav>

            <div class="tw-mt-6 tw-max-w-2xl">
                <x-badge tone="primary"><x-slot:icon><x-lucide-key-round class="tw-h-3.5 tw-w-3.5" /></x-slot:icon>{{ __('Account recovery') }}</x-badge>
                <h1 class="tw-mt-3 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-neutral-50 md:tw-text-4xl">
                    {{ __('Forgot password?') }}
                </h1>
                <p class="tw-mt-2 tw-text-sm tw-leading-relaxed tw-text-neutral-300">
                    {{ __('Enter your registered email and we will send you a new password.') }}
                </p>
            </div>
        </div>
    </section>

    <div class="tw-mx-auto tw-max-w-xl tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
        <x-card :title="__('Reset your password')" :subtitle="__('Check your inbox after submitting.')">
            @if (session('success_msg'))
                <x-alert tone="success" class="tw-mb-5" :title="__('Check your email')">
                    <x-slot:icon><x-lucide-circle-check class="tw-h-5 tw-w-5" /></x-slot:icon>
                    {{ session('success_msg') }}
                </x-alert>
            @endif

            @if (session('error_msg'))
                <x-alert tone="danger" class="tw-mb-5" :title="__('Could not reset password')">
                    <x-slot:icon><x-lucide-circle-alert class="tw-h-5 tw-w-5" /></x-slot:icon>
                    {{ session('error_msg') }}
                </x-alert>
            @endif

            <form action="{{ url('user/resetPassword') }}" method="post" data-qa="form-login" name="login">
                @csrf
                <input type="hidden" name="url" value="{{ url()->previous() }}">

                <x-input name="email" :label="__('Email address')" type="email" required autocomplete="email"
                    placeholder="{{ __('Enter registered email') }}" />

                <x-button type="submit" data-qa="login-button" class="tw-mt-6" full>
                    {{ __('Reset password') }}
                </x-button>

                <p class="tw-mt-5 tw-text-center tw-text-sm">
                    <a href="{{ url('user/login') }}"
                        class="tw-font-bold tw-text-primary-700 hover:tw-text-primary-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-primary-300">{{ __('Back to login') }}</a>
                </p>
            </form>
        </x-card>
    </div>
@endsection
