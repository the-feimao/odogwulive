@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Edit Blog')" :parent="__('Blog')" url="blog" />

    <x-flash-messages />

    <x-card :title="__('Edit Blog')" :subtitle="__('Update the article details or replace its image.')">
        <form method="post" action="{{ route('blog.update', [$blog->id]) }}" enctype="multipart/form-data" class="tw-grid tw-gap-6">
            @csrf
            @method('PUT')

            <div class="tw-grid tw-gap-6 md:tw-grid-cols-[minmax(0,190px)_minmax(0,1fr)]">
                <div class="ds-field tw-flex tw-flex-col tw-gap-1.5">
                    <label for="image-upload" class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
                        {{ __('Image') }}
                    </label>
                    <div id="image-preview" class="image-preview tw-flex tw-h-36 tw-items-center tw-justify-center tw-rounded-card tw-border tw-border-dashed tw-border-neutral-300 tw-bg-neutral-50 tw-bg-cover tw-bg-center dark:tw-border-neutral-700 dark:tw-bg-ink-950"
                        style="background-image: url({{ url('images/upload/'.$blog->image) }})">
                        <label for="image-upload" id="image-label"
                            class="tw-flex tw-h-10 tw-w-10 tw-cursor-pointer tw-items-center tw-justify-center tw-rounded-pill tw-bg-white/80 tw-text-neutral-600 tw-shadow-sm dark:tw-bg-ink-900/80 dark:tw-text-neutral-200">
                            <x-lucide-plus class="tw-h-5 tw-w-5" />
                            <span class="tw-sr-only">{{ __('Change image') }}</span>
                        </label>
                    </div>
                    <input id="image-upload" name="image" type="file"
                        class="tw-text-sm tw-text-neutral-600 file:tw-mr-3 file:tw-rounded-pill file:tw-bg-neutral-100 file:tw-px-3 file:tw-py-1.5 file:tw-font-semibold file:tw-text-ink-800 dark:tw-text-neutral-300 dark:file:tw-bg-neutral-800 dark:file:tw-text-neutral-100">
                    @error('image')
                        <p class="tw-text-xs tw-font-medium tw-text-danger-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="tw-grid tw-gap-5">
                    <x-input name="title" :label="__('Title')" :placeholder="__('Title')" :value="$blog->title" />

                    <div class="tw-grid tw-gap-5 sm:tw-grid-cols-2">
                        <x-select name="category_id" :label="__('Category')" class="select2">
                            <option value="">{{ __('Select Category') }}</option>
                            @foreach ($category as $item)
                                <option value="{{ $item->id }}" {{ old('category_id', $blog->category_id) == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                            @endforeach
                        </x-select>

                        <x-select name="status" :label="__('status')" class="select2">
                            <option value="1" {{ old('status', $blog->status) == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                            <option value="0" {{ old('status', $blog->status) == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
                        </x-select>
                    </div>

                    <x-input name="tags" :label="__('Tags')" class="inputtags" :value="$blog->tags" />

                    <x-rich-textarea name="description" :label="__('Description')"
                        :value="$blog->description"
                        :help="__('Use the toolbar to format the article before publishing.')" />
                </div>
            </div>

            <div>
                <x-button type="submit">{{ __('Submit') }}</x-button>
            </div>
        </form>
    </x-card>
@endsection
