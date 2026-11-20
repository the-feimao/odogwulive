@extends('layouts.admin')

@section('admin-content')
    <x-admin-breadcrumb :title="__('Add Roles')" :parent="__('Roles')" url="roles" />

    <x-flash-messages />

    <x-card :title="__('Add Roles')" :subtitle="__('Give the role a name and choose the permissions it should have.')">
        <form method="post" action="{{ url('roles') }}" class="tw-grid tw-gap-6">
            @csrf

            <x-input name="name" :label="__('Name')" :placeholder="__('Name')" :value="old('name')" />

            <x-select name="permissions[]" :label="__('Permissions')" multiple class="select2"
                :help="__('Use Ctrl or Cmd to select more than one permission.')">
                @foreach ($permissions as $per)
                    <option value="{{ $per['id'] }}">{{ $per['name'] }}</option>
                @endforeach
            </x-select>

            <div>
                <x-button type="submit">{{ __('Submit') }}</x-button>
            </div>
        </form>
    </x-card>
@endsection
