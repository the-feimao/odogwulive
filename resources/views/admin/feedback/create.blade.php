@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Add Feedback')" :parent="__('Feedback')" url="feedback" />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('Add Feedback') }}
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('Share how your experience has been so far.') }}
            </p>
        </div>
    </div>

    <x-flash-messages />

    <x-card :title="__('Add Feedback')" :subtitle="__('Your rating and message help us improve.')">
        <form method="post" class="feedback-form tw-grid tw-gap-6" action="{{ url('feedback') }}" enctype="multipart/form-data"
            x-data="{ rate: {{ (int) old('rate', 0) }} }">
            @csrf

            <input type="hidden" name="rate" value="{{ old('rate') }}" x-bind:value="rate">

            <div class="tw-flex tw-flex-col tw-gap-1.5">
                <label for="feedback-rating" class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
                    {{ __('Rate') }}<span class="tw-text-danger-600" aria-hidden="true"> *</span>
                </label>
                <div class="rating tw-flex tw-items-center tw-gap-2" id="feedback-rating" role="group" aria-label="{{ __('Rating') }}">
                    @for ($i = 1; $i <= 5; $i++)
                        <i id="rate-{{ $i }}" role="button" tabindex="0"
                            aria-label="{{ $i }} {{ __('star(s)') }}"
                            x-bind:aria-pressed="rate >= {{ $i }} ? 'true' : 'false'"
                            x-bind:class="rate >= {{ $i }} ? 'tw-text-warning-500' : 'tw-text-neutral-300 dark:tw-text-neutral-700'"
                            x-on:click="rate = {{ $i }}"
                            x-on:keydown.enter.prevent="rate = {{ $i }}"
                            x-on:keydown.space.prevent="rate = {{ $i }}"
                            onclick="addRate('{{ $i }}');"
                            class="tw-inline-flex tw-cursor-pointer tw-items-center tw-justify-center tw-rounded-stub tw-p-1 tw-transition focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                            <x-lucide-star class="tw-h-7 tw-w-7 tw-fill-current" />
                        </i>
                    @endfor
                </div>
                @error('rate')
                    <p class="tw-text-xs tw-font-medium tw-text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <x-input name="email" :label="__('Email')" type="email" :value="old('email')" :placeholder="__('Email')" required />

            <x-textarea name="message" :label="__('Message')" :value="old('message')" :placeholder="__('Message')" required />

            <div>
                <x-button type="submit">{{ __('Submit') }}</x-button>
            </div>
        </form>
    </x-card>
@endsection
