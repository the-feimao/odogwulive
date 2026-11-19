<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\Setting;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RemainingPublicPagesTest extends TestCase
{
    #[Test]
    public function the_blog_index_lists_published_articles_and_preserves_blog_favorites()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);

        $blog = Blog::create([
            'title' => 'Published Test Article',
            'description' => 'A published article body.',
            'category_id' => $event->category_id,
            'tags' => 'featured, guides',
            'image' => 'default.png',
            'status' => 1,
        ]);
        Blog::create([
            'title' => 'Hidden Test Article',
            'description' => 'A hidden article body.',
            'category_id' => $event->category_id,
            'tags' => 'draft',
            'image' => 'default.png',
            'status' => 0,
        ]);

        $customer = $this->customer(['favorite_blog' => (string) $blog->id]);

        $this->actingAs($customer, 'appuser')
            ->get('/all-blogs')
            ->assertOk()
            ->assertSee('Published Test Article', false)
            ->assertDontSee('Hidden Test Article', false)
            ->assertSee('Test Category', false)
            ->assertSee(url('blog-detail/'.$blog->id.'/'.preg_replace('/\s+/', '-', $blog->title)), false)
            ->assertSee(url('/add-favorite/'.$blog->id.'/blog'), false)
            ->assertSee('aria-label="Remove from saved blog"', false)
            ->assertDontSee('stackpath.bootstrapcdn.com', false)
            ->assertDontSee('frontend/js/custom.js', false)
            ->assertDontSee('owl-carousel', false);
    }

    #[Test]
    public function the_blog_detail_page_renders_the_article_without_legacy_assets()
    {
        $organizer = $this->organizer();
        $event = $this->event($organizer);

        $blog = Blog::create([
            'title' => 'Detailed Test Article',
            'description' => 'A detailed article body for the blog page.',
            'category_id' => $event->category_id,
            'tags' => 'featured, guides',
            'image' => 'default.png',
            'status' => 1,
        ]);

        $this->withSession(['status' => 'Report is submitted successfully.'])
            ->get('/blog-detail/'.$blog->id.'/detailed-test-article')
            ->assertOk()
            ->assertSee('Detailed Test Article', false)
            ->assertSee('A detailed article body for the blog page.', false)
            ->assertSee('Test Category', false)
            ->assertSee('featured', false)
            ->assertSee('Report is submitted successfully.', false)
            ->assertSee('Back to all blogs', false)
            ->assertDontSee('javascript:void(0)', false)
            ->assertDontSee('owl-carousel', false)
            ->assertDontSee('stackpath.bootstrapcdn.com', false)
            ->assertDontSee('frontend/js/custom.js', false);
    }

    #[Test]
    public function the_organizer_detail_page_renders_profile_events_and_follow_hook()
    {
        $organizer = $this->organizer([
            'bio' => 'Organizer biography for the test.',
            'phone' => '08012345678',
            'country' => 'Nigeria',
        ]);
        $event = $this->event($organizer, ['name' => 'Organizer Test Event']);

        $customer = $this->customer(['following' => (string) $organizer->id]);

        $this->actingAs($customer, 'appuser')
            ->get('/organization/'.$organizer->id.'/test-organizer')
            ->assertOk()
            ->assertSee('Organizer biography for the test.', false)
            ->assertSee('08012345678', false)
            ->assertSee('Nigeria', false)
            ->assertSee('Organizer Test Event', false)
            ->assertSee('onclick="follow('.$organizer->id.')"', false)
            ->assertSee('data-follow-id="'.$organizer->id.'"', false)
            ->assertSee('Unfollow', false)
            ->assertSee(url('event/'.$event->id.'/'.preg_replace('/\s+/', '-', $event->name)), false)
            ->assertDontSee('agent-single', false)
            ->assertDontSee('frontend/js/custom.js', false)
            ->assertDontSee('stackpath.bootstrapcdn.com', false);
    }

    #[Test]
    public function the_contact_page_preserves_the_form_and_uses_an_osm_map()
    {
        $setting = Setting::find(1);

        $this->get('/contact')
            ->assertOk()
            ->assertSee('action="#"', false)
            ->assertSee('method="post"', false)
            ->assertSee('name="name"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="subject"', false)
            ->assertSee('name="message"', false)
            ->assertSee('id="contact_map"', false)
            ->assertSee('data-lat="'.(float) $setting->default_lat.'"', false)
            ->assertSee('data-lng="'.(float) $setting->default_long.'"', false)
            ->assertDontSee('maps.googleapis.com', false)
            ->assertDontSee('frontend/js/map.js', false)
            ->assertDontSee('php-email-form', false)
            ->assertDontSee('stackpath.bootstrapcdn.com', false);
    }
}
