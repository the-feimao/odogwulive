<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Category;
use App\Models\Event;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    public function index()
    {
        $base = rtrim(config('brand.canonical_url', config('app.url')), '/');
        $timezone = Setting::find(1)?->timezone ?? config('app.timezone');
        $now = Carbon::now($timezone)->format('Y-m-d H:i:s');

        $entries = collect([
            ['loc' => $base.'/', 'lastmod' => null, 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => $base.'/all-events', 'lastmod' => null, 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => $base.'/all-category', 'lastmod' => null, 'changefreq' => 'weekly', 'priority' => '0.6'],
            ['loc' => $base.'/all-blogs', 'lastmod' => null, 'changefreq' => 'weekly', 'priority' => '0.6'],
            ['loc' => $base.'/contact', 'lastmod' => null, 'changefreq' => 'monthly', 'priority' => '0.4'],
        ]);

        Event::query()
            ->where('status', 1)
            ->where('is_deleted', 0)
            ->where('event_status', 'Pending')
            ->where('end_time', '>=', $now)
            ->select(['id', 'name', 'updated_at'])
            ->orderBy('id')
            ->chunkById(500, function ($events) use ($entries, $base) {
                foreach ($events as $event) {
                    $entries->push([
                        'loc' => $base.'/event/'.$event->id.'/'.(Str::slug((string) $event->name) ?: 'event'),
                        'lastmod' => optional($event->updated_at)->toAtomString(),
                        'changefreq' => 'daily',
                        'priority' => '0.8',
                    ]);
                }
            });

        Category::query()
            ->where('status', 1)
            ->select(['id', 'name', 'updated_at'])
            ->orderBy('id')
            ->chunkById(500, function ($categories) use ($entries, $base) {
                foreach ($categories as $category) {
                    $entries->push([
                        'loc' => $base.'/events-category/'.$category->id.'/'.(Str::slug((string) $category->name) ?: 'category'),
                        'lastmod' => optional($category->updated_at)->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.6',
                    ]);
                }
            });

        Blog::query()
            ->where('status', 1)
            ->select(['id', 'title', 'updated_at'])
            ->orderBy('id')
            ->chunkById(500, function ($blogs) use ($entries, $base) {
                foreach ($blogs as $blog) {
                    $entries->push([
                        'loc' => $base.'/blog-detail/'.$blog->id.'/'.(Str::slug((string) $blog->title) ?: 'blog'),
                        'lastmod' => optional($blog->updated_at)->toAtomString(),
                        'changefreq' => 'weekly',
                        'priority' => '0.6',
                    ]);
                }
            });

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";

        foreach ($entries as $entry) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>'.$this->xml($entry['loc'])."</loc>\n";

            if (! empty($entry['lastmod'])) {
                $xml .= '    <lastmod>'.$this->xml($entry['lastmod'])."</lastmod>\n";
            }
            if (! empty($entry['changefreq'])) {
                $xml .= '    <changefreq>'.$this->xml($entry['changefreq'])."</changefreq>\n";
            }
            if (! empty($entry['priority'])) {
                $xml .= '    <priority>'.$this->xml($entry['priority'])."</priority>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>'."\n";

        return response($xml, 200)
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function xml($value): string
    {
        return htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
