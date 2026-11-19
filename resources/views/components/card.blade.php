@props([
    'title' => null,
    'subtitle' => null,
    'padded' => true,
])

<section {{ $attributes->merge(['class' => 'ds-card tw-rounded-card tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-shadow-card dark:tw-border-neutral-800 dark:tw-bg-ink-900']) }}>
    @if ($title || isset($header))
        <header class="tw-flex tw-items-start tw-justify-between tw-gap-4 tw-border-b tw-border-neutral-100 tw-px-5 tw-py-4 dark:tw-border-neutral-800">
            @if (isset($header))
                {{ $header }}
            @else
                <div>
                    <h2 class="tw-text-lg tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ $title }}</h2>
                    @if ($subtitle)
                        <p class="tw-mt-0.5 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">{{ $subtitle }}</p>
                    @endif
                </div>
            @endif
        </header>
    @endif

    <div class="{{ $padded ? 'tw-p-5' : '' }}">
        {{ $slot }}
    </div>

    @if (isset($footer))
        <footer class="tw-border-t tw-border-neutral-100 tw-px-5 tw-py-4 dark:tw-border-neutral-800">
            {{ $footer }}
        </footer>
    @endif
</section>
