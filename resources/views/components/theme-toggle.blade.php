<button type="button"
    x-data="{ dark: document.documentElement.classList.contains('tw-dark') }"
    @click="dark = !dark; document.documentElement.classList.toggle('tw-dark', dark); localStorage.setItem('ds-theme', dark ? 'dark' : 'light')"
    x-bind:aria-label="dark ? 'Switch to light mode' : 'Switch to dark mode'"
    {{ $attributes->merge(['class' => 'ds-theme-toggle tw-inline-flex tw-h-10 tw-w-10 tw-items-center tw-justify-center tw-rounded-pill tw-border tw-border-neutral-200 tw-bg-neutral-0 tw-text-ink-700 tw-transition hover:tw-bg-neutral-100 focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600 dark:tw-border-neutral-700 dark:tw-bg-ink-900 dark:tw-text-neutral-200 dark:hover:tw-bg-neutral-800']) }}>
    <x-lucide-sun x-cloak x-show="!dark" class="tw-h-5 tw-w-5" />
    <x-lucide-moon x-cloak x-show="dark" class="tw-h-5 tw-w-5" />
</button>
