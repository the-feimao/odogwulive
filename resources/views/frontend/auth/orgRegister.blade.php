@extends('layouts.public')

@section('title', __('Organizer sign up').' — '.config('brand.name'))

@php
    $countries = \App\Models\Currency::get();
@endphp

@section('content')
    <section class="tw-relative tw-overflow-hidden tw-bg-ink-950">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-br tw-from-ink-950 tw-via-ink-900 tw-to-accent-900/60"></div>
        <div class="tw-relative tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
            <nav aria-label="{{ __('Breadcrumb') }}" class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-text-neutral-400">
                <a href="{{ url('/') }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ __('Home') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <span class="tw-text-neutral-200">{{ __('Organizer sign up') }}</span>
            </nav>

            <div class="tw-mt-6 tw-max-w-2xl">
                <x-badge tone="accent"><x-slot:icon><x-lucide-users class="tw-h-3.5 tw-w-3.5" /></x-slot:icon>{{ __('Become a partner') }}</x-badge>
                <h1 class="tw-mt-3 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-neutral-50 md:tw-text-4xl">
                    {{ __('Sign up as an organizer') }}
                </h1>
                <p class="tw-mt-2 tw-text-sm tw-leading-relaxed tw-text-neutral-300">
                    {{ __('Create events, sell tickets and manage your audience from one place.') }}
                </p>
            </div>
        </div>
    </section>

    <div class="tw-mx-auto tw-max-w-4xl tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
        <x-card :title="__('Organizer details')" :subtitle="__('Tell us about you and your organization.')">
            <form action="{{ url('user/org-register') }}" method="post">
                @csrf
                <input type="hidden" name="url" value="{{ url()->previous() }}">

                <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                    <x-input name="first_name" :label="__('First Name')" :value="old('first_name')" required autocomplete="given-name"
                        placeholder="{{ __('First Name') }}" />
                    <x-input name="last_name" :label="__('Last Name')" :value="old('last_name')" required autocomplete="family-name"
                        placeholder="{{ __('Last Name') }}" />
                    <x-input name="email" :label="__('Email')" type="email" :value="old('email')" required autocomplete="email"
                        placeholder="{{ __('Your Email') }}" />
                    <x-input name="phone" :label="__('Phone')" type="tel" :value="old('phone')" required autocomplete="tel"
                        placeholder="{{ __('Contact') }}" />
                    <x-input name="password" :label="__('Password')" type="password" required autocomplete="new-password"
                        placeholder="{{ __('Your password') }}" />
                    <x-input name="confirm_password" :label="__('Confirm Password')" type="password" required autocomplete="new-password"
                        placeholder="{{ __('Confirm password') }}" />
                    <x-input name="name" :label="__('Organization Name')" :value="old('name')" required autocomplete="organization"
                        placeholder="{{ __('Organization Name') }}" />
                    <x-select name="country" :label="__('Country')" required>
                        <option value="">{{ __('Choose Country') }}</option>
                        @foreach ($countries as $item)
                            <option value="{{ $item->country }}" @selected(old('country') === $item->country)>{{ $item->country }}</option>
                        @endforeach
                    </x-select>
                </div>

                <div class="tw-mt-7 tw-flex tw-flex-wrap tw-items-center tw-gap-3">
                    <x-button type="submit" data-qa="login-button">{{ __('Sign up') }}</x-button>
                    <a href="{{ url('login') }}"
                        class="tw-inline-flex tw-h-10 tw-items-center tw-gap-1.5 tw-rounded-pill tw-px-4 tw-text-sm tw-font-bold tw-text-neutral-600 hover:tw-text-ink-900 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-neutral-300 dark:hover:tw-text-neutral-50">
                        <x-lucide-arrow-left class="tw-h-4 tw-w-4" />{{ __('Sign in now') }}
                    </a>
                </div>
            </form>
        </x-card>
    </div>
@endsection
