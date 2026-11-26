{{--
    Shared event create/edit form.

    Legacy hooks are intentionally preserved: `.event-form`, `#image-preview`,
    `#image-upload`, `#image-label`, `#start_time`, `#end_time`,
    `#org-for-event`, `.scanner_id`, `.location-detail`, `.inputtags` and
    `.textarea_editor`. Steps use x-show (not x-if) so these plugins keep
    initialising against elements that are already in the DOM.
--}}
@php
    $isEdit = isset($event) && $event !== null;
    $type = old('type', $isEdit ? $event->type : 'offline');
    $jsType = in_array($type, ['offline', 'online'], true) ? $type : 'offline';
    $security = $isEdit ? (string) $event->security : old('security', '1');
    $onlineLink = old('event_link', $isEdit ? $event->event_link : '');

    // Return organisers to the step that actually failed validation.
    $initialStep = 1;
    foreach (['type', 'event_link', 'address', 'lat', 'lang', 'security'] as $accessField) {
        if ($errors->has($accessField)) {
            $initialStep = 2;
            break;
        }
    }
@endphp

<form method="post" class="event-form tw-grid tw-gap-6"
    x-data="{ step: {{ $initialStep }}, type: '{{ $jsType }}' }"
    action="{{ $isEdit ? route('events.update', [$event->id]) : url('events') }}"
    enctype="multipart/form-data">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    @if ($errors->any())
        <div class="tw-flex tw-items-start tw-gap-3 tw-rounded-stub tw-border tw-border-danger-200 tw-bg-danger-50 tw-px-4 tw-py-3 tw-text-danger-800 dark:tw-border-danger-900 dark:tw-bg-danger-950/40 dark:tw-text-danger-200">
            <x-lucide-circle-alert class="tw-mt-0.5 tw-h-4 tw-w-4 tw-shrink-0" />
            <p class="tw-text-sm tw-font-semibold">
                {{ __('Please review the highlighted fields before continuing.') }}
            </p>
        </div>
    @endif

    <div class="tw-grid tw-gap-3 sm:tw-grid-cols-3" role="tablist" aria-label="{{ __('Event form steps') }}">
        <button type="button" role="tab" @click="step = 1"
            :aria-selected="step === 1 ? 'true' : 'false'"
            :class="step === 1
                ? 'tw-border-accent-500 tw-bg-accent-50 tw-text-accent-800 dark:tw-border-accent-400 dark:tw-bg-accent-900/30 dark:tw-text-accent-200'
                : 'tw-border-neutral-200 tw-bg-neutral-0 tw-text-ink-800 hover:tw-border-accent-400 dark:tw-border-neutral-700 dark:tw-bg-ink-900 dark:tw-text-neutral-100'"
            class="tw-flex tw-items-center tw-gap-3 tw-rounded-stub tw-border tw-px-4 tw-py-3 tw-text-left tw-transition focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
            <span class="tw-flex tw-h-8 tw-w-8 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-pill tw-bg-neutral-100 tw-text-xs tw-font-bold dark:tw-bg-neutral-800">
                1
            </span>
            <span class="tw-flex tw-flex-col">
                <span class="tw-text-sm tw-font-bold">{{ __('Event details') }}</span>
                <span class="tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Cover, schedule and content') }}</span>
            </span>
        </button>
        <button type="button" role="tab" @click="step = 2"
            :aria-selected="step === 2 ? 'true' : 'false'"
            :class="step === 2
                ? 'tw-border-accent-500 tw-bg-accent-50 tw-text-accent-800 dark:tw-border-accent-400 dark:tw-bg-accent-900/30 dark:tw-text-accent-200'
                : 'tw-border-neutral-200 tw-bg-neutral-0 tw-text-ink-800 hover:tw-border-accent-400 dark:tw-border-neutral-700 dark:tw-bg-ink-900 dark:tw-text-neutral-100'"
            class="tw-flex tw-items-center tw-gap-3 tw-rounded-stub tw-border tw-px-4 tw-py-3 tw-text-left tw-transition focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
            <span class="tw-flex tw-h-8 tw-w-8 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-pill tw-bg-neutral-100 tw-text-xs tw-font-bold dark:tw-bg-neutral-800">
                2
            </span>
            <span class="tw-flex tw-flex-col">
                <span class="tw-text-sm tw-font-bold">{{ __('Location & access') }}</span>
                <span class="tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('How guests join and find it') }}</span>
            </span>
        </button>
        <button type="button" role="tab" @click="step = 3"
            :aria-selected="step === 3 ? 'true' : 'false'"
            :class="step === 3
                ? 'tw-border-accent-500 tw-bg-accent-50 tw-text-accent-800 dark:tw-border-accent-400 dark:tw-bg-accent-900/30 dark:tw-text-accent-200'
                : 'tw-border-neutral-200 tw-bg-neutral-0 tw-text-ink-800 hover:tw-border-accent-400 dark:tw-border-neutral-700 dark:tw-bg-ink-900 dark:tw-text-neutral-100'"
            class="tw-flex tw-items-center tw-gap-3 tw-rounded-stub tw-border tw-px-4 tw-py-3 tw-text-left tw-transition focus:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
            <span class="tw-flex tw-h-8 tw-w-8 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-pill tw-bg-neutral-100 tw-text-xs tw-font-bold dark:tw-bg-neutral-800">
                3
            </span>
            <span class="tw-flex tw-flex-col">
                <span class="tw-text-sm tw-font-bold">{{ __('Tickets & pricing') }}</span>
                <span class="tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Set prices after saving') }}</span>
            </span>
        </button>
    </div>

    <section x-show="step === 1" x-cloak role="tabpanel" class="tw-grid tw-gap-6">
        <div class="tw-grid tw-gap-5 lg:tw-grid-cols-5">
            <div class="lg:tw-col-span-2">
                <label for="image-upload" class="tw-mb-2 tw-block tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
                    {{ __('Image') }}
                </label>
                <div id="image-preview"
                    class="image-preview tw-flex tw-h-52 tw-items-center tw-justify-center tw-rounded-card tw-border tw-border-dashed tw-border-neutral-300 tw-bg-neutral-50 tw-bg-cover tw-bg-center dark:tw-border-neutral-700 dark:tw-bg-ink-950"
                    @if ($isEdit) style="background-image: url('{{ url('images/upload/'.$event->image) }}')" @endif>
                    <label for="image-upload" id="image-label"
                        class="tw-flex tw-h-11 tw-w-11 tw-cursor-pointer tw-items-center tw-justify-center tw-rounded-pill tw-bg-neutral-0 tw-text-accent-600 tw-shadow-card hover:tw-text-accent-500 dark:tw-bg-neutral-800 dark:tw-text-accent-300">
                        <x-lucide-plus class="tw-h-5 tw-w-5" />
                    </label>
                    <input type="file" name="image" id="image-upload" class="tw-sr-only" />
                </div>
                @error('image')
                    <p class="tw-mt-1 tw-text-xs tw-font-medium tw-text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="tw-grid tw-gap-5 tw-content-start lg:tw-col-span-3">
                <x-input name="name" :label="__('Name')" :value="$isEdit ? $event->name : ''"
                    :placeholder="__('Name')" />

                <x-select name="category_id" :label="__('Category')" class="select2" required>
                    <option value="">{{ __('Select Category') }}</option>
                    @foreach ($category as $item)
                        <option value="{{ $item->id }}"
                            {{ old('category_id', $isEdit ? $event->category_id : '') == $item->id ? 'selected' : '' }}>
                            {{ $item->name }}
                        </option>
                    @endforeach
                </x-select>
            </div>
        </div>

        <div class="tw-grid tw-gap-5 tw-border-t tw-border-neutral-100 tw-pt-5 dark:tw-border-neutral-800">
            <div class="tw-flex tw-items-center tw-gap-2">
                <x-lucide-calendar-days class="tw-h-4 tw-w-4 tw-text-accent-600 dark:tw-text-accent-300" />
                <h3 class="tw-text-sm tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Schedule') }}</h3>
            </div>
            <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                <x-input name="start_time" id="start_time" class="date" :label="__('Start Time')"
                    :value="$isEdit && $event->start_time ? $event->start_time->format('Y-m-d h:i A') : ''"
                    :placeholder="__('Choose Start time')" data-flatpickr="datetime" autocomplete="off" />
                <x-input name="end_time" id="end_time" class="date" :label="__('End Time')"
                    :value="$isEdit && $event->end_time ? $event->end_time->format('Y-m-d h:i A') : ''"
                    :placeholder="__('Choose End time')" data-flatpickr="datetime" autocomplete="off" />
            </div>
        </div>

        <div class="tw-grid tw-gap-5 tw-border-t tw-border-neutral-100 tw-pt-5 dark:tw-border-neutral-800">
            <div class="tw-flex tw-items-center tw-gap-2">
                <x-lucide-info class="tw-h-4 tw-w-4 tw-text-accent-600 dark:tw-text-accent-300" />
                <h3 class="tw-text-sm tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Operations') }}</h3>
            </div>
            @if (Auth::user()->hasRole('admin'))
                <x-select name="user_id" id="org-for-event" :label="__('Organization')" class="select2" required>
                    <option value="">{{ __('Choose Organization') }}</option>
                    @foreach ($users as $item)
                        <option value="{{ $item->id }}"
                            {{ old('user_id', $isEdit ? $event->user_id : '') == $item->id ? 'selected' : '' }}>
                            {{ $item->first_name.' '.$item->last_name }}
                        </option>
                    @endforeach
                </x-select>
            @endif

            <x-select name="scanner_id" :label="__('Scanner')" class="scanner_id select2" required>
                <option value="">{{ __('Choose Scanner') }}</option>
                @foreach ($scanner as $item)
                    <option value="{{ $item->id }}"
                        {{ old('scanner_id', $isEdit ? $event->scanner_id : '') == $item->id ? 'selected' : '' }}>
                        {{ $item->first_name.' '.$item->last_name }}
                    </option>
                @endforeach
            </x-select>

            <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                <x-input name="people" id="people" type="number" min="1" :label="__('Maximum people will join in this event')"
                    :value="$isEdit ? $event->people : ''" :placeholder="__('Maximum people will join in this event')" required />
                <x-select name="status" :label="__('status')" class="select2">
                    <option value="1" {{ old('status', $isEdit ? (string) $event->status : '1') == '1' ? 'selected' : '' }}>
                        {{ __('Active') }}
                    </option>
                    <option value="0" {{ old('status', $isEdit ? (string) $event->status : '1') == '0' ? 'selected' : '' }}>
                        {{ __('Inactive') }}
                    </option>
                </x-select>
            </div>
        </div>

        <div class="tw-grid tw-gap-5 tw-border-t tw-border-neutral-100 tw-pt-5 dark:tw-border-neutral-800">
            <div class="tw-flex tw-items-center tw-gap-2">
                <x-lucide-info class="tw-h-4 tw-w-4 tw-text-accent-600 dark:tw-text-accent-300" />
                <h3 class="tw-text-sm tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">{{ __('Content') }}</h3>
            </div>
            <x-input name="tags" class="inputtags" :label="__('Tags')" :value="$isEdit ? $event->tags : ''" />
            <x-textarea name="description" class="textarea_editor" :label="__('Description')"
                :value="$isEdit ? $event->description : ''" :placeholder="__('Description')" />
        </div>

        <div class="tw-flex tw-justify-end tw-border-t tw-border-neutral-100 tw-pt-5 dark:tw-border-neutral-800">
            <x-button type="button" @click="step = 2" variant="secondary">
                {{ __('Next: Location & access') }}
                <x-lucide-arrow-right class="tw-h-4 tw-w-4" />
            </x-button>
        </div>
    </section>

    <section x-show="step === 2" x-cloak role="tabpanel" class="tw-grid tw-gap-6">
        <fieldset class="ds-field tw-grid tw-gap-3">
            <legend class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
                {{ __('Location Detail') }}
            </legend>
            <p class="-tw-mt-2 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                {{ __('Choose how attendees will join this event.') }}
            </p>
            <div class="selectgroup !tw-grid tw-gap-2 sm:tw-grid-cols-2">
                <label class="selectgroup-item !tw-ml-0 !tw-w-auto tw-relative tw-cursor-pointer"
                    @click="type = 'offline'">
                    <input type="radio" name="type" value="offline" x-model="type"
                        @change="type = $event.target.value"
                        class="selectgroup-input tw-peer tw-sr-only"
                        {{ $type == 'online' ? '' : 'checked' }}>
                    <span class="tw-flex tw-h-full tw-items-start tw-gap-3 tw-rounded-stub tw-border tw-border-neutral-200 tw-px-4 tw-py-3 tw-text-ink-800 tw-transition peer-checked:tw-border-accent-500 peer-checked:tw-bg-accent-50 peer-checked:tw-text-accent-800 peer-focus-visible:tw-ring-2 peer-focus-visible:tw-ring-accent-600 peer-focus-visible:tw-ring-offset-2 dark:tw-border-neutral-700 dark:tw-text-neutral-100 dark:peer-checked:tw-border-accent-400 dark:peer-checked:tw-bg-accent-900/30 dark:peer-checked:tw-text-accent-200">
                        <span class="tw-mt-0.5 tw-flex tw-h-8 tw-w-8 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-stub tw-bg-neutral-100 tw-text-neutral-500 dark:tw-bg-neutral-800 dark:tw-text-neutral-400">
                            <x-lucide-map-pin class="tw-h-4 tw-w-4" />
                        </span>
                        <span class="tw-flex tw-flex-col tw-gap-0.5">
                            <span class="tw-text-sm tw-font-semibold">{{ __('Venue') }}</span>
                            <span class="tw-text-xs tw-leading-5 tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Add a physical address and map coordinates.') }}</span>
                        </span>
                    </span>
                    <span aria-hidden="true"
                        class="tw-pointer-events-none tw-absolute tw-right-3 tw-top-3 tw-h-1.5 tw-w-1.5 tw-rounded-pill tw-bg-primary-500 tw-opacity-0 tw-transition peer-checked:tw-opacity-100"></span>
                </label>
                <label class="selectgroup-item !tw-ml-0 !tw-w-auto tw-relative tw-cursor-pointer"
                    @click="type = 'online'">
                    <input type="radio" name="type" value="online" x-model="type"
                        @change="type = $event.target.value"
                        class="selectgroup-input tw-peer tw-sr-only"
                        {{ $type == 'online' ? 'checked' : '' }}>
                    <span class="tw-flex tw-h-full tw-items-start tw-gap-3 tw-rounded-stub tw-border tw-border-neutral-200 tw-px-4 tw-py-3 tw-text-ink-800 tw-transition peer-checked:tw-border-accent-500 peer-checked:tw-bg-accent-50 peer-checked:tw-text-accent-800 peer-focus-visible:tw-ring-2 peer-focus-visible:tw-ring-accent-600 peer-focus-visible:tw-ring-offset-2 dark:tw-border-neutral-700 dark:tw-text-neutral-100 dark:peer-checked:tw-border-accent-400 dark:peer-checked:tw-bg-accent-900/30 dark:peer-checked:tw-text-accent-200">
                        <span class="tw-mt-0.5 tw-flex tw-h-8 tw-w-8 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-stub tw-bg-neutral-100 tw-text-neutral-500 dark:tw-bg-neutral-800 dark:tw-text-neutral-400">
                            <x-lucide-globe class="tw-h-4 tw-w-4" />
                        </span>
                        <span class="tw-flex tw-flex-col tw-gap-0.5">
                            <span class="tw-text-sm tw-font-semibold">{{ __('Online Event') }}</span>
                            <span class="tw-text-xs tw-leading-5 tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Meet attendees online without a venue address.') }}</span>
                        </span>
                    </span>
                    <span aria-hidden="true"
                        class="tw-pointer-events-none tw-absolute tw-right-3 tw-top-3 tw-h-1.5 tw-w-1.5 tw-rounded-pill tw-bg-primary-500 tw-opacity-0 tw-transition peer-checked:tw-opacity-100"></span>
                </label>
            </div>
            @error('type')
                <p class="tw-text-xs tw-font-medium tw-text-danger-600">{{ $message }}</p>
            @enderror
        </fieldset>

        <div x-show="type === 'online'" x-cloak
            class="tw-grid tw-gap-5 tw-rounded-stub tw-border-l-2 tw-border-accent-300 tw-bg-neutral-50 tw-p-4 dark:tw-border-accent-700 dark:tw-bg-ink-950/60">
            <div class="tw-flex tw-items-center tw-gap-2">
                <x-lucide-link class="tw-h-4 tw-w-4 tw-text-accent-600 dark:tw-text-accent-300" />
                <p class="tw-text-xs tw-font-semibold tw-text-ink-700 dark:tw-text-neutral-300">
                    {{ __('Online joining link') }}
                </p>
            </div>
            <x-input name="event_link" id="event_link" type="text" inputmode="url"
                :label="__('Event link')" :value="$onlineLink"
                :help="__('Required for online events. Attendees will see a Join button on the event page.')"
                placeholder="https://meet.example.com/your-event" />
        </div>

        <div class="location-detail {{ $type == 'online' ? 'tw-hidden' : '' }}"
            x-show="type !== 'online'" x-cloak>
            <div class="tw-grid tw-gap-5 tw-rounded-stub tw-border-l-2 tw-border-accent-300 tw-bg-neutral-50 tw-p-4 dark:tw-border-accent-700 dark:tw-bg-ink-950/60">
                <p class="tw-text-xs tw-font-semibold tw-text-ink-700 dark:tw-text-neutral-300">
                    {{ __('Venue details') }}
                </p>
                <x-input name="address" id="address" :label="__('Event Address')"
                    :value="$isEdit ? $event->address : ''" :placeholder="__('Event Address')" />
                <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                    <x-input name="lat" id="lat" :label="__('Latitude')" :value="$isEdit ? $event->lat : ''"
                        :placeholder="__('Latitude')" />
                    <x-input name="lang" id="lang" :label="__('Longitude')" :value="$isEdit ? $event->lang : ''"
                        :placeholder="__('Longitude')" />
                </div>
            </div>
        </div>

        <fieldset class="ds-field tw-grid tw-gap-3 tw-border-t tw-border-neutral-100 tw-pt-5 dark:tw-border-neutral-800">
            <legend class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
                {{ __('Visibility') }}
            </legend>
            <p class="-tw-mt-2 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
                {{ __('Choose who can find and book this event.') }}
            </p>
            <div class="selectgroup !tw-grid tw-gap-2 sm:tw-grid-cols-2">
                <label class="selectgroup-item !tw-ml-0 !tw-w-auto tw-relative tw-cursor-pointer">
                    <input type="radio" name="security" value="1"
                        class="custom-switch-input tw-peer tw-sr-only"
                        {{ $security == '1' ? 'checked' : '' }}>
                    <span class="tw-flex tw-h-full tw-items-start tw-gap-3 tw-rounded-stub tw-border tw-border-neutral-200 tw-px-4 tw-py-3 tw-text-ink-800 tw-transition peer-checked:tw-border-accent-500 peer-checked:tw-bg-accent-50 peer-checked:tw-text-accent-800 peer-focus-visible:tw-ring-2 peer-focus-visible:tw-ring-accent-600 peer-focus-visible:tw-ring-offset-2 dark:tw-border-neutral-700 dark:tw-text-neutral-100 dark:peer-checked:tw-border-accent-400 dark:peer-checked:tw-bg-accent-900/30 dark:peer-checked:tw-text-accent-200">
                        <span class="tw-mt-0.5 tw-flex tw-h-8 tw-w-8 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-stub tw-bg-neutral-100 tw-text-neutral-500 dark:tw-bg-neutral-800 dark:tw-text-neutral-400">
                            <x-lucide-eye class="tw-h-4 tw-w-4" />
                        </span>
                        <span class="tw-flex tw-flex-col tw-gap-0.5">
                            <span class="tw-text-sm tw-font-semibold">{{ __('Public') }}</span>
                            <span class="tw-text-xs tw-leading-5 tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Listed for everyone to find and book.') }}</span>
                        </span>
                    </span>
                    <span aria-hidden="true"
                        class="tw-pointer-events-none tw-absolute tw-right-3 tw-top-3 tw-h-1.5 tw-w-1.5 tw-rounded-pill tw-bg-primary-500 tw-opacity-0 tw-transition peer-checked:tw-opacity-100"></span>
                </label>
                <label class="selectgroup-item !tw-ml-0 !tw-w-auto tw-relative tw-cursor-pointer">
                    <input type="radio" name="security" value="0"
                        class="custom-switch-input tw-peer tw-sr-only"
                        {{ $security == '0' ? 'checked' : '' }}>
                    <span class="tw-flex tw-h-full tw-items-start tw-gap-3 tw-rounded-stub tw-border tw-border-neutral-200 tw-px-4 tw-py-3 tw-text-ink-800 tw-transition peer-checked:tw-border-accent-500 peer-checked:tw-bg-accent-50 peer-checked:tw-text-accent-800 peer-focus-visible:tw-ring-2 peer-focus-visible:tw-ring-accent-600 peer-focus-visible:tw-ring-offset-2 dark:tw-border-neutral-700 dark:tw-text-neutral-100 dark:peer-checked:tw-border-accent-400 dark:peer-checked:tw-bg-accent-900/30 dark:peer-checked:tw-text-accent-200">
                        <span class="tw-mt-0.5 tw-flex tw-h-8 tw-w-8 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-stub tw-bg-neutral-100 tw-text-neutral-500 dark:tw-bg-neutral-800 dark:tw-text-neutral-400">
                            <x-lucide-lock class="tw-h-4 tw-w-4" />
                        </span>
                        <span class="tw-flex tw-flex-col tw-gap-0.5">
                            <span class="tw-text-sm tw-font-semibold">{{ __('Private') }}</span>
                            <span class="tw-text-xs tw-leading-5 tw-text-neutral-500 dark:tw-text-neutral-400">{{ __('Hidden from public listings and search.') }}</span>
                        </span>
                    </span>
                    <span aria-hidden="true"
                        class="tw-pointer-events-none tw-absolute tw-right-3 tw-top-3 tw-h-1.5 tw-w-1.5 tw-rounded-pill tw-bg-primary-500 tw-opacity-0 tw-transition peer-checked:tw-opacity-100"></span>
                </label>
            </div>
        </fieldset>

        <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3 tw-border-t tw-border-neutral-100 tw-pt-5 dark:tw-border-neutral-800">
            <x-button type="button" variant="ghost" @click="step = 1">
                <x-lucide-arrow-left class="tw-h-4 tw-w-4" />
                {{ __('Back to details') }}
            </x-button>
            <x-button type="button" @click="step = 3">
                {{ __('Next: Tickets & pricing') }}
                <x-lucide-arrow-right class="tw-h-4 tw-w-4" />
            </x-button>
        </div>
    </section>

    <section x-show="step === 3" x-cloak role="tabpanel" class="tw-grid tw-gap-6">
        <div class="tw-grid tw-gap-5 tw-rounded-card tw-border tw-border-accent-200 tw-bg-accent-50 tw-p-5 dark:tw-border-accent-800 dark:tw-bg-accent-900/20">
            <div class="tw-flex tw-items-center tw-gap-3">
                <span class="tw-flex tw-h-10 tw-w-10 tw-shrink-0 tw-items-center tw-justify-center tw-rounded-pill tw-bg-neutral-0 tw-text-accent-600 dark:tw-bg-ink-900 dark:tw-text-accent-300">
                    <x-lucide-ticket class="tw-h-5 tw-w-5" />
                </span>
                <div>
                    <h3 class="tw-text-base tw-font-bold tw-text-ink-900 dark:tw-text-neutral-50">
                        {{ __('Ticket prices are set on tickets, not on the event') }}
                    </h3>
                    <p class="tw-mt-0.5 tw-text-sm tw-text-neutral-600 dark:tw-text-neutral-300">
                        {{ __('Save this event first, then add a free or paid ticket and enter its price. Attendees see the cheapest active ticket price on the public listing.') }}
                    </p>
                </div>
            </div>

            @if ($isEdit)
                <a href="{{ url($event->id.'/'.preg_replace('/\s+/', '-', $event->name).'/tickets') }}"
                    class="tw-inline-flex tw-h-10 tw-w-fit tw-items-center tw-gap-2 tw-rounded-pill tw-bg-primary-500 tw-px-4 tw-text-sm tw-font-bold tw-text-ink-950 tw-transition hover:tw-bg-primary-400 focus-visible:tw-outline-none focus-visible:tw-ring-2 focus-visible:tw-ring-accent-600">
                    <x-lucide-ticket class="tw-h-4 tw-w-4" />
                    {{ __('Open ticket manager') }}
                </a>
            @endif
        </div>

        <div class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3 tw-border-t tw-border-neutral-100 tw-pt-5 dark:tw-border-neutral-800">
            <x-button type="button" variant="ghost" @click="step = 2">
                <x-lucide-arrow-left class="tw-h-4 tw-w-4" />
                {{ __('Back to access') }}
            </x-button>
            <div class="tw-flex tw-flex-wrap tw-items-center tw-gap-2">
                <x-button type="submit" variant="secondary" name="after_save" value="tickets">
                    {{ $isEdit ? __('Update & manage tickets') : __('Create & set ticket prices') }}
                </x-button>
                <x-button type="submit">
                    {{ $isEdit ? __('Update event') : __('Create event') }}
                </x-button>
            </div>
        </div>
    </section>
</form>
