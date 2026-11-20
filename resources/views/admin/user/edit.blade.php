@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Edit User')" :parent="__('Users')" url="users" />

    <x-flash-messages />

    <x-card :title="__('Edit User')" :subtitle="__('Update the account details and role assignments.')">
        <form method="post" action="{{ route('users.update', [$user->id]) }}" class="tw-grid tw-gap-6">
            @csrf
            @method('PUT')

            <div class="tw-grid tw-gap-5 md:tw-grid-cols-2">
                <x-input name="first_name" :label="__('First Name')" :value="$user->first_name" />
                <x-input name="last_name" :label="__('Last Name')" :value="$user->last_name" />
            </div>

            <x-input name="email" type="email" :label="__('Email')" :value="$user->email" readonly />
            <x-input name="phone" :label="__('Phone')" :value="$user->phone" />

            <x-select name="roles[]" :label="__('Roles')" id="role" class="select2" multiple="multiple">
                @foreach ($roles as $role)
                    <option value="{{ $role['id'] }}"
                        {{ (in_array($role->id, old('roles', [])) || $user->roles->contains($role->id)) ? 'selected' : '' }}>
                        {{ $role['name'] }}
                    </option>
                @endforeach
            </x-select>

            <div id="org" class="{{ $user->org_id == null ? 'display-none' : '' }}">
                <x-select name="organization" :label="__('Organization')" class="select2">
                    @foreach ($orgs as $org)
                        <option value="{{ $org['id'] }}" {{ $user->org_id == $org['id'] ? 'selected' : '' }}>
                            {{ $org['first_name'].' '.$org['last_name'] }}
                        </option>
                    @endforeach
                </x-select>
            </div>

            <div>
                <x-button type="submit">{{ __('Submit') }}</x-button>
            </div>
        </form>
    </x-card>
@endsection
