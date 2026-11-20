<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SeoAndSitemapTest extends TestCase
{
    #[Test]
    public function event_detail_has_canonical_og_twitter_and_event_json_ld(): void
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer, [
            'name' => 'SEO Test Event',
            'description' => 'SEO test description.',
        ]);
        $this->ticket($event, ['type' => 'paid', 'price' => 1000, 'quantity' => 10]);
        $this->ticket($event, ['type' => 'free', 'price' => 0, 'quantity' => 5]);

        $canonical = config('brand.canonical_url').'/event/'.$event->id.'/seo-test-event';

        $this->get('/event/'.$event->id.'/seo-test-event')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.$canonical.'"', false)
            ->assertSee('property="og:type" content="event"', false)
            ->assertSee('property="og:url" content="'.$canonical.'"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false)
            ->assertSee('type="application/ld+json"', false)
            ->assertSee('"@type":"Event"', false)
            ->assertSee('"priceCurrency":', false);
    }

    #[Test]
    public function public_pages_have_default_seo_metadata(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="canonical"', false)
            ->assertSee('property="og:type" content="website"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false);
    }

    #[Test]
    public function sitemap_is_xml_and_contains_public_event_url(): void
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer, ['name' => 'Sitemap Event']);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"', false)
            ->assertSee('/event/'.$event->id.'/sitemap-event', false)
            ->assertDontSee('admin', false);
    }

    #[Test]
    public function robots_points_to_the_sitemap(): void
    {
        $this->assertStringContainsString(
            'Sitemap: /sitemap.xml',
            (string) file_get_contents(public_path('robots.txt'))
        );
    }
}
