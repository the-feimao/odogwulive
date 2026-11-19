@extends('layouts.public')

@section('title', __('Update User Profile').' — '.config('brand.name'))

@section('content')
    <section class="tw-relative tw-overflow-hidden tw-bg-ink-950">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-br tw-from-ink-950 tw-via-ink-900 tw-to-primary-900/60"></div>
        <div class="tw-relative tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
            <nav aria-label="{{ __('Breadcrumb') }}" class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-text-neutral-400">
                <a href="{{ url('/') }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ __('Home') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <a href="{{ url('user/profile') }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ __('My profile') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <span class="tw-text-neutral-200">{{ __('Update Profile') }}</span>
            </nav>
            <h1 class="tw-mt-6 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-neutral-50 md:tw-text-4xl">
                {{ __('Update User Profile') }}
            </h1>
            <p class="tw-mt-2 tw-max-w-2xl tw-text-sm tw-text-neutral-300">
                {{ __('Keep your contact details current so your tickets and order updates reach you.') }}
            </p>
        </div>
    </section>

    <div class="tw-mx-auto tw-max-w-3xl tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
        <x-card :title="__('Personal details')" :subtitle="__('Your email address cannot be changed here.')">
            <form action="{{ url('update_user_profile') }}" method="post" data-qa="form-login" name="login">
                @csrf
                <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                    <x-input name="name" :label="__('First Name')" :value="$user->name" required autocomplete="off" />
                    <x-input name="last_name" :label="__('Last Name')" :value="$user->last_name" required autocomplete="off" />
                    <x-input name="email" :label="__('Email')" :value="$user->email" type="email" required readonly autocomplete="off" />
                    <x-input name="phone" :label="__('Phone')" :value="$user->phone" required autocomplete="off" placeholder="{{ __('Contact') }}" />

                    <div class="md:tw-col-span-2">
                        <x-select name="language" :label="__('Select Language')" required>
                            <option value="">{{ __('Select default Language') }}</option>
                            @foreach ($languages as $language)
                                <option value="{{ $language->name }}" @selected($language->name === $user->language)>{{ $language->name }}</option>
                            @endforeach
                        </x-select>
                    </div>
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
