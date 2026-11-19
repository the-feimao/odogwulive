<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ScannerPwaTest extends TestCase
{
    #[Test]
    public function the_scanner_pwa_route_renders_without_legacy_cdn_dependencies()
    {
        $this->get(route('scanner.pwa'))
            ->assertOk()
            ->assertSee('Ticket scanner')
            ->assertSee('Scanner sign in')
            ->assertSee('Manual ticket code')
            ->assertSee('scanner.webmanifest')
            ->assertDontSee('/site.webmanifest')
            ->assertDontSee('code.jquery.com')
            ->assertDontSee('sweetalert2');
    }

    #[Test]
    public function the_scanner_pwa_static_assets_are_present()
    {
        $manifest = json_decode(file_get_contents(base_path('public/manifest.webmanifest')), true);
        $worker = file_get_contents(base_path('public/scanner-sw.js'));
        $offline = file_get_contents(base_path('public/offline.html'));

        $this->assertSame('/scanner-app', $manifest['start_url']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertStringContainsString('/scanner-icon.svg', $manifest['icons'][0]['src']);
        $this->assertStringContainsString("caches.match('/offline.html')", $worker);
        $this->assertStringContainsString('You are offline', $offline);
    }

    #[Test]
    public function the_public_site_has_a_config_driven_manifest()
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('site.webmanifest')
            ->assertSee('theme-color');

        $site = $this->get(route('pwa.site-manifest'))->assertOk();
        $scanner = $this->get(route('pwa.scanner-manifest'))->assertOk();

        $this->assertStringContainsString(
            'application/manifest+json',
            $site->headers->get('Content-Type')
        );
        $this->assertStringContainsString(
            'application/manifest+json',
            $scanner->headers->get('Content-Type')
        );

        $siteManifest = json_decode($site->getContent(), true);
        $scannerManifest = json_decode($scanner->getContent(), true);

        $this->assertSame(config('brand.name').' Events', $siteManifest['name']);
        $this->assertSame(config('brand.name').' Ticket Scanner', $scannerManifest['name']);
        $this->assertSame('/scanner-app', $scannerManifest['start_url']);
    }
}
