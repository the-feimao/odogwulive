@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Edit Coupon')" :parent="__('Coupon')" url="coupon" />

    <x-card :title="__('Edit Coupon')" :subtitle="__('Update the coupon details or its validity period.')">
        <form method="post" action="{{ url('coupon/'.$coupon->id) }}" enctype="multipart/form-data" class="tw-grid tw-gap-6">
            @csrf
            @method('PUT')

            <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                <x-input name="name" :label="__('Name')" :placeholder="__('Name')" :value="$coupon->name" />

                <x-select name="event_id" :label="__('Event')">
                    <option value="">{{ __('Select Event') }}</option>
                    @foreach ($event as $item)
                        <option value="{{ $item->id }}" {{ $item->id == $coupon->event_id ? 'selected' : '' }}>{{ $item->name }}</option>
                    @endforeach
                </x-select>
            </div>

            <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                <x-input name="discount" :label="__('Discount (In percentage)')" type="number" min="1" max="100"
                    :placeholder="__('Discount')" :value="$coupon->discount" />
                <x-input name="max_use" :label="__('Maximum Use')" type="number" min="1"
                    :placeholder="__('Maximum Use')" :value="$coupon->max_use" />
            </div>

            <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                <x-input name="start_date" :label="__('Start Date')" :placeholder="__('Start Date')"
                    :value="$coupon->start_date" id="start_date" class="date" />
                <x-input name="end_date" :label="__('End Date')" :placeholder="__('End Date')"
                    :value="$coupon->end_date" id="end_date" class="date" />
            </div>

            <x-textarea name="description" :label="__('Description')" :placeholder="__('Description')"
                :value="$coupon->description" />

            <x-select name="status" :label="__('status')">
                <option value="1" {{ old('status', $coupon->status) == '1' ? 'selected' : '' }}>{{ __('Active') }}</option>
                <option value="0" {{ old('status', $coupon->status) == '0' ? 'selected' : '' }}>{{ __('Inactive') }}</option>
            </x-select>

            <div>
                <x-button type="submit">{{ __('Submit') }}</x-button>
            </div>
        </form>
    </x-card>
@endsection
