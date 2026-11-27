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
                                {{ count($followerIds ?? []) }}
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
                    $followers = \App\Models\AppUser::whereIn('id', $followerIds ?? [])->get();
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

            @if (Auth::user()->hasRole('organization'))
                @php
                    $payoutAccount = Auth::user();
                @endphp
                <x-card :title="__('Paystack Payout Details')" :subtitle="__('Used to pay your commission by direct Paystack transfer.')">
                    <div x-data="{ banks: [], loading: true }"
                        x-init="fetch('{{ url('/organization/paystack-banks') }}').then(function (response) { return response.json(); }).then(function (data) { banks = data.banks || []; }).catch(function () { banks = []; }).finally(function () { loading = false; })"
                        class="tw-grid tw-gap-5">

                        @if ($payoutAccount->hasPayoutDetails())
                            <x-alert tone="success" :title="__('Payout account on file')">
                                {{ __('Bank:').' '.$payoutAccount->bank_name.' · '.__('Account:').' '.$payoutAccount->maskedAccountNumber() }}
                                @if ($payoutAccount->payout_verified_at)
                                    · {{ __('Verified with Paystack') }}
                                @endif
                            </x-alert>
                        @endif

                        <datalist id="paystack-bank-names">
                            <template x-for="bank in banks" :key="bank.code">
                                <option :value="bank.name"></option>
                            </template>
                        </datalist>
                        <datalist id="paystack-bank-codes">
                            <template x-for="bank in banks" :key="bank.code">
                                <option :value="bank.code" x-text="bank.name"></option>
                            </template>
                        </datalist>

                        <form method="post" action="{{ url('organization/paystack-account') }}" class="tw-grid tw-gap-5">
                            @csrf

                            <p class="tw-text-xs tw-leading-relaxed tw-text-neutral-500 dark:tw-text-neutral-400" x-show="loading">
                                {{ __('Loading Paystack banks…') }}
                            </p>

                            <div class="tw-grid tw-gap-4 sm:tw-grid-cols-2">
                                <x-input name="bank_name" :label="__('Bank name')" :value="old('bank_name', $payoutAccount->bank_name)"
                                    :placeholder="__('e.g. Access Bank')" list="paystack-bank-names" required />
                                <x-input name="bank_code" :label="__('Bank code')" :value="old('bank_code', $payoutAccount->bank_code)"
                                    :placeholder="__('e.g. 044')" list="paystack-bank-codes" required />
                            </div>

                            <x-input name="account_name" :label="__('Account name')" :value="old('account_name', $payoutAccount->account_name)"
                                :placeholder="__('Name on the bank account')" required />

                            @php
                                $accountError = $errors->first('account_number') ?: ($payoutAccount->hasPayoutDetails() ? null : __('Account number is required.'));
                            @endphp
                            <div class="ds-field tw-flex tw-flex-col tw-gap-1.5">
                                <label for="field-account_number" class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
                                    {{ __('Account number') }}
                                </label>
                                <input id="field-account_number" name="account_number" type="text" value=""
                                    placeholder="{{ $payoutAccount->hasPayoutDetails() ? __('Leave blank to keep ').$payoutAccount->maskedAccountNumber() : __('10-digit account number') }}"
                                    inputmode="numeric" pattern="[0-9]{10}"
                                    @if ($accountError) aria-invalid="true" aria-describedby="field-account_number-error" @endif
                                    class="tw-h-10 tw-w-full tw-rounded-card tw-border tw-bg-neutral-0 tw-px-3 tw-text-sm tw-text-ink-900 tw-placeholder-neutral-400 tw-transition focus:tw-outline-none focus:tw-ring-2 focus:tw-ring-accent-600/20 dark:tw-bg-ink-900 dark:tw-text-neutral-50 dark:tw-placeholder-neutral-500 {{ $accountError ? 'tw-border-danger-600' : 'tw-border-neutral-200 dark:tw-border-neutral-700' }}">
                                @if ($accountError)
                                    <p id="field-account_number-error" class="tw-text-xs tw-font-medium tw-text-danger-600">{{ $accountError }}</p>
                                @endif
                            </div>

                            <label class="tw-flex tw-items-start tw-gap-2 tw-text-sm tw-text-neutral-700 dark:tw-text-neutral-200">
                                <input type="checkbox" name="verify" value="1"
                                    class="tw-mt-0.5 tw-h-4 tw-w-4 tw-rounded-card tw-border-neutral-300 tw-text-accent-600 focus:tw-ring-accent-600 dark:tw-border-neutral-600 dark:tw-bg-ink-900">
                                <span>{{ __('Verify this account with Paystack before saving.') }}</span>
                            </label>

                            <div>
                                <x-button type="submit">{{ __('Save Payout Details') }}</x-button>
                            </div>
                        </form>
                    </div>
                </x-card>
            @endif

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
