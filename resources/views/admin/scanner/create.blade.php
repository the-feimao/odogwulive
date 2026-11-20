@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Add Scanner')" :parent="__('Scanner')" url="scanner" />

    <x-flash-messages />

    <x-card :title="__('Add Scanner')" :subtitle="__('Create an account the scanner can use to check in attendees.')">
        <form method="post" action="{{ url('scanner') }}" class="tw-grid tw-gap-6">
            @csrf

            <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                <x-input name="first_name" :label="__('First Name')" :placeholder="__('First Name')" :value="old('first_name')" />
                <x-input name="last_name" :label="__('Last Name')" :placeholder="__('Last Name')" :value="old('last_name')" />
            </div>

            <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                <x-input name="email" type="email" :label="__('Email')" :placeholder="__('Email')" :value="old('email')" />
                <x-input name="phone" :label="__('Phone')" :placeholder="__('Phone')" :value="old('phone')" />
            </div>

            <x-input name="password" type="password" :label="__('Password')" :placeholder="__('Password')" />

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
