{{--
    Standalone admin authentication wrapper. The master layout already omits
    the admin shell for guests; this layout only adds the design system.
--}}
@extends('master')

@section('content')
    {{-- The legacy admin shell keeps its native light styling; the public
         theme preference is intentionally not applied here. --}}
    <div class="ds ds-admin">
        @yield('admin-auth-content')
    </div>
@endsection
