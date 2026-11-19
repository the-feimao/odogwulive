@props([
    'tabs' => [],
    'active' => 0,
])

@php
    $normalizedTabs = collect($tabs)->map(function ($tab, $index) {
        if (is_array($tab)) {
            return [
                'label' => $tab['label'] ?? 'Tab '.($index + 1),
                'slot' => $tab['slot'] ?? 'tab'.($index + 1),
            ];
        }

        return ['label' => (string) $tab, 'slot' => 'tab'.($index + 1)];
    })->values()->all();

    $initial = (int) $active;
@endphp

<div x-data="{ active: {{ $initial }} }" {{ $attributes->merge(['class' => 'ds-tabs']) }}>
    <div role="tablist" aria-label="Tabs" class="tw-flex tw-gap-1 tw-overflow-x-auto tw-border-b tw-border-neutral-200 dark:tw-border-neutral-800">
        @foreach ($normalizedTabs as $index => $tab)
            <button type="button" role="tab" @click="active = {{ $index }}"
                x-bind:aria-selected="active === {{ $index }} ? 'true' : 'false'"
                x-bind:tabindex="active === {{ $index }} ? 0 : -1"
                x-bind:class="active === {{ $index }}
                    ? 'tw-border-accent-600 tw-text-ink-900 dark:tw-text-neutral-50'
                    : 'tw-border-transparent tw-text-neutral-500 hover:tw-text-ink-800 dark:hover:tw-text-neutral-200'"
                class="tw-whitespace-nowrap tw-border-b-2 tw-px-3 tw-py-2.5 tw-text-sm tw-font-semibold tw-transition focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                {{ $tab['label'] }}
            </button>
        @endforeach
    </div>

    <div class="tw-pt-5">
        @foreach ($normalizedTabs as $index => $tab)
            @php
                $slotName = $tab['slot'];
                $panel = isset($$slotName) ? (string) $$slotName : '';
            @endphp
            <div x-show="active === {{ $index }}" @if ($index !== $initial) x-cloak @endif role="tabpanel">
                {!! $panel !!}
            </div>
        @endforeach
    </div>
</div>
