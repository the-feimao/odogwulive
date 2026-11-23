@extends('layouts.public')

@section('title', $title.' — '.config('brand.name'))

@section('content')
    <div class="tw-mx-auto tw-max-w-xl tw-px-4 tw-py-12 md:tw-px-6">
        <x-card :title="$title">
            <p class="tw-text-sm tw-leading-relaxed tw-text-neutral-600 dark:tw-text-neutral-300">{{ $message }}</p>
            <a href="{{ url('/') }}"
                class="tw-mt-6 tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-5 tw-text-sm tw-font-bold tw-text-ink-950 hover:tw-bg-primary-400 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-600">
                <x-lucide-arrow-left class="tw-h-4 tw-w-4" />
                {{ __('Back to home') }}
            </a>
        </x-card>
    </div>
@endsection
