<!DOCTYPE html>
<html lang="en">

<head>
    @php
        // RequestData is request-scoped and already used by the shared view
        // composer, so reuse it instead of querying settings on every line.
        $adminSetting = app(\App\Support\RequestData::class)->setting();
        $adminPaymentSetting = app(\App\Support\RequestData::class)->paymentSetting();
    @endphp
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>{{ $adminSetting?->app_name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-admin-theme-init />
    <!-- General CSS Files -->
    <link href="{{ url('images/upload/' . $adminSetting?->favicon) }}" rel="icon" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/admin-legacy.js'])

    {{-- PDF fonts and JSZip are only needed by the report tables. --}}
    @if (request()->is('admin-report/*', 'organization-report/*', 'view-settlement/*'))
        @vite('resources/js/admin-export.js')
    @endif

    {{-- Trix is only needed on blog forms. --}}
    @if (request()->is('blog/create', 'blog/*/edit'))
        @vite('resources/js/admin-editor.js')
    @endif

    <link rel="stylesheet" href="{{ url('admin/css/style.css') }}">
    <link rel="stylesheet" href="{{ url('admin/css/components.css') }}">
    <link rel="stylesheet" href="{{ url('admin/css/custom.css') }}">
    @if (session('direction') == 'rtl')
        <link rel="stylesheet" href="{{ url('admin/css/rtl.css') }}">
    @endif
</head>

<body>
    <?php $primary_color = $adminSetting?->primary_color ?? '#000000'; ?>

    <style>
        :root {
            --primary_color: <?php echo $primary_color; ?>;
            --light_primary_color: <?php echo $primary_color . '1a'; ?>;
            --middle_light_primary_color: <?php echo $primary_color . '85'; ?>;
        }

    </style>

    <input type="hidden" name="base_url" id="base_url" value="{{ url('/') }}">
    <div class="preload">
        <img src="{{ url('images/pre-loader.gif') }}">
    </div>
    <div id="app">
        @if (Auth::check())
            <input type="hidden" name="currency" id="currency" value="{{ $currency }}">
            <div class="main-wrapper">
                @include('admin.layout.header')


                @include('admin.layout.sidebar')
                <div class="main-content">
                    @if (Auth::user()->hasRole('admin'))
                        <?php $status = $adminSetting?->license_status; ?>
                        @if ($status == 1)
                            @yield('content')
                            @yield('content-license')
                        @else
                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    var url = window.location.origin + window.location.pathname;
                                    var a = $('#base_url').val() + '/license-setting';
                                    if (a != url) {
                                        setTimeout(() => {
                                            Swal.fire({
                                                title: 'Your License is deactivated!',
                                                icon: 'info',
                                                html: 'to get benefits of Odogwu fest please activate your license<br><br> ' +
                                                    '<a href="' + a +
                                                    '" style="background:#3085d6;color:#fff;padding:8px 10px;border-radius:5px;">Activate License</a>',
                                                showCloseButton: false,
                                                showCancelButton: false,
                                                showConfirmButton: false,
                                                focusConfirm: false,
                                                onClose: () => {
                                                    window.location.replace(a);
                                                }
                                            })
                                        }, 500);
                                    }
                                });
                            </script>

                            @yield('content-license')
                        @endif
                    @else
                        @yield('content')
                        @yield('content-license')
                    @endif
                </div>
                @include('admin.layout.footer')
            </div>
        @else
            @yield('content')
        @endif
    </div>

    <!-- General JS Scripts -->
    <?php
    $paypalClientId = $adminPaymentSetting?->paypalClientId ?? null;
    $cur = $adminSetting?->currency ?? 'USD';
    ?>
    @if (!empty($paypalClientId))
        <script src="https://www.paypal.com/sdk/js?client-id={{ $paypalClientId }}&currency={{ $cur }}"
                data-namespace="paypal_sdk"></script>
    @endif
    <script defer src="{{ url('admin/js/jquery-jvectormap.min.js') }}"></script>
    <script defer src="{{ url('admin/js/jquery-jvectormap-world-mill.js') }}"></script>
    <script defer src="{{ url('admin/js/jquery.uploadPreview.min.js') }}"></script>
    <script defer src="{{ url('admin/js/bootstrap-tagsinput.min.js') }}"></script>
    <script defer src="{{ url('admin/js/bootstrap-colorpicker.min.js') }}"></script>
    <script defer src="{{ url('admin/js/dropzone.js') }}"></script>
    <script defer src="{{ url('admin/js/charts.js') }}"></script>
    <script defer src="{{ url('admin/js/stisla.js') }}"></script>
    <script defer src="{{ url('admin/js/scripts.js') }}"></script>
    <script defer src="{{ url('admin/js/custom.js') }}"></script>
    <script defer src="{{ url('admin/js/myCustom.js') }}"></script>

</body>

</html>
