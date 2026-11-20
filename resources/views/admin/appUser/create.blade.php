@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Add User')" :parent="__('Users')" url="app-user" />

    <x-flash-messages />

    <x-card :title="__('Add User')" :subtitle="__('Create an attendee account and choose its initial status.')">
        <form method="post" action="{{ url('app-user') }}" enctype="multipart/form-data" class="tw-grid tw-gap-6">
            @csrf

            <div class="tw-grid tw-gap-6 md:tw-grid-cols-[minmax(0,190px)_minmax(0,1fr)]">
                <div class="ds-field tw-flex tw-flex-col tw-gap-1.5">
                    <label for="image-upload" class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
                        {{ __('Image') }}
                    </label>
                    <div id="image-preview" class="image-preview tw-flex tw-h-36 tw-items-center tw-justify-center tw-rounded-card tw-border tw-border-dashed tw-border-neutral-300 tw-bg-neutral-50 dark:tw-border-neutral-700 dark:tw-bg-ink-950">
                        <label for="image-upload" id="image-label"
                            class="tw-flex tw-h-10 tw-w-10 tw-cursor-pointer tw-items-center tw-justify-center tw-rounded-pill tw-bg-neutral-200 tw-text-neutral-600 dark:tw-bg-neutral-700 dark:tw-text-neutral-200">
                            <x-lucide-plus class="tw-h-5 tw-w-5" />
                            <span class="tw-sr-only">{{ __('Choose image') }}</span>
                        </label>
                    </div>
                    <input id="image-upload" name="image" type="file"
                        class="tw-text-sm tw-text-neutral-600 file:tw-mr-3 file:tw-rounded-pill file:tw-bg-neutral-100 file:tw-px-3 file:tw-py-1.5 file:tw-font-semibold file:tw-text-ink-800 dark:tw-text-neutral-300 dark:file:tw-bg-neutral-800 dark:file:tw-text-neutral-100">
                    @error('image')
                        <p class="tw-text-xs tw-font-medium tw-text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="tw-grid tw-gap-5">
                    <x-input name="name" :label="__('Full Name')" :placeholder="__('Full Name')" :value="old('name')" />
                    <x-input name="email" type="email" :label="__('Email')" :placeholder="__('Email')" :value="old('email')" />

                    <div class="tw-grid tw-gap-5 sm:tw-grid-cols-2">
                        <x-input name="password" type="password" :label="__('Password')" :placeholder="__('Password')" />

                        <x-select name="status" :label="__('status')" class="select2">
                            <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>{{ __('Block') }}</option>
                        </x-select>
                    </div>
                </div>
            </div>

            <div>
                <x-button type="submit">{{ __('Submit') }}</x-button>
            </div>
        </form>
    </x-card>
@endsection
