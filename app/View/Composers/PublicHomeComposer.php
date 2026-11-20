<?php

namespace App\View\Composers;

use App\Models\Category;
use App\Models\Event;
use App\Models\Ticket;
use App\Support\RequestData;
use Carbon\Carbon;
use Illuminate\View\View;

class PublicHomeComposer
{
    public function __construct(private RequestData $requestData)
    {
    }

    public function compose(View $view): void
    {
        $setting = $this->requestData->setting();
        $timezone = $setting->timezone ?? config('app.timezone');
        $now = Carbon::now($timezone)->format('Y-m-d H:i:s');

        $activeEvents = Event::query()->where([
            ['status', 1],
            ['is_deleted', 0],
            ['event_status', 'Pending'],
            ['end_time', '>', $now],
        ]);

        $events = (clone $activeEvents)
            ->with([
                'category:id,name,image',
                'organization:id,name,first_name,last_name,image',
            ])
            ->orderBy('start_time')
            ->limit(6)
            ->get();

        // The Event model appends aggregate accessors that each run a query.
        // They are not needed on a listing page.
        $events->each->setAppends([]);

        $eventIds = $events->pluck('id');

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

        $categoryCounts = (clone $activeEvents)
            ->select('category_id')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        $categories = Category::where('status', 1)
            ->orderBy('name')
            ->limit(6)
            ->get();

        $organizers = $events->pluck('organization')
            ->filter()
            ->unique('id')
            ->take(3)
            ->values();

        $organizers->each->setAppends([]);

        $view->with([
            'publicEvents' => $events,
            'publicCategories' => $categories,
            'categoryCounts' => $categoryCounts,
            'eventPrices' => $eventPrices,
            'publicOrganizers' => $organizers,
        ]);
    }
}
