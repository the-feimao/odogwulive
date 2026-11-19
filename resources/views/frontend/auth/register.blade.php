@extends('layouts.public')

@section('title', __('Sign up').' — '.config('brand.name'))

@php
    $setting = \App\Models\Setting::find(1);
@endphp

@section('content')
    <section class="tw-relative tw-overflow-hidden tw-bg-ink-950">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-br tw-from-ink-950 tw-via-ink-900 tw-to-accent-900/60"></div>
        <div class="tw-relative tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
            <nav aria-label="{{ __('Breadcrumb') }}" class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-text-neutral-400">
                <a href="{{ url('/') }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ __('Home') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <span class="tw-text-neutral-200">{{ __('Sign up') }}</span>
            </nav>

            <div class="tw-mt-6 tw-max-w-2xl">
                <x-badge tone="accent">{{ __('Join the audience') }}</x-badge>
                <h1 class="tw-mt-3 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-neutral-50 md:tw-text-4xl">
                    {{ __('Sign up to book') }}
                </h1>
                <p class="tw-mt-2 tw-text-sm tw-leading-relaxed tw-text-neutral-300">
                    {{ __('Create an account to book tickets, save events and keep your orders in one place.') }}
                </p>
            </div>
        </div>
    </section>

    <div class="tw-mx-auto tw-max-w-5xl tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
        <div class="tw-grid tw-gap-8 lg:tw-grid-cols-[minmax(0,1fr)_320px]">
            <x-card :title="__('Create your account')" :subtitle="__('All fields are required.')">
                <form action="{{ url('user/register') }}" method="post">
                    @csrf
                    <input type="hidden" name="url" value="{{ url()->previous() }}">

                    <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                        <x-input name="name" :label="__('First Name')" :value="old('name')" required autocomplete="given-name"
                            placeholder="{{ __('First Name') }}" />
                        <x-input name="last_name" :label="__('Last Name')" :value="old('last_name')" required autocomplete="family-name"
                            placeholder="{{ __('Last Name') }}" />
                        <x-input name="email" :label="__('Email')" type="email" :value="old('email')" required autocomplete="email"
                            placeholder="{{ __('Your Email') }}" />
                        <x-input name="phone" :label="__('Phone')" type="tel" :value="old('phone')" required autocomplete="tel"
                            placeholder="{{ __('Contact') }}" />
                        <div class="md:tw-col-span-2">
                            <x-input name="password" :label="__('Password')" type="password" required autocomplete="new-password"
                                placeholder="{{ __('Your password') }}" help="{{ __('Use at least six characters.') }}" />
                        </div>
                    </div>

                    <x-button type="submit" data-qa="login-button" class="tw-mt-6" full>
                        {{ __('Sign up') }}
                    </x-button>

                    <p class="tw-mt-5 tw-text-center tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                        {{ __('Already have a user account?') }}
                        <a href="{{ url('user/login') }}"
                            class="tw-font-bold tw-text-primary-700 hover:tw-text-primary-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-primary-300">{{ __('Sign in now') }}</a>
                    </p>
                </form>
            </x-card>

            <x-card :title="__('Become a partner')" :subtitle="__('Host your own events.')">
                <p class="tw-text-sm tw-leading-relaxed tw-text-neutral-600 dark:tw-text-neutral-300">
                    {{ __('Create your event with') }} {{ $setting->app_name ?? config('brand.name') }} {{ __('by signing up as an organizer.') }}
                </p>
                <a href="{{ url('user/org-register') }}" target="_blank" rel="noopener"
                    class="tw-mt-5 tw-inline-flex tw-h-10 tw-w-full tw-items-center tw-justify-center tw-gap-2 tw-rounded-pill tw-border tw-border-primary-300 tw-px-4 tw-text-sm tw-font-bold tw-text-primary-700 tw-transition-colors hover:tw-bg-primary-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-primary-500/40 dark:tw-text-primary-200 dark:hover:tw-bg-primary-500/10">
                    <x-lucide-users class="tw-h-4 tw-w-4" />
                    {{ __('Sign up as an organizer') }}
                </a>
            </x-card>
        </div>
    </div>
@endsection
