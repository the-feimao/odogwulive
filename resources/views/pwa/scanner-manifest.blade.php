{
    "name": @json(config('brand.name', config('app.name')).' Ticket Scanner'),
    "short_name": "Scanner",
    "description": "Scan event tickets using the existing scanner API.",
    "start_url": "/scanner-app",
    "scope": "/",
    "display": "standalone",
    "background_color": "#f7f8fa",
    "theme_color": "#FEC009",
    "icons": [
        {
            "src": "/scanner-icon.svg",
            "sizes": "any",
            "type": "image/svg+xml",
            "purpose": "any maskable"
        }
    ]
}
