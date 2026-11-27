@props([
    'event',
    'prices' => null,
    'currency' => null,
    'image' => null,
    'date' => null,
    'location' => null,
    'href' => null,
    'badge' => null,
    'favorite' => true,
])

@php
    $minPrice = $prices && $prices->has($event->id)
        ? $prices->get($event->id)->min_price
        : null;

    $priceLabel = $minPrice === null
        ? __('See details')
        : ((float) $minPrice === 0.0
            ? __('Free')
            : __('From').' '.trim((string) $currency).number_format((float) $minPrice));

    $image = $image ?? ($event->image ? url('images/upload/'.$event->image) : asset('web/bg/1.jpg'));
    $date = $date ?? $event->start_time?->format('D, M j · g:i A');
    $location = $location ?? ($event->type === 'online' ? __('Online') : $event->address);
    $href = $href ?? url('/event/'.$event->id.'/'.\Illuminate\Support\Str::slug($event->name));
    $badge = $badge ?? $event->category?->name;
@endphp

<div class="tw-relative">
    <x-event-card :event="$event" :image="$image" :date="$date" :location="$location"
        :price="$priceLabel" :href="$href" :badge="$badge" />

    @if ($favorite)
        <div class="tw-absolute tw-right-4 tw-top-4 tw-z-10">
            <x-favorite-button :id="$event->id" />
        </div>
    @endif
</div>
