<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Models\Ticket;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class EventListingComposer
{
    /**
     * Adds shared listing data without changing the existing controller
     * methods or the variables they already pass to these views.
     */
    public function compose(View $view): void
    {
        $events = $view->getData()['events'] ?? collect();
        $eventIds = $events instanceof Collection ? $events->pluck('id') : collect($events)->pluck('id');

        $eventPrices = Ticket::whereIn('event_id', $eventIds)
            ->where([
                ['status', 1],
                ['is_deleted', 0],
            ])
            ->select('event_id')
            ->selectRaw('MIN(price) as min_price')
            ->groupBy('event_id')
            ->get()
            ->keyBy('event_id');

        $view->with([
            'eventPrices' => $eventPrices,
            'listingCategories' => Category::where('status', 1)
                ->orderBy('name')
                ->get(),
        ]);
    }
}
