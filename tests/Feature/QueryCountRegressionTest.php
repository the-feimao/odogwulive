<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class QueryCountRegressionTest extends TestCase
{
    /**
     * Count the queries made by one page request after the fixtures have been
     * created. Timing is intentionally not asserted because it is environment
     * dependent; the point is to catch per-row queries coming back.
     */
    private function queryCount(string $uri, ?callable $actingAs = null): int
    {
        if ($actingAs) {
            $this->actingAs($actingAs());
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        try {
            $this->get($uri)->assertOk();

            return count(DB::getQueryLog());
        } finally {
            DB::disableQueryLog();
        }
    }

    #[Test]
    public function hot_pages_avoid_per_row_queries(): void
    {
        Setting::find(1)->update(['license_status' => 1]);
        $admin = $this->admin();
        $organizer = $this->organizer();
        $scanner = $this->scannerUser($organizer);

        for ($i = 0; $i < 3; $i++) {
            $event = $this->event($organizer, [
                'name' => 'Query Count Event '.$i,
                'scanner_id' => $scanner->id,
            ]);
            $ticket = $this->ticket($event);
            $this->order($event, $ticket, $this->customer(), $organizer);
        }

        // Before batching: home 10, all-events 8, admin-home 23, events 15,
        // scanner 11. The remaining counts are the fixed setup for each page;
        // scanner gained one batched pivot lookup for multi-scanner events.
        $this->assertLessThanOrEqual(10, $this->queryCount('/'), 'home');
        $this->assertLessThanOrEqual(8, $this->queryCount('/all-events'), 'all-events');
        $this->assertLessThanOrEqual(18, $this->queryCount('/admin/home', fn () => $admin), 'admin-home');
        $this->assertLessThanOrEqual(8, $this->queryCount('/events', fn () => $organizer), 'events');
        $this->assertLessThanOrEqual(6, $this->queryCount('/scanner', fn () => $admin), 'scanner');
    }
}
