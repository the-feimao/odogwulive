{{--
    Intermediate admin layout. It extends the existing master so the license
    gate, navigation and legacy action scripts keep working unchanged, while
    migrated views get the Tailwind/Alpine design system inside the content area.
--}}
@extends('master')

@section('content')
    <x-theme-init />

    <div class="ds ds-admin tw-mx-auto tw-max-w-content tw-space-y-5 tw-px-4 tw-py-6 md:tw-px-6 lg:tw-px-8">
        @yield('admin-content')
    </div>
@endsection
