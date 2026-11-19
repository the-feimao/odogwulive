{
    "name": @json(config('brand.name', config('app.name')).' Events'),
    "short_name": @json(config('brand.name', config('app.name'))),
    "description": "Discover events and manage your tickets.",
    "start_url": "/",
    "scope": "/",
    "display": "standalone",
    "background_color": "#f7f8fa",
    "theme_color": "#FEC009",
    "icons": [
        {
            "src": "/site-icon.svg",
            "sizes": "any",
            "type": "image/svg+xml",
            "purpose": "any maskable"
        }
    ]
}
