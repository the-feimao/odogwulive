@extends('layouts.design')

@section('content')
    <script>
        // Deterministic side-by-side preview; the toggle below still changes the whole page.
        document.documentElement.classList.remove('tw-dark');
    </script>

    <div class="tw-mb-12 tw-rounded-card tw-border tw-border-neutral-200 tw-p-4 dark:tw-border-neutral-800">
        <p class="tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
            Every component is rendered twice below. The theme toggle changes the whole page; the dark section is always wrapped in an explicit <code class="tw-font-mono tw-text-xs">.tw-dark</code> container.
        </p>
    </div>

    <section aria-labelledby="light-preview">
        <h2 id="light-preview" class="tw-mb-6 tw-text-xl tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">Light mode</h2>
        <div class="ds tw-space-y-4 tw-rounded-card tw-border tw-border-neutral-200 tw-p-5 dark:tw-border-neutral-800">
            @include('components.style-guide-samples')
        </div>
    </section>

    <section class="tw-mt-16" aria-labelledby="dark-preview">
        <h2 id="dark-preview" class="tw-mb-6 tw-text-xl tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">Dark mode</h2>
        <div class="tw-dark">
            <div class="ds tw-space-y-4 tw-rounded-card tw-border tw-border-neutral-800 tw-bg-ink-950 tw-p-5">
                @include('components.style-guide-samples')
            </div>
        </div>
    </section>
@endsection
