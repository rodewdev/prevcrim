<div style="width:100%;height:400px;position:relative;">
    <div style="margin-bottom:0.75em;padding:0.75em;background-color:#f8fafc;border-radius:0.5em;border-left:4px solid #3b82f6;">
        <h4 style="margin:0 0 0.5em 0;color:#1e40af;font-size:1em;">📍 {{ $label ?? 'Ubicación en el Mapa' }}</h4>
        <div style="display:flex;justify-content:space-between;align-items:center;font-size:0.875em;color:#6b7280;">
            <span><strong>Coordenadas:</strong> {{ number_format($lat, 6) }}, {{ number_format($lng, 6) }}</span>
            <button type="button" id="btn-copy-coords-popup-{{ $id }}" style="background-color:#6b7280;color:white;padding:0.25em 0.5em;border-radius:0.25em;border:none;cursor:pointer;font-size:0.75em;">📋 Copiar</button>
        </div>
    </div>
    
    <div style="width:100%;height:350px;border-radius:8px;overflow:hidden;border:2px solid #e5e7eb;position:relative;">
        <iframe id="map-popup-{{ $id }}" 
                width="100%" 
                height="100%" 
                style="border:0;" 
                referrerpolicy="no-referrer-when-downgrade"
                src="https://maps.google.com/maps?q={{ $lat }},{{ $lng }}({{ urlencode($label ?? 'Ubicación') }})&output=embed&z=16">
        </iframe>
        
        <!-- Controles del mapa -->
        <div style="position:absolute;top:10px;right:10px;display:flex;flex-direction:column;gap:0.5em;">
            <button type="button" id="btn-zoom-in-{{ $id }}" style="background-color:white;border:1px solid #d1d5db;padding:0.5em;border-radius:0.25em;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,0.1);" title="Acercar zoom">➕</button>
            <button type="button" id="btn-zoom-out-{{ $id }}" style="background-color:white;border:1px solid #d1d5db;padding:0.5em;border-radius:0.25em;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,0.1);" title="Alejar zoom">➖</button>
            <button type="button" id="btn-center-{{ $id }}" style="background-color:white;border:1px solid #d1d5db;padding:0.5em;border-radius:0.25em;cursor:pointer;box-shadow:0 1px 3px rgba(0,0,0,0.1);" title="Centrar ubicación">🎯</button>
        </div>
    </div>
    
    <div style="margin-top:0.75em;padding:0.5em;background-color:#f3f4f6;border-radius:0.375em;font-size:0.875em;color:#6b7280;text-align:center;">
        💡 <strong>Tip:</strong> Haga clic derecho en el mapa para obtener más opciones o coordenadas exactas
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var mapIframe = document.getElementById('map-popup-{{ $id }}');
        var btnCopyCoords = document.getElementById('btn-copy-coords-popup-{{ $id }}');
        var btnZoomIn = document.getElementById('btn-zoom-in-{{ $id }}');
        var btnZoomOut = document.getElementById('btn-zoom-out-{{ $id }}');
        var btnCenter = document.getElementById('btn-center-{{ $id }}');
        
        var lat = {{ $lat }};
        var lng = {{ $lng }};
        var label = @json($label ?? 'Ubicación');
        var currentZoom = 16;
        
        console.log('Popup map initialized for:', label, lat, lng);
        
        // Función para actualizar URL del mapa con zoom
        function updateMapUrl(zoomLevel) {
            var coords = lat + ',' + lng;
            var encodedLabel = encodeURIComponent(label);
            var newUrl = 'https://maps.google.com/maps?q=' + coords + '(' + encodedLabel + ')&output=embed&z=' + zoomLevel;
            mapIframe.src = newUrl;
            currentZoom = zoomLevel;
            console.log('Map updated with zoom:', zoomLevel, newUrl);
        }
        
        // Controles de zoom
        btnZoomIn.addEventListener('click', function() {
            if (currentZoom < 20) {
                updateMapUrl(currentZoom + 2);
                showFeedback(btnZoomIn, '🔍 Zoom +');
            }
        });
        
        btnZoomOut.addEventListener('click', function() {
            if (currentZoom > 5) {
                updateMapUrl(currentZoom - 2);
                showFeedback(btnZoomOut, '🔍 Zoom -');
            }
        });
        
        btnCenter.addEventListener('click', function() {
            updateMapUrl(16); // Reset to default zoom
            showFeedback(btnCenter, '🎯 Centrado');
        });
        
        // Copiar coordenadas al portapapeles
        btnCopyCoords.addEventListener('click', function() {
            var coordsText = lat + ', ' + lng;
            
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(coordsText).then(function() {
                    showFeedback(btnCopyCoords, '✅ Copiado', 'success');
                }).catch(function() {
                    fallbackCopyCoords(coordsText);
                });
            } else {
                fallbackCopyCoords(coordsText);
            }
        });
        
        function fallbackCopyCoords(text) {
            var tempInput = document.createElement('input');
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            
            try {
                document.execCommand('copy');
                showFeedback(btnCopyCoords, '✅ Copiado', 'success');
            } catch (err) {
                showFeedback(btnCopyCoords, '❌ Error', 'error');
            }
            
            document.body.removeChild(tempInput);
        }
        
        function showFeedback(button, message, type) {
            var originalText = button.innerHTML;
            var originalBg = button.style.backgroundColor;
            
            button.innerHTML = message;
            
            if (type === 'success') {
                button.style.backgroundColor = '#059669';
                button.style.color = 'white';
            } else if (type === 'error') {
                button.style.backgroundColor = '#dc2626';
                button.style.color = 'white';
            }
            
            setTimeout(function() {
                button.innerHTML = originalText;
                button.style.backgroundColor = originalBg;
                button.style.color = '';
            }, 1500);
        }
        
        console.log('Popup map controls initialized successfully');
    });
</script>
