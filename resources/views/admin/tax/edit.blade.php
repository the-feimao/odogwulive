@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Edit Tax')" :parent="__('Tax')" url="tax" />

    <x-card :title="__('Edit Tax')" :subtitle="__('Update the tax name, charge or status.')">
        <form method="post" action="{{ url('tax/'.$tax->id) }}" class="tw-grid tw-gap-6">
            @csrf
            @method('PUT')

            <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                <x-input name="name" :label="__('Name')" :placeholder="__('Name')" :value="$tax->name" />
                <x-input name="price" :label="__('Charges')" type="number" min="1" :placeholder="__('Charges')" :value="$tax->price" />
            </div>

            <x-select name="status" :label="__('status')">
                <option value="1" {{ old('status', $tax->status) == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                <option value="0" {{ old('status', $tax->status) == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
            </x-select>

            <div class="ds-field tw-flex tw-flex-col tw-gap-1.5">
                <div class="tw-flex tw-items-center tw-gap-3">
                    <input type="checkbox" name="allow_all_bill" value="1" id="allow_all_bill" tabindex="3"
                        {{ $tax->allow_all_bill == 1 ? 'checked' : '' }}
                        class="tw-h-4 tw-w-4 tw-rounded tw-border-neutral-300 tw-text-primary-600 focus:tw-ring-accent-600 dark:tw-border-neutral-600 dark:tw-bg-ink-900">
                    <label for="allow_all_bill" class="tw-text-sm tw-font-semibold tw-text-ink-800 dark:tw-text-neutral-100">
                        {{ __('Allow this tax in all bills') }}
                    </label>
                </div>
                @error('allow_all_bill')
                    <p class="tw-text-xs tw-font-medium tw-text-danger-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <x-button type="submit">{{ __('Submit') }}</x-button>
            </div>
        </form>
    </x-card>
@endsection
