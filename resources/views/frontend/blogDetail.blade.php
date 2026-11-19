@extends('layouts.public')

@section('title')
    {{ $data->title }} — {{ config('brand.name') }}
@endsection

@section('description', \Illuminate\Support\Str::limit(strip_tags((string) $data->description), 160))

@section('content')
    @php
        $tags = array_values(array_filter(array_map('trim', explode(',', (string) $data->tags))));
        $image = $data->image ? url('images/upload/'.$data->image) : asset('web/bg/1.jpg');
        $articleUrl = url('blog-detail/'.$data->id.'/'.preg_replace('/\s+/', '-', $data->title));
    @endphp

    <section class="tw-relative tw-overflow-hidden tw-bg-ink-950">
        <div class="tw-absolute tw-inset-0 tw-bg-gradient-to-br tw-from-ink-950 tw-via-ink-900 tw-to-accent-900/60"></div>
        <div class="tw-relative tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
            <nav aria-label="{{ __('Breadcrumb') }}" class="tw-flex tw-flex-wrap tw-items-center tw-gap-2 tw-text-xs tw-font-semibold tw-text-neutral-400">
                <a href="{{ url('/') }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ __('Home') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <a href="{{ url('all-blogs') }}" class="hover:tw-text-neutral-50 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-primary-500">{{ __('Blogs') }}</a>
                <x-lucide-chevron-right class="tw-h-3 tw-w-3" />
                <span class="tw-max-w-xs tw-truncate tw-text-neutral-200">{{ $data->title }}</span>
            </nav>

            <div class="tw-mt-6 tw-max-w-3xl">
                <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2">
                    @if ($data->category)
                        <x-badge tone="primary">{{ $data->category->name }}</x-badge>
                    @endif
                    <x-badge tone="neutral">
                        <x-slot:icon><x-lucide-calendar-days class="tw-h-3.5 tw-w-3.5" /></x-slot:icon>
                        {{ $data->created_at?->format('d F.Y') }}
                    </x-badge>
                </div>

                <h1 class="tw-mt-3 tw-text-3xl tw-font-extrabold tw-leading-tight tw-tracking-tight tw-text-neutral-50 md:tw-text-4xl">
                    {{ $data->title }}
                </h1>
            </div>
        </div>
    </section>

    <div class="tw-mx-auto tw-max-w-content tw-px-4 tw-py-10 md:tw-px-6 md:tw-py-14">
        <div class="tw-grid tw-gap-8 lg:tw-grid-cols-[minmax(0,1fr)_280px]">
            <div class="tw-min-w-0 tw-space-y-6">
                @if (session('status'))
                    <x-alert tone="success" :title="__('Notice')" dismissible>
                        <x-slot:icon><x-lucide-circle-check class="tw-h-5 tw-w-5" /></x-slot:icon>
                        {{ session('status') }}
                    </x-alert>
                @endif

                <x-card :padded="false">
                    <div class="tw-overflow-hidden tw-bg-neutral-100 dark:tw-bg-neutral-800">
                        <img src="{{ $image }}" alt="" width="1200" height="675" loading="lazy"
                            class="tw-h-auto tw-max-h-[520px] tw-w-full tw-object-cover">
                    </div>

                    <div class="tw-p-5 md:tw-p-8">
                        <p class="tw-text-xl tw-font-bold tw-leading-snug tw-text-ink-900 dark:tw-text-neutral-50">
                            {{ $data->title }}
                        </p>
                        <div class="tw-mt-4 tw-whitespace-pre-line tw-text-sm tw-leading-7 tw-text-neutral-600 dark:tw-text-neutral-300 md:tw-text-base">
                            {{ $data->description }}
                        </div>

                        @if (count($tags) > 0)
                            <div class="tw-mt-8 tw-border-t tw-border-neutral-100 tw-pt-5 dark:tw-border-neutral-800">
                                <p class="tw-text-xs tw-font-bold tw-uppercase tw-tracking-wider tw-text-neutral-400">{{ __('Tags') }}</p>
                                <div class="tw-mt-3 tw-flex tw-flex-wrap tw-gap-2">
                                    @foreach ($tags as $tag)
                                        <x-badge tone="neutral">{{ $tag }}</x-badge>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </x-card>
            </div>

            <aside class="tw-min-w-0 tw-space-y-6">
                <x-card :title="__('Share this article')" :subtitle="__('Bring a friend along.')">
                    <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-3">
                        <x-favorite-button :id="$data->id" type="blog" />
                        <div x-data="{ copied: false, copyLink() { navigator.clipboard.writeText('{{ $articleUrl }}').then(() => { this.copied = true; setTimeout(() => { this.copied = false; }, 1800); }); } }">
                            <button type="button" @click="copyLink()"
                                class="tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-border tw-border-neutral-200 tw-px-4 tw-text-sm tw-font-bold tw-text-neutral-700 tw-transition-colors hover:tw-border-primary-400 hover:tw-text-primary-700 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-text-neutral-200">
                                <x-lucide-copy class="tw-h-4 tw-w-4" />
                                <span x-text="copied ? '{{ __('Copied!') }}' : '{{ __('Copy link') }}'">{{ __('Copy link') }}</span>
                            </button>
                        </div>
                    </div>
                </x-card>

                <x-card :title="__('Keep exploring')">
                    <p class="tw-text-sm tw-leading-relaxed tw-text-neutral-600 dark:tw-text-neutral-300">
                        {{ __('Find more stories, guides and event highlights from the community.') }}
                    </p>
                    <a href="{{ url('all-blogs') }}"
                        class="tw-mt-4 tw-inline-flex tw-items-center tw-gap-1 tw-text-sm tw-font-bold tw-text-primary-700 hover:tw-text-primary-800 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-text-primary-300">
                        <x-lucide-arrow-left class="tw-h-4 tw-w-4" />
                        {{ __('Back to all blogs') }}
                    </a>
                </x-card>
            </aside>
        </div>
    </div>
@endsection
