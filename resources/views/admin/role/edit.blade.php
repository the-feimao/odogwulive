@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Edit Roles')" :parent="__('Roles')" url="roles" />

    <x-flash-messages />

    <x-card :title="__('Edit Roles')" :subtitle="__('The role name is fixed; update its permissions below.')">
        <form method="post" action="{{ route('roles.update', [$role->id]) }}" class="tw-grid tw-gap-6">
            @csrf
            @method('PUT')

            <x-input name="name" :label="__('Name')" :placeholder="__('Name')" :value="$role->name" readonly />

            <x-select name="permissions[]" :label="__('Permissions')" multiple class="select2"
                :help="__('Use Ctrl or Cmd to select more than one permission.')">
                @foreach ($permissions as $per)
                    <option value="{{ $per['id'] }}"
                        {{ (in_array($per->id, old('permissions', [])) || $role->permissions->contains($per->id)) ? 'selected' : '' }}>
                        {{ $per['name'] }}
                    </option>
                @endforeach
            </x-select>

            <div>
                <x-button type="submit">{{ __('Submit') }}</x-button>
            </div>
        </form>
    </x-card>
@endsection
