@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Blog')" />

    <div class="tw-flex tw-flex-wrap tw-items-end tw-justify-between tw-gap-4">
        <div>
            <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight tw-text-ink-900 dark:tw-text-neutral-50">
                {{ __('All Blogs') }}
            </h1>
            <p class="tw-mt-1 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                {{ __('Manage articles and announcements published on the site.') }}
            </p>
        </div>

        @can('blog_create')
            <a href="{{ url('blog/create') }}"
                class="tw-inline-flex tw-h-10 tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                <x-lucide-plus class="tw-h-4 tw-w-4" />
                {{ __('Add New') }}
            </a>
        @endcan
    </div>

    <x-flash-messages />

    <x-card :padded="false">
        @if ($blog->isEmpty())
            <x-empty-state :title="__('No blogs yet')" :description="__('Publish an article to share news with your audience.')" />
        @else
            <div class="tw-overflow-x-auto">
                <table id="report_table" class="tw-min-w-full tw-text-left tw-text-sm">
                    <thead>
                        <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500 dark:tw-border-neutral-700 dark:tw-text-neutral-400">
                            <th class="tw-w-8 tw-px-3 tw-py-3"></th>
                            <th class="tw-px-3 tw-py-3">{{ __('Image') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Title') }}</th>
                            <th class="tw-px-3 tw-py-3">{{ __('Status') }}</th>
                            @if (Gate::check('blog_edit') || Gate::check('blog_delete'))
                                <th class="tw-px-3 tw-py-3">{{ __('Action') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($blog as $item)
                            <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0 dark:tw-border-neutral-800">
                                <td class="tw-px-3 tw-py-3">
                                    <span class="tw-inline-flex tw-h-8 tw-w-8 tw-items-center tw-justify-center tw-rounded-stub tw-bg-accent-100 tw-text-accent-700 dark:tw-bg-accent-500/20 dark:tw-text-accent-300">
                                        <x-lucide-book-open class="tw-h-4 tw-w-4" />
                                    </span>
                                </td>
                                <td class="tw-px-3 tw-py-3">
                                    <img src="{{ url('images/upload/'.$item->image) }}" alt="{{ $item->title }}"
                                        loading="lazy" width="48" height="48"
                                        class="tw-h-12 tw-w-12 tw-rounded-stub tw-object-cover">
                                </td>
                                <td class="tw-px-3 tw-py-3 tw-font-semibold tw-text-ink-900 dark:tw-text-neutral-100">{{ $item->title }}</td>
                                <td class="tw-px-3 tw-py-3">
                                    <x-badge :tone="$item->status == '1' ? 'success' : 'danger'">
                                        {{ $item->status == '1' ? __('Active') : __('Inactive') }}
                                    </x-badge>
                                </td>
                                @if (Gate::check('blog_edit') || Gate::check('blog_delete'))
                                    <td class="tw-px-3 tw-py-3">
                                        <span class="tw-inline-flex tw-items-center tw-gap-2">
                                            @can('blog_edit')
                                                <a href="{{ route('blog.edit', $item->id) }}" title="Edit Blog"
                                                    aria-label="{{ __('Edit') }} {{ $item->title }}"
                                                    class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-text-accent-600 tw-transition hover:tw-bg-accent-50 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:hover:tw-bg-accent-900/20">
                                                    <x-lucide-pencil class="tw-h-4 tw-w-4" />
                                                </a>
                                            @endcan
                                            @can('blog_delete')
                                                <a href="javascript:void(0)" onclick="deleteData('blog','{{ $item->id }}');"
                                                    title="Delete Blog" aria-label="{{ __('Delete') }} {{ $item->title }}"
                                                    class="tw-inline-flex tw-h-9 tw-w-9 tw-items-center tw-justify-center tw-rounded-stub tw-border tw-border-danger-200 tw-text-danger-600 tw-transition hover:tw-bg-danger-100 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-danger-600 dark:tw-border-danger-500/30 dark:tw-text-danger-300 dark:hover:tw-bg-danger-500/10">
                                                    <x-lucide-trash-2 class="tw-h-4 tw-w-4" />
                                                </a>
                                            @endcan
                                        </span>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-card>
@endsection
