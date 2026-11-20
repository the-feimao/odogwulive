@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Profile')" />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('Hi,') }} {{ Auth::user()->name }}!
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('Change information about yourself on this page.') }}
            </p>
        </div>
    </div>

    <x-flash-messages />

    <div class="tw-grid tw-gap-5 lg:tw-grid-cols-[minmax(0,340px)_minmax(0,1fr)]">
        <div class="tw-space-y-5">
            <x-card :padded="false">
                <div class="tw-p-5">
                    <div class="tw-flex tw-items-center tw-gap-4">
                        <img src="{{ url('images/upload/'.Auth::user()->image) }}" alt="{{ Auth::user()->name }}"
                            loading="lazy" class="tw-h-16 tw-w-16 tw-shrink-0 tw-rounded-pill tw-object-cover">
                        <div class="tw-min-w-0">
                            <p class="tw-truncate tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">
                                {{ Auth::user()->name }}
                            </p>
                            <p class="tw-truncate tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                                {{ Auth::user()->email }}
                            </p>
                        </div>
                    </div>

                    @if (Auth::user()->bio)
                        <p class="tw-mt-4 tw-text-sm tw-leading-relaxed tw-text-neutral-600 dark:tw-text-neutral-300">
                            {{ Auth::user()->bio }}
                        </p>
                    @endif
                </div>

                <div class="tw-grid tw-grid-cols-2 tw-border-t tw-border-neutral-100 dark:tw-border-neutral-800">
                    @if (Auth::user()->hasRole('admin'))
                        <div class="tw-border-r tw-border-neutral-100 tw-p-4 dark:tw-border-neutral-800">
                            <p class="tw-text-2xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">
                                {{ __('Organizations') }}
                            </p>
                            <p class="tw-mt-1 tw-text-xl tw-font-extrabold tw-text-ink-900 dark:tw-text-neutral-50">
                                {{ App\Models\User::role('organization')->count() }}
                            </p>
                        </div>
                    @endif

                    @if (Auth::user()->hasRole('organization'))
                        <div class="tw-border-r tw-border-neutral-100 tw-p-4 dark:tw-border-neutral-800">
                            <p class="tw-text-2xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">
                                {{ __('Followers') }}
                            </p>
                            <p class="tw-mt-1 tw-text-xl tw-font-extrabold tw-text-ink-900 dark:tw-text-neutral-50">
                                {{ count(Auth::user()->followers) }}
                            </p>
                        </div>
                    @endif

                    <div class="tw-p-4">
                        <p class="tw-text-2xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">
                            {{ __('Events') }}
                        </p>
                        <p class="tw-mt-1 tw-text-xl tw-font-extrabold tw-text-ink-900 dark:tw-text-neutral-50">
                            @if (Auth::user()->hasRole('admin'))
                                {{ App\Models\Event::count() }}
                            @elseif (Auth::user()->hasRole('organization'))
                                {{ App\Models\Event::where('user_id', Auth::user()->id)->count() }}
                            @endif
                        </p>
                    </div>
                </div>
            </x-card>

            @if (Auth::user()->hasRole('organization'))
                @php
                    $followers = \App\Models\AppUser::whereIn('id', Auth::user()->followers)->get();
                @endphp

                <x-card :title="__('Followers ('.count($followers).')')">
                    @if ($followers->isEmpty())
                        <x-empty-state :title="__('No followers yet')" :description="__('People who follow your events will appear here.')" />
                    @else
                        <ul class="tw-space-y-3">
                            @foreach ($followers as $item)
                                <li class="tw-flex tw-items-center tw-gap-3">
                                    <img src="{{ url('images/upload/'.$item->image) }}" alt="{{ $item->name }}"
                                        loading="lazy" class="tw-h-10 tw-w-10 tw-rounded-pill tw-object-cover">
                                    <div class="tw-min-w-0">
                                        <p class="tw-truncate tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">
                                            {{ $item->name.' '.$item->last_name }}
                                        </p>
                                        <p class="tw-truncate tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                            {{ $item->email }}
                                        </p>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </x-card>
            @endif
        </div>

        <div class="tw-space-y-5">
            <x-card :title="__('Edit Profile')" :subtitle="__('Keep your public organizer details up to date.')">
                <form method="post" class="tw-grid tw-gap-5" action="{{ url('edit-profile') }}">
                    @csrf

                    <p class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-text-neutral-400">
                        {{ __('Admin Information') }}
                    </p>

                    <x-input name="name" :label="__('Name')" :value="Auth::user()->name" required />

                    <x-input name="email" :label="__('Email')" type="email" :value="Auth::user()->email" required readonly />

                    <x-select name="language" :label="__('Language')" required>
                        @foreach ($languages as $item)
                            <option value="{{ $item->name }}" {{ $item->name == old('language', Auth::user()->language) ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </x-select>

                    <x-textarea name="bio" :label="__('Bio')" :value="Auth::user()->bio" :placeholder="__('About you')" />

                    <div>
                        <x-button type="submit">{{ __('Save Changes') }}</x-button>
                    </div>
                </form>
            </x-card>

            <x-card :title="__('Change Password')" :subtitle="__('Use a strong password you do not use elsewhere.')">
                <form method="post" class="tw-grid tw-gap-5" action="{{ url('change-password') }}">
                    @csrf

                    @php
                        $currentPasswordError = $errors->first('current_password') ?: session('error_msg');
                    @endphp

                    <div class="ds-field tw-flex tw-flex-col tw-gap-1.5">
                        <label for="field-current_password" class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
                            {{ __('Current Password') }}<span class="tw-text-danger-600" aria-hidden="true"> *</span>
                        </label>
                        <input id="field-current_password" name="current_password" type="password"
                            placeholder="{{ __('Current Password') }}" required aria-required="true"
                            @if ($currentPasswordError) aria-invalid="true" aria-describedby="field-current_password-error" @endif
                            class="tw-h-10 tw-w-full tw-rounded-card tw-border tw-bg-neutral-0 tw-px-3 tw-text-sm tw-text-ink-900 tw-placeholder-neutral-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-accent-600/20 dark:tw-bg-ink-900 dark:tw-text-neutral-50 dark:tw-placeholder-neutral-500 {{ $currentPasswordError ? 'tw-border-danger-600' : 'tw-border-neutral-200 dark:tw-border-neutral-700' }}">
                        @if ($currentPasswordError)
                            <p id="field-current_password-error" class="tw-text-xs tw-font-medium tw-text-danger-600">
                                {{ $currentPasswordError }}
                            </p>
                        @endif
                    </div>

                    <div class="ds-field tw-flex tw-flex-col tw-gap-1.5">
                        <label for="field-password" class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
                            {{ __('New Password') }}<span class="tw-text-danger-600" aria-hidden="true"> *</span>
                        </label>
                        <input id="field-password" name="password" type="password"
                            placeholder="{{ __('New Password') }}" required aria-required="true"
                            @if ($errors->first('password')) aria-invalid="true" aria-describedby="field-password-error" @endif
                            class="tw-h-10 tw-w-full tw-rounded-card tw-border tw-bg-neutral-0 tw-px-3 tw-text-sm tw-text-ink-900 tw-placeholder-neutral-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-accent-600/20 dark:tw-bg-ink-900 dark:tw-text-neutral-50 dark:tw-placeholder-neutral-500 {{ $errors->first('password') ? 'tw-border-danger-600' : 'tw-border-neutral-200 dark:tw-border-neutral-700' }}">
                        @error('password')
                            <p id="field-password-error" class="tw-text-xs tw-font-medium tw-text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="ds-field tw-flex tw-flex-col tw-gap-1.5">
                        <label for="field-confirm_password" class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
                            {{ __('Confirm Password') }}<span class="tw-text-danger-600" aria-hidden="true"> *</span>
                        </label>
                        <input id="field-confirm_password" name="confirm_password" type="password"
                            placeholder="{{ __('Confirm Password') }}" required aria-required="true"
                            @if ($errors->first('confirm_password')) aria-invalid="true" aria-describedby="field-confirm_password-error" @endif
                            class="tw-h-10 tw-w-full tw-rounded-card tw-border tw-bg-neutral-0 tw-px-3 tw-text-sm tw-text-ink-900 tw-placeholder-neutral-400 focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-accent-600/20 dark:tw-bg-ink-900 dark:tw-text-neutral-50 dark:tw-placeholder-neutral-500 {{ $errors->first('confirm_password') ? 'tw-border-danger-600' : 'tw-border-neutral-200 dark:tw-border-neutral-700' }}">
                        @error('confirm_password')
                            <p id="field-confirm_password-error" class="tw-text-xs tw-font-medium tw-text-danger-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <x-button type="submit">{{ __('Changes') }}</x-button>
                    </div>
                </form>
            </x-card>
        </div>
    </div>
@endsection
