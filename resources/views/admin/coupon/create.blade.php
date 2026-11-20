@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Add Coupon')" :parent="__('Coupon')" url="coupon" />

    <x-card :title="__('Add Coupon')" :subtitle="__('Set the discount, event and validity period.')">
        <form method="post" action="{{ url('coupon') }}" enctype="multipart/form-data" class="tw-grid tw-gap-6">
            @csrf

            <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                <x-input name="name" :label="__('Name')" :placeholder="__('Name')" :value="old('name')" />

                <x-select name="event_id" :label="__('Event')">
                    <option value="">{{ __('Select Event') }}</option>
                    @foreach ($event as $item)
                        <option value="{{ $item->id }}" {{ $item->id == old('event_id') ? 'selected' : '' }}>{{ $item->name }}</option>
                    @endforeach
                </x-select>
            </div>

            <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                <x-input name="discount" :label="__('Discount (In percentage)')" type="number" min="1" max="100"
                    :placeholder="__('Discount')" :value="old('discount')" />
                <x-input name="max_use" :label="__('Maximum Use')" type="number" min="1"
                    :placeholder="__('Maximum Use')" :value="old('max_use')" />
            </div>

            <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                <x-input name="start_date" :label="__('Start Date')" :placeholder="__('Start Date')"
                    :value="old('start_date')" id="start_date" class="date" />
                <x-input name="end_date" :label="__('End Date')" :placeholder="__('End Date')"
                    :value="old('end_date')" id="end_date" class="date" />
            </div>

            <x-textarea name="description" :label="__('Description')" :placeholder="__('Description')"
                :value="old('description')" />

            <x-select name="status" :label="__('status')">
                <option value="1" {{ old('status', '1') == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
            </x-select>

            <div>
                <x-button type="submit">{{ __('Submit') }}</x-button>
            </div>
        </form>
    </x-card>
@endsection
