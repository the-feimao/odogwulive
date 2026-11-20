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
            :value="$isEdit ? $event->start_time : ''" :placeholder="__('Choose Start time')" />
        <x-input name="end_time" id="end_time" class="date" :label="__('End Time')"
            :value="$isEdit ? $event->end_time : ''" :placeholder="__('Choose End time')" />
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

    <fieldset class="ds-field tw-flex tw-flex-col tw-gap-2">
        <legend class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">{{ __('Location Detail') }}</legend>
        <div class="selectgroup tw-flex tw-flex-wrap tw-gap-3">
            <label class="selectgroup-item tw-inline-flex tw-items-center tw-gap-2 tw-rounded-pill tw-border tw-border-neutral-200 tw-px-3 tw-py-2 tw-text-sm tw-font-semibold tw-text-ink-800 tw-transition hover:tw-border-accent-400 dark:tw-border-neutral-700 dark:tw-text-neutral-100">
                <input type="radio" name="type" value="offline" class="selectgroup-input tw-h-4 tw-w-4 tw-accent-primary-500"
                    {{ $type == 'online' ? '' : 'checked' }}>
                {{ __('Venue') }}
            </label>
            <label class="selectgroup-item tw-inline-flex tw-items-center tw-gap-2 tw-rounded-pill tw-border tw-border-neutral-200 tw-px-3 tw-py-2 tw-text-sm tw-font-semibold tw-text-ink-800 tw-transition hover:tw-border-accent-400 dark:tw-border-neutral-700 dark:tw-text-neutral-100">
                <input type="radio" name="type" value="online" class="selectgroup-input tw-h-4 tw-w-4 tw-accent-primary-500"
                    {{ $type == 'online' ? 'checked' : '' }}>
                {{ __('Online Event') }}
            </label>
        </div>
    </fieldset>

    <div class="location-detail tw-grid tw-gap-5 {{ $type == 'online' ? 'tw-hidden' : '' }}">
        <x-input name="address" id="address" :label="__('Event Address')"
            :value="$isEdit ? $event->address : ''" :placeholder="__('Event Address')" />
        <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
            <x-input name="lat" id="lat" :label="__('Latitude')" :value="$isEdit ? $event->lat : ''"
                :placeholder="__('Latitude')" />
            <x-input name="lang" id="lang" :label="__('Longitude')" :value="$isEdit ? $event->lang : ''"
                :placeholder="__('Longitude')" />
        </div>
    </div>

    <fieldset class="ds-field tw-flex tw-flex-col tw-gap-2">
        <legend class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">{{ __('Visibility') }}</legend>
        <div class="tw-flex tw-flex-wrap tw-gap-4">
            <label class="tw-inline-flex tw-items-center tw-gap-2 tw-text-sm tw-text-ink-800 dark:tw-text-neutral-100">
                <input type="radio" name="security" value="1" class="custom-switch-input tw-h-4 tw-w-4 tw-accent-primary-500"
                    {{ $security == '1' ? 'checked' : '' }}>
                {{ __('Public') }}
            </label>
            <label class="tw-inline-flex tw-items-center tw-gap-2 tw-text-sm tw-text-ink-800 dark:tw-text-neutral-100">
                <input type="radio" name="security" value="0" class="custom-switch-input tw-h-4 tw-w-4 tw-accent-primary-500"
                    {{ $security == '0' ? 'checked' : '' }}>
                {{ __('Private') }}
            </label>
        </div>
    </fieldset>

    <div>
        <x-button type="submit">{{ __('Submit') }}</x-button>
    </div>
</form>
