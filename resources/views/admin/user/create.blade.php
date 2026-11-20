@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Add User')" :parent="__('Users')" url="users" />

    <x-flash-messages />

    <x-card :title="__('Add User')" :subtitle="__('Create a staff or organization account and assign its roles.')">
        <form method="post" action="{{ url('users') }}" class="tw-grid tw-gap-6">
            @csrf

            <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                <x-input name="first_name" :label="__('First Name')" :placeholder="__('First Name')" />
                <x-input name="last_name" :label="__('Last Name')" :placeholder="__('Last Name')" />
            </div>

            <x-input name="email" type="email" :label="__('Email')" :placeholder="__('Email')" />
            <x-input name="phone" :label="__('Phone')" :placeholder="__('Phone')" />
            <x-input name="password" type="password" :label="__('Password')" :placeholder="__('Password')" />

            <x-select name="roles[]" :label="__('Roles')" id="role" class="select2" multiple="multiple">
                @foreach ($roles as $role)
                    <option value="{{ $role['id'] }}">{{ $role['name'] }}</option>
                @endforeach
            </x-select>

            <div id="org" class="display-none">
                <x-select name="organization" :label="__('Organization')" class="select2">
                    @foreach ($orgs as $org)
                        <option value="{{ $org['id'] }}">{{ $org['first_name'].' '.$org['last_name'] }}</option>
                    @endforeach
                </x-select>
            </div>

            <div>
                <x-button type="submit">{{ __('Submit') }}</x-button>
            </div>
        </form>
    </x-card>
@endsection
