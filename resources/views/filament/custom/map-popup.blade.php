<div style="width:100%;height:350px;">
    <div id="map-{{ $id }}" style="width:100%;height:100%;border-radius:8px;"></div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (window.L === undefined) {
            var leaflet = document.createElement('link');
            leaflet.rel = 'stylesheet';
            leaflet.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
            document.head.appendChild(leaflet);
            var script = document.createElement('script');
            script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
            script.onload = function() { renderMap(); };
            document.body.appendChild(script);
        } else {
            renderMap();
        }
        function renderMap() {
            var map = L.map('map-{{ $id }}').setView([{{ $lat }}, {{ $lng }}], 16);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            }).addTo(map);
            L.marker([{{ $lat }}, {{ $lng }}]).addTo(map)
                .bindPopup(@json($label)).openPopup();
        }
    });
</script>
