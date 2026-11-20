{{--
    Standalone admin authentication wrapper. The master layout already omits
    the admin shell for guests; this layout only adds the design system.
--}}
@extends('master')

@section('content')
    <x-theme-init />

    <div class="ds ds-admin">
        @yield('admin-auth-content')
    </div>
@endsection
