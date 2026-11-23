{{--
    Shared event create/edit form.

    Legacy hooks are intentionally preserved: `.event-form`, `#image-preview`,
    `#image-upload`, `#image-label`, `#start_time`, `#end_time`,
    `#org-for-event`, `.scanner_id`, `.location-detail`, `.inputtags` and
    `.textarea_editor`.
--}}
@php
    $isEdit = isset($event) && $event !== null;
    $type = old('type', $isEdit ? $event->type : 'offline');
    $security = $isEdit ? (string) $event->security : old('security', '1');
@endphp

<form method="post" class="event-form tw-grid tw-gap-6"
    action="{{ $isEdit ? route('events.update', [$event->id]) : url('events') }}"
    enctype="multipart/form-data">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="tw-grid tw-gap-6 lg:tw-grid-cols-2">
        <div class="ds-field tw-flex tw-flex-col tw-gap-2">
            <label for="image-upload" class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
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
                <p class="tw-text-xs tw-font-medium tw-text-danger-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="tw-grid tw-gap-5 tw-content-start">
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

    <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
        <x-input name="start_time" id="start_time" class="date" :label="__('Start Time')"
            :value="$isEdit && $event->start_time ? $event->start_time->format('Y-m-d h:i A') : ''"
            :placeholder="__('Choose Start time')" data-flatpickr="datetime" autocomplete="off" />
        <x-input name="end_time" id="end_time" class="date" :label="__('End Time')"
            :value="$isEdit && $event->end_time ? $event->end_time->format('Y-m-d h:i A') : ''"
            :placeholder="__('Choose End time')" data-flatpickr="datetime" autocomplete="off" />
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

    <x-input name="tags" class="inputtags" :label="__('Tags')" :value="$isEdit ? $event->tags : ''" />

    <x-textarea name="description" class="textarea_editor" :label="__('Description')"
        :value="$isEdit ? $event->description : ''" :placeholder="__('Description')" />

    <fieldset class="ds-field tw-grid tw-gap-3 tw-border-t tw-border-neutral-100 tw-pt-5 dark:tw-border-neutral-800">
        <legend class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
            {{ __('Location Detail') }}
        </legend>
        <p class="-tw-mt-2 tw-text-xs tw-text-neutral-500 dark:tw-text-neutral-400">
            {{ __('Choose how attendees will join this event.') }}
        </p>
        <div class="selectgroup !tw-grid tw-gap-2 sm:tw-grid-cols-2">
            <label class="selectgroup-item !tw-ml-0 !tw-w-auto tw-relative tw-cursor-pointer">
                <input type="radio" name="type" value="offline"
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
            <label class="selectgroup-item !tw-ml-0 !tw-w-auto tw-relative tw-cursor-pointer">
                <input type="radio" name="type" value="online"
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
    </fieldset>

    <div class="location-detail {{ $type == 'online' ? 'tw-hidden' : '' }}">
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

    <div>
        <x-button type="submit">{{ __('Submit') }}</x-button>
    </div>
</form>
