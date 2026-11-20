@php
    $setting = App\Models\Setting::find(1);
@endphp
<!DOCTYPE html>
<html lang="en" @if (session('direction') == 'rtl') dir="rtl" @endif>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
    <title>{{ $setting->app_name }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ url('images/upload/'.$setting->favicon) }}" rel="icon" type="image/png">
    @vite('resources/css/app.css')
    <style>
        :root {
            --primary_color: {{ $setting->primary_color }};
            --light_primary_color: {{ $setting->primary_color }}1a;
            --middle_light_primary_color: {{ $setting->primary_color }}85;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
    <script>
        window.print();
    </script>
</head>

<body class="tw-min-h-screen tw-bg-neutral-0 tw-p-6 tw-font-sans tw-text-ink-900 md:tw-p-10">
    <main class="invoice invoice-print tw-mx-auto tw-max-w-4xl">
        <header class="tw-flex tw-flex-wrap tw-items-center tw-justify-between tw-gap-3 tw-border-b tw-border-neutral-200 tw-pb-4">
            <div>
                <h1 class="tw-text-2xl tw-font-extrabold tw-tracking-tight">
                    {{ __('Order') }} <span class="tw-font-mono">{{ $order->order_id }}</span>
                </h1>
                <p class="tw-mt-1 tw-text-sm tw-text-neutral-500">{{ $setting->app_name }}</p>
            </div>
        </header>

        <div class="tw-mt-6 tw-grid tw-gap-6 md:tw-grid-cols-2">
            <div>
                <p class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500">{{ __('Event') }}:</p>
                <div class="tw-mt-2 tw-flex tw-items-center tw-gap-3">
                    <img src="{{ url('images/upload/'.$order->event->image) }}" alt="{{ $order->event->name }}"
                        class="tw-h-12 tw-w-12 tw-rounded-stub tw-object-cover" width="50" height="50">
                    <div>
                        <p class="tw-font-semibold">{{ $order->event->name }}</p>
                        <p class="tw-mt-0.5 tw-text-sm tw-text-neutral-500">
                            {{ $order->event->start_time->format('l').', '.$order->event->start_time->format('d M Y') }}
                        </p>
                    </div>
                </div>
            </div>
            <div class="md:tw-text-right">
                <p class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500">{{ __('Organizer') }}:</p>
                <address class="tw-mt-2 tw-not-italic tw-text-sm">
                    {{ $order->organization->first_name.' '.$order->organization->last_name }}<br>
                    {{ $order->organization->email }}<br>
                    {{ $order->organization->country }}
                </address>
            </div>
        </div>

        <div class="tw-mt-6 tw-grid tw-gap-6 md:tw-grid-cols-2">
            <div>
                <p class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500">{{ __('Attendee') }}:</p>
                <address class="tw-mt-2 tw-not-italic tw-text-sm">
                    {{ $order->customer->name.' '.$order->customer->last_name }}<br>
                    {{ $order->customer->email }}
                </address>
            </div>
            <div class="md:tw-text-right">
                <p class="tw-text-xs tw-font-semibold tw-uppercase tw-tracking-wide tw-text-neutral-500">{{ __('Order Date') }}:</p>
                <p class="tw-mt-2 tw-text-sm">{{ $order->created_at->format('d F, Y') }}</p>
            </div>
        </div>

        <section class="tw-mt-8">
            <h2 class="tw-mb-3 tw-text-base tw-font-bold">{{ __('Order Summary') }}</h2>
            <div class="tw-overflow-x-auto">
                <table class="tw-min-w-full tw-text-left tw-text-sm">
                    <thead>
                        <tr class="tw-border-b tw-border-neutral-200 tw-text-xs tw-uppercase tw-tracking-wide tw-text-neutral-500">
                            <th class="tw-px-3 tw-py-3">#</th>
                            <th class="tw-px-3 tw-py-3 tw-text-center">{{ __('Ticket Name') }}</th>
                            <th class="tw-px-3 tw-py-3 tw-text-center">{{ __('Ticket Number') }}</th>
                            <th class="tw-px-3 tw-py-3 tw-text-center">{{ __('Price') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->ticket_data as $item)
                            <tr class="tw-border-b tw-border-neutral-100 last:tw-border-0">
                                <td class="tw-px-3 tw-py-3">{{ $loop->iteration }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-center">{{ $order->ticket->name }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-center tw-font-mono tw-text-xs">{{ $item->ticket_number }}</td>
                                <td class="tw-px-3 tw-py-3 tw-text-center">{{ $currency.$order->ticket->price }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <div class="tw-mt-8 tw-grid tw-gap-6 md:tw-grid-cols-3">
            <div class="md:tw-col-span-2">
                <h2 class="tw-text-base tw-font-bold">{{ __('Payment Method') }}</h2>
                <address class="tw-mt-2 tw-not-italic tw-text-sm">
                    <strong>{{ $order->payment_type }}</strong><br>
                    @if ($order->payment_type != 'FREE')
                        <span class="tw-mt-1 tw-inline-block tw-rounded-pill tw-px-2.5 tw-py-1 tw-text-xs tw-font-semibold {{ $order->payment_status == 1 ? 'tw-bg-success-100 tw-text-success-700' : 'tw-bg-warning-100 tw-text-warning-700' }}">
                            {{ $order->payment_status == 1 ? 'Paid' : 'waiting' }}
                        </span><br>
                        {{ __('Token:') }} {{ $order->payment_token == null ? '-' : $order->payment_token }}<br>
                    @endif
                </address>
            </div>

            <div class="tw-space-y-2 md:tw-text-right">
                <div class="tw-flex tw-items-center tw-justify-between tw-gap-4 tw-text-sm">
                    <span class="tw-font-semibold tw-text-neutral-600">{{ __('Subtotal') }}</span>
                    <span>{{ $currency.($order->payment + $order->coupon_discount - $order->tax) }}</span>
                </div>
                <div class="tw-flex tw-items-center tw-justify-between tw-gap-4 tw-text-sm">
                    <span class="tw-font-semibold tw-text-neutral-600">{{ __('Coupon Discount') }}</span>
                    <span>(-) {{ $currency.$order->coupon_discount }}</span>
                </div>
                @foreach ($order->tax_data as $item)
                    <div class="tw-flex tw-items-center tw-justify-between tw-gap-4 tw-text-sm">
                        <span class="tw-font-semibold tw-text-neutral-600">{{ $item->taxName }}</span>
                        <span>(+) {{ $currency.$item->price }}</span>
                    </div>
                @endforeach
                <div class="tw-flex tw-items-center tw-justify-between tw-gap-4 tw-border-t tw-border-neutral-200 tw-pt-3">
                    <span class="tw-font-bold">{{ __('Total') }}</span>
                    <span class="tw-text-xl tw-font-extrabold">{{ $currency.$order->payment }}</span>
                </div>
            </div>
        </div>
    </main>
</body>

</html>
