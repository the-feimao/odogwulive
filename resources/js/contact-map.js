import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

(() => {
    const container = document.getElementById('contact_map');

    if (!container) {
        return;
    }

    const lat = Number.parseFloat(container.dataset.lat || '');
    const lng = Number.parseFloat(container.dataset.lng || '');
    const fallback = container.querySelector('[data-map-fallback]');

    if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
        return;
    }

    if (fallback) {
        fallback.remove();
    }

    const map = L.map(container, {
        center: [lat, lng],
        zoom: 15,
        scrollWheelZoom: false,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener">OpenStreetMap</a> contributors',
    }).addTo(map);

    L.circleMarker([lat, lng], {
        radius: 9,
        color: '#ffffff',
        weight: 3,
        fillColor: '#f59e0b',
        fillOpacity: 1,
    }).addTo(map);
})();
