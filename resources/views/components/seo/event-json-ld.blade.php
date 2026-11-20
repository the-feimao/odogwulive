@props([
    'event',
    'url' => null,
    'image' => null,
    'organization' => null,
    'tickets' => null,
    'currency' => null,
])

@php
    $base = rtrim(config('brand.canonical_url', config('app.url')), '/');

    $absolute = function ($value) use ($base) {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (preg_match('/^https?:\/\//i', $value)) {
            return $value;
        }
        if (str_starts_with($value, '/')) {
            return $base.$value;
        }

        return url('images/upload/'.$value);
    };

    $images = collect([$image, $event->image])
        ->merge(array_filter(explode(',', (string) $event->gallery)))
        ->filter()
        ->map($absolute)
        ->filter()
        ->unique()
        ->values()
        ->all();

    $isOnline = $event->type === 'online';

    $location = $isOnline
        ? [
            '@type' => 'VirtualLocation',
            'url' => $url,
        ]
        : array_filter([
            '@type' => 'Place',
            'name' => $event->address ?: config('brand.location'),
            'address' => $event->address ? [
                '@type' => 'PostalAddress',
                'streetAddress' => $event->address,
            ] : null,
            'geo' => (is_numeric($event->lat) && is_numeric($event->lang)) ? [
                '@type' => 'GeoCoordinates',
                'latitude' => (float) $event->lat,
                'longitude' => (float) $event->lang,
            ] : null,
        ]);

    $offers = collect($tickets ?: [])
        ->filter(function ($ticket) {
            $available = $ticket->available_qty ?? $ticket->quantity ?? 1;

            return (int) ($ticket->status ?? 1) === 1 && (float) $available > 0;
        })
        ->map(function ($ticket) use ($url, $currency) {
            $price = $ticket->type === 'free' ? 0 : (float) $ticket->price;

            return [
                '@type' => 'Offer',
                'name' => $ticket->name,
                'url' => $url,
                'price' => number_format($price, 2, '.', ''),
                'priceCurrency' => $currency ?: config('brand.currency', 'NGN'),
                'availability' => 'https://schema.org/InStock',
            ];
        })
        ->values()
        ->all();

    $organizer = $organization ? [
        '@type' => 'Organization',
        'name' => $organization->name ?: trim($organization->first_name.' '.$organization->last_name),
        'url' => $base.'/organization/'.$organization->id.'/'.\Illuminate\Support\Str::slug((string) ($organization->name ?: 'organizer')),
    ] : null;

    $jsonLd = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Event',
        'name' => $event->name,
        'description' => \Illuminate\Support\Str::limit(strip_tags((string) $event->description), 500, ''),
        'url' => $url,
        'startDate' => optional($event->start_time)->toIso8601String(),
        'endDate' => optional($event->end_time)->toIso8601String(),
        'eventAttendanceMode' => $isOnline
            ? 'https://schema.org/OnlineEventAttendanceMode'
            : 'https://schema.org/OfflineEventAttendanceMode',
        'eventStatus' => 'https://schema.org/EventScheduled',
        'image' => $images,
        'location' => $location,
        'organizer' => $organizer,
        'offers' => $offers,
        'keywords' => trim((string) $event->tags) !== '' ? $event->tags : null,
    ]);
@endphp

<script type="application/ld+json">
{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE) !!}
</script>
