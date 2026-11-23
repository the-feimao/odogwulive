{{--
    The license screen must stay inside the master layout's content-license
    yield so it remains reachable while the license is inactive. Only the
    presentation is wrapped here; the license gate itself is unchanged.
--}}
@extends('master')

@section('content-license')
    {{-- The legacy admin shell keeps its native light styling; the public
         theme preference is intentionally not applied here. --}}
    <div class="ds ds-admin tw-mx-auto tw-max-w-content tw-space-y-5 tw-px-4 tw-py-6 md:tw-px-6 lg:tw-px-8">
        @yield('admin-license-content')
    </div>
@endsection
