@extends('layouts.public')

@section('title', __('Our Latest Blog').' — '.config('brand.name'))
@section('description', __('Stories, guides and highlights from the '.config('brand.name').' community.'))

@section('content')
    <section class="tw-relative tw-overflow-hidden tw-bg-ink-950">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-br tw-from-ink-950 tw-via-ink-900 tw-to-primary-900/60"></div>
        <div class="tw-relative tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
            <nav aria-label="{{ __('Breadcrumb') }}" class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-text-neutral-400">
                <a href="{{ url('/') }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ __('Home') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <span class="tw-text-neutral-200">{{ __('Blogs') }}</span>
            </nav>

            <div class="tw-mt-6 tw-max-w-2xl">
                <x-badge tone="primary"><x-slot:icon><x-lucide-ticket class="tw-h-3.5 tw-w-3.5" /></x-slot:icon>{{ config('brand.name') }}</x-badge>
                <h1 class="tw-mt-3 tw-text-3xl tw-font-extrabold tw-tracking-tight tw-text-neutral-50 md:tw-text-4xl">
                    {{ __('Our Latest Blog') }}
                </h1>
                <p class="tw-mt-2 tw-text-sm tw-leading-relaxed tw-text-neutral-300">
                    {{ __('Stories, guides and highlights from our community.') }}
                </p>
            </div>
        </div>
    </section>

    <div class="tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
        @if ($blogs->isNotEmpty())
            <div class="tw-grid tw-gap-6 sm:tw-grid-cols-2 xl:tw-grid-cols-3">
                @foreach ($blogs as $item)
                    @php
                        $articleUrl = url('blog-detail/'.$item->id.'/'.preg_replace('/\s+/', '-', $item->title));
                        $image = $item->image ? url('images/upload/'.$item->image) : asset('web/bg/1.jpg');
                    @endphp

                    <article class="tw-group tw-overflow-hidden tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-shadow-card tw-transition-shadow hover:tw-shadow-card-hover dark:tw-border-neutral-800 dark:tw-bg-ink-900">
                        <div class="tw-relative">
                            <a href="{{ $articleUrl }}" class="tw-block tw-overflow-hidden tw-bg-neutral-100 dark:tw-bg-neutral-800" tabindex="-1" aria-hidden="true">
                                <div class="tw-aspect-video">
                                    <img src="{{ $image }}" alt="" loading="lazy" width="640" height="360"
                                        class="tw-h-full tw-w-full tw-object-cover tw-transition-transform tw-duration-300 group-hover:tw-scale-105">
                                </div>
                            </a>
                            <div class="tw-absolute tw-right-4 tw-top-4 tw-z-10">
                                <x-favorite-button :id="$item->id" type="blog" />
                            </div>
                        </div>

                        <div class="tw-p-5">
                            <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-x-4 tw-gap-y-1 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                                <span class="tw-inline-flex tw-items-center tw-gap-1.5">
                                    <x-lucide-ticket class="tw-h-3.5 tw-w-3.5" />
                                    {{ $item->category?->name ?: __('General') }}
                                </span>
                                <span class="tw-inline-flex tw-items-center tw-gap-1.5">
                                    <x-lucide-calendar-days class="tw-h-3.5 tw-w-3.5" />
                                    {{ $item->created_at?->format('d M.Y') }}
                                </span>
                            </div>

                            <h2 class="tw-mt-3 tw-text-lg tw-font-bold tw-leading-snug tw-text-ink-900 dark:tw-text-neutral-50">
                                <a href="{{ $articleUrl }}" class="tw-transition-colors hover:tw-text-primary-700 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">{{ $item->title }}</a>
                            </h2>

                            <a href="{{ $articleUrl }}"
                                class="tw-mt-4 tw-inline-flex tw-items-center tw-gap-1 tw-text-sm tw-font-semibold tw-text-accent-700 hover:tw-text-accent-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-accent-300">
                                {{ __('Read article') }}
                                <x-lucide-arrow-up-right class="tw-h-4 tw-w-4" />
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <x-card>
                <x-empty-state :title="__('No articles yet')" :description="__('Check back soon for stories and event guides.')" />
            </x-card>
        @endif
    </div>
@endsection
