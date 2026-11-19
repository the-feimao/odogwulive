@php
    $demoPaginator = new Illuminate\Pagination\LengthAwarePaginator(
        collect(range(1, 60)),
        60,
        10,
        3,
        ['path' => request()->url()]
    );
@endphp

<section class="tw-mb-14">
    <h2 class="tw-text-2xl tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">Tokens</h2>
    <p class="tw-mt-1 tw-text-sm tw-text-neutral-500 dark:tw-text-neutral-400">One palette, one type family, one 8px rhythm.</p>

    <div class="tw-mt-5 tw-grid tw-grid-cols-2 tw-gap-4 md:tw-grid-cols-3 lg:tw-grid-cols-6">
        @foreach ([['primary', 'tw-bg-primary-500'], ['accent', 'tw-bg-accent-600'], ['ink', 'tw-bg-ink-900'], ['success', 'tw-bg-success-600'], ['warning', 'tw-bg-warning-500'], ['danger', 'tw-bg-danger-600']] as $swatch)
            <div class="tw-rounded-card tw-border tw-border-neutral-200 tw-p-3 dark:tw-border-neutral-800">
                <div class="tw-h-10 tw-rounded-stub {{ $swatch[1] }}"></div>
                <p class="tw-mt-2 tw-text-xs tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-200">{{ $swatch[0] }}</p>
            </div>
        @endforeach
    </div>

    <div class="tw-mt-5 tw-flex tw-flex-wrap tw-items-center tw-gap-6">
        <div><p class="tw-text-3xl tw-font-bold tw-tracking-tight">Aa</p><p class="tw-text-xs tw-text-neutral-500">Plus Jakarta Sans / 700</p></div>
        <div><p class="tw-text-xl tw-font-semibold">Heading</p><p class="tw-text-xs tw-text-neutral-500">2xl / 600</p></div>
        <div><p class="tw-text-sm">Body copy at 14px</p><p class="tw-text-xs tw-text-neutral-500">sm / 400</p></div>
        <div><span class="tw-inline-block tw-rounded-card tw-bg-primary-500 tw-px-4 tw-py-2 tw-shadow-card">Radius + shadow</span></div>
    </div>
</section>

<section class="tw-mb-14">
    <h2 class="tw-text-2xl tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">Buttons</h2>
    <div class="tw-mt-5 tw-flex tw-flex-wrap tw-items-center tw-gap-3">
        <x-button>Primary action</x-button>
        <x-button variant="secondary">Secondary</x-button>
        <x-button variant="ghost">Ghost</x-button>
        <x-button variant="danger">Delete</x-button>
        <x-button size="sm">Small</x-button>
        <x-button size="lg">Large</x-button>
        <x-button disabled>Disabled</x-button>
        <x-button>
            <x-slot:icon><x-lucide-ticket class="tw-h-4 tw-w-4" /></x-slot:icon>
            With icon
        </x-button>
        <x-button variant="secondary" full class="tw-max-w-xs">Full width</x-button>
    </div>
</section>

<section class="tw-mb-14">
    <h2 class="tw-text-2xl tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">Forms</h2>
    <div class="tw-mt-5 tw-grid tw-gap-5 md:tw-grid-cols-3">
        <x-input name="demo_email" label="Email address" type="email" placeholder="you@example.com" help="We never share your email." required />
        <x-select name="demo_city" label="City" help="Choose your nearest city.">
            <option value="">Select a city</option>
            <option value="lagos">Lagos</option>
            <option value="abuja">Abuja</option>
        </x-select>
        <x-textarea name="demo_notes" label="Notes" placeholder="Anything we should know?" rows="3" error="Please add a short note." />
    </div>
</section>

<section class="tw-mb-14">
    <h2 class="tw-text-2xl tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">Badges, cards and alerts</h2>

    <div class="tw-mt-5 tw-flex tw-flex-wrap tw-gap-2">
        <x-badge>Neutral</x-badge>
        <x-badge tone="primary">Featured</x-badge>
        <x-badge tone="accent">Verified</x-badge>
        <x-badge tone="success">Confirmed</x-badge>
        <x-badge tone="warning">Pending</x-badge>
        <x-badge tone="danger">Cancelled</x-badge>
    </div>

    <div class="tw-mt-5 tw-grid tw-gap-4 md:tw-grid-cols-2">
        <x-card title="Event summary" subtitle="Everything at a glance">
            <p class="tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">Cards carry dense content without losing hierarchy.</p>
            <x-slot:footer>
                <x-button size="sm">Continue</x-button>
            </x-slot:footer>
        </x-card>

        <x-card>
            <x-slot:header>
                <div>
                    <h2 class="tw-text-lg tw-font-bold">Custom header</h2>
                    <p class="tw-text-xs tw-text-neutral-500">Use the header slot when needed.</p>
                </div>
            </x-slot:header>
            <p class="tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">The footer slot is optional.</p>
        </x-card>
    </div>

    <div class="tw-mt-5 tw-grid tw-gap-4 md:tw-grid-cols-2">
        <x-alert tone="success" title="Payment confirmed" dismissible>
            <x-slot:icon><x-lucide-circle-check class="tw-h-5 tw-w-5" /></x-slot:icon>
            Your tickets are ready to download.
        </x-alert>
        <x-alert tone="warning" title="Almost sold out">
            <x-slot:icon><x-lucide-alert-circle class="tw-h-5 tw-w-5" /></x-slot:icon>
            Only a few tickets remain for this event.
        </x-alert>
    </div>

    <x-toast title="Saved" timeout="60000">
        <x-slot:icon><x-lucide-check class="tw-h-5 tw-w-5" /></x-slot:icon>
        Your changes were saved successfully.
    </x-toast>
</section>

<section class="tw-mb-14">
    <h2 class="tw-text-2xl tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">Interactive components</h2>

    <div class="tw-mt-5 tw-grid tw-gap-5 md:tw-grid-cols-3">
        <x-modal title="Confirm order" id="demo-modal">
            <p class="tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">This dialog closes on Escape, backdrop click or the close button.</p>
            <x-slot:trigger><x-button size="sm">Open modal</x-button></x-slot:trigger>
            <x-slot:footer>
                <x-button variant="secondary" size="sm">Cancel</x-button>
                <x-button size="sm">Confirm</x-button>
            </x-slot:footer>
        </x-modal>

        <x-dropdown label="Actions">
            <button type="button" role="menuitem" class="tw-block tw-w-full tw-rounded-stub tw-px-3 tw-py-2 tw-text-left tw-text-sm hover:tw-bg-neutral-100 dark:hover:tw-bg-neutral-800">Edit details</button>
            <button type="button" role="menuitem" class="tw-block tw-w-full tw-rounded-stub tw-px-3 tw-py-2 tw-text-left tw-text-sm hover:tw-bg-neutral-100 dark:hover:tw-bg-neutral-800">Duplicate</button>
            <button type="button" role="menuitem" class="tw-block tw-w-full tw-rounded-stub tw-px-3 tw-py-2 tw-text-left tw-text-sm tw-text-danger-600 hover:tw-bg-danger-50 dark:hover:tw-bg-danger-900/30">Delete</button>
        </x-dropdown>

        <div>
            <p class="tw-text-xs tw-text-neutral-500">Theme toggle</p>
            <div class="tw-mt-2"><x-theme-toggle /></div>
        </div>
    </div>

    <div class="tw-mt-6">
        <x-tabs :tabs="[['label' => 'Overview', 'slot' => 'overview'], ['label' => 'Schedule', 'slot' => 'schedule'], ['label' => 'Venue', 'slot' => 'venue']]">
            <x-slot:overview><p class="tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">Overview panel content.</p></x-slot:overview>
            <x-slot:schedule><p class="tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">Schedule panel content.</p></x-slot:schedule>
            <x-slot:venue><p class="tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">Venue panel content.</p></x-slot:venue>
        </x-tabs>
    </div>
</section>

<section class="tw-mb-14">
    <h2 class="tw-text-2xl tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">Domain cards</h2>
    <div class="tw-mt-5 tw-grid tw-gap-5 md:tw-grid-cols-2">
        <x-event-card title="Odogwu Fest 2026" date="Sat, 21 Nov · 18:00" location="Lagos, Nigeria" price="₦25,000" badge="Featured" href="#" />
        <x-ticket-card title="VIP Admission" event="Odogwu Fest 2026" price="₦25,000" code="ODG-2026-VIP-0042" status="Valid" />
    </div>
</section>

<section class="tw-mb-14">
    <h2 class="tw-text-2xl tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">Loading and states</h2>
    <div class="tw-mt-5 tw-grid tw-gap-5 md:tw-grid-cols-2">
        <x-card title="Skeleton loading">
            <x-skeleton :lines="3" avatar />
        </x-card>
        <x-card title="Empty state">
            <x-empty-state title="No events found" description="Try changing your filters to see more events.">
                <x-slot:action><x-button variant="secondary" size="sm">Clear filters</x-button></x-slot:action>
            </x-empty-state>
        </x-card>
    </div>

    <div class="tw-mt-6">
        <x-pagination :paginator="$demoPaginator" />
    </div>
</section>
