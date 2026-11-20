@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Event Gallery')" :parent="__('Event')" url="events" />

    <x-flash-messages />

    <x-card :padded="false">
        <x-slot:header>
            <div>
                <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Event Gallery') }}</h2>
                <p class="tw-mt-0.5 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">
                    {{ __('Upload and remove photos for this event.') }}
                </p>
            </div>
        </x-slot:header>

        <div class="tw-space-y-6 tw-p-5">
            <div class="image-gallery tw-grid tw-gap-4 sm:tw-grid-cols-2 lg:tw-grid-cols-3">
                @foreach (array_filter(explode(',', $data->gallery ?? '')) as $item)
                    <div class="img tw-overflow-hidden tw-rounded-card tw-border tw-border-neutral-200 dark:tw-border-neutral-800">
                        <img src="{{ url('images/upload/'.$item) }}" alt="{{ $item }}" loading="lazy"
                            class="tw-h-40 tw-w-full tw-object-cover" width="240" height="160">
                        <div class="tw-flex tw-items-center tw-justify-between tw-gap-2 tw-p-3">
                            <h4 class="tw-truncate tw-text-xs tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">{{ $item }}</h4>
                            <a href="{{ url('remove-image/'.$item.'/'.$data->id) }}" title="{{ __('Remove Image') }}"
                                aria-label="{{ __('Remove') }} {{ $item }}"
                                class="tw-inline-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-stub tw-border tw-border-danger-200 tw-text-danger-600 tw-transition hover:tw-bg-danger-100 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-danger-600 dark:tw-border-danger-500/30 dark:tw-text-danger-300 dark:hover:tw-bg-danger-500/10">
                                <x-lucide-trash-2 class="tw-h-4 tw-w-4" />
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <input type="hidden" name="id" id="data_id" value="{{ $data->id }}">

            <div class="dropzone dropzone-multiple tw-rounded-card tw-border tw-border-dashed tw-border-neutral-300 tw-bg-neutral-50 tw-p-5 dark:tw-border-neutral-700 dark:tw-bg-ink-950"
                data-toggle="dropzone" data-dropzone-multiple data-dropzone-url="{{ url('add-event-gallery') }}">
                @csrf
                <div class="fallback">
                    <div class="ds-field tw-flex tw-flex-col tw-gap-2">
                        <label for="dropzoneMultipleUpload" class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
                            {{ __('Choose file') }}
                        </label>
                        <input type="file" name="image[]" accept=".png, .jpg, .jpeg, .svg" class="tw-h-10 tw-w-full tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-px-3 tw-text-sm dark:tw-border-neutral-700 dark:tw-bg-ink-900"
                            id="dropzoneMultipleUpload" multiple>
                    </div>
                </div>
                <ul class="dz-preview dz-preview-multiple tw-mt-4 tw-space-y-2">
                    <li class="tw-flex tw-items-center tw-gap-3 tw-rounded-card tw-border tw-border-neutral-200 tw-p-3 dark:tw-border-neutral-800">
                        <div class="tw-h-10 tw-w-10 tw-shrink-0 tw-overflow-hidden tw-rounded-stub tw-bg-neutral-100 dark:tw-bg-neutral-800">
                            <img class="tw-h-full tw-w-full tw-object-cover" src="" alt="" data-dz-thumbnail>
                        </div>
                        <div class="tw-min-w-0 tw-flex-1">
                            <h4 class="tw-truncate tw-text-sm tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-50" data-dz-name>...</h4>
                            <p class="tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400" data-dz-size>...</p>
                        </div>
                        <a href="javascript:void(0)" class="tw-text-xs tw-font-semibold tw-text-danger-600 hover:tw-underline dark:tw-text-danger-300"
                            data-dz-remove>{{ __('Remove') }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </x-card>
@endsection
