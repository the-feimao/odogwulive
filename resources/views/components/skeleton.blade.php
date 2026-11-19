@props([
    'lines' => 3,
    'avatar' => false,
])

<div class="ds-skeleton tw-flex tw-items-start tw-gap-3 tw-animate-pulse" aria-hidden="true">
    @if ($avatar)
        <div class="tw-h-10 tw-w-10 tw-shrink-0 tw-rounded-pill tw-bg-neutral-200 dark:tw-bg-neutral-800"></div>
    @endif

    <div class="tw-flex-1 tw-space-y-2">
        @for ($line = 0; $line < (int) $lines; $line++)
            <div class="tw-h-3 tw-rounded tw-bg-neutral-200 dark:tw-bg-neutral-800"
                style="width: {{ [100, 84, 68, 92][$line % 4] }}%"></div>
        @endfor
    </div>
</div>
