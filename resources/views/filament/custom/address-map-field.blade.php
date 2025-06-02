<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<div style="margin-bottom:1em;">
    <label for="address-{{ $id }}" style="font-weight:bold;">{{ $label }}</label>
    <input id="address-{{ $id }}" type="text" class="filament-input" style="width:100%;margin-bottom:0.5em;" name="{{ $addressField }}_mapa" value="{{ old($addressField, $record->{$addressField} ?? ($mainValue ?? '')) }}" placeholder="Buscar dirección..." autocomplete="off" data-main-field="{{ $addressField }}">
    <button type="button" id="btn-apply-{{ $id }}" style="margin-bottom:0.5em;display:block;background-color:#4a5568;color:white;padding:0.5em 1em;border-radius:0.25em;border:none;cursor:pointer;">Cambiar {{ $label }}</button>
    <div id="map-{{ $id }}" style="width:100%;height:250px;border-radius:8px;overflow:hidden;"></div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('address-{{ $id }}');
        var btnApply = document.getElementById('btn-apply-{{ $id }}');
        var mapId = 'map-{{ $id }}';
        var mainFieldName = '{{ $addressField }}';
        var map = L.map(mapId).setView([-33.4489, -70.6693], 13); // Santiago por defecto
        var marker;
        var debug = true; // Activar logs de depuración
        
        // Función para registrar mensajes de depuración
        function log(message, data) {
            if (!debug) return;
            console.log('[{{ $id }}]', message, data || '');
        }
        
        log('Inicializando mapa para', mainFieldName);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '© OpenStreetMap'
        }).addTo(map);

        // Si hay valor en el input del mapa, centrar el mapa y poner marcador
        if(input.value && input.value.length > 3) {
            log('Valor inicial del input:', input.value);
            fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(input.value))
                .then(response => response.json())
                .then(data => {
                    if(data && data[0]) {
                        var lat = parseFloat(data[0].lat);
                        var lon = parseFloat(data[0].lon);
                        map.setView([lat, lon], 17);
                        marker = L.marker([lat, lon]).addTo(map);
                        log('Marcador inicial colocado en:', [lat, lon]);
                    }
                });
        }

        // Buscar dirección al escribir en el input del mapa
        input.addEventListener('change', function(e) {
            var val = e.target.value;
            if(val.length > 3) {
                log('Búsqueda de dirección:', val);
                fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(val))
                    .then(response => response.json())
                    .then(data => {
                        if(data && data[0]) {
                            var lat = parseFloat(data[0].lat);
                            var lon = parseFloat(data[0].lon);
                            map.setView([lat, lon], 17);
                            if(marker) map.removeLayer(marker);
                            marker = L.marker([lat, lon]).addTo(map);
                            log('Dirección encontrada:', data[0].display_name);
                        }
                    });
            } else {
                // Si el campo queda vacío, limpiar el marcador y centrar el mapa
                if(marker) map.removeLayer(marker);
                map.setView([-33.4489, -70.6693], 13); // Reset a vista por defecto
            }
        });

        // Al hacer clic en el mapa, obtener dirección y actualizar input
        map.on('click', function(e) {
            var latlng = e.latlng;
            log('Clic en el mapa en:', [latlng.lat, latlng.lng]);
            if(marker) map.removeLayer(marker);
            marker = L.marker([latlng.lat, latlng.lng]).addTo(map);
            fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + latlng.lat + '&lon=' + latlng.lng)
                .then(response => response.json())
                .then(data => {
                    if(data && data.display_name) {
                        input.value = data.display_name;
                        log('Nueva dirección seleccionada:', data.display_name);
                    }
                });
        });
        
        // Método principal para encontrar el campo readonly
        function findMainInput() {
            log('Buscando campo principal:', mainFieldName);
            
            // 1. Buscar todos los inputs readonly
            var allReadonlyInputs = document.querySelectorAll('input[readonly]');
            log('Inputs readonly encontrados:', allReadonlyInputs.length);
            
            // Primero intenta buscar por exactamente el nombre del campo
            for (var i = 0; i < allReadonlyInputs.length; i++) {
                var inp = allReadonlyInputs[i];
                
                // Comprobar si el nombre coincide
                if (inp.name === mainFieldName) {
                    log('¡Encontrado por name exacto!', inp);
                    return inp;
                }
                
                // Comprobar si termina con el nombre del campo
                if (inp.name.endsWith('.' + mainFieldName) || 
                    inp.name.endsWith('[' + mainFieldName + ']')) {
                    log('¡Encontrado por name parcial!', inp);
                    return inp;
                }
            }
            
            // Si no encuentra por name, intenta por otros atributos
            for (var i = 0; i < allReadonlyInputs.length; i++) {
                var inp = allReadonlyInputs[i];
                
                // Comprobar si el nombre contiene el campo (menos preciso)
                if (inp.name && inp.name.includes(mainFieldName)) {
                    log('¡Encontrado por name contenido!', inp);
                    return inp;
                }
                
                // Comprobar por data-state-path
                if (inp.getAttribute('data-state-path') && 
                    inp.getAttribute('data-state-path').includes(mainFieldName)) {
                    log('¡Encontrado por data-state-path!', inp);
                    return inp;
                }
                
                // Si hay label asociado que contenga el texto del campo
                var labels = document.querySelectorAll('label');
                for (var j = 0; j < labels.length; j++) {
                    var label = labels[j];
                    if (label.textContent.toLowerCase().includes(mainFieldName.toLowerCase()) && 
                        label.getAttribute('for') === inp.id) {
                        log('¡Encontrado por label asociado!', inp);
                        return inp;
                    }
                }
            }
            
            // Si aún no encuentra, busca por posición relativa (último recurso)
            var currentForm = input.closest('form') || document;
            var possibleInputs = currentForm.querySelectorAll('input[readonly]');
            
            // Encontrar el input más cercano al nuestro
            for (var i = 0; i < possibleInputs.length; i++) {
                var inp = possibleInputs[i];
                // Si está en la misma sección del formulario
                if (input.closest('.filament-form-field') && 
                    inp.closest('.filament-form-field') === input.closest('.filament-form-field').previousElementSibling) {
                    log('¡Encontrado por posición relativa!', inp);
                    return inp;
                }
            }
            
            log('No se encontró el campo principal :(');
            return null;
        }

        // Botón para aplicar el valor del input auxiliar al campo principal (readonly)
        btnApply.addEventListener('click', function() {
            log('Botón Cambiar clickeado');
            
            // Buscar el input principal (readonly)
            var mainInput = findMainInput();
            
            if (mainInput) {
                log('Campo principal encontrado:', mainInput);
                log('Valor actual:', mainInput.value);
                log('Nuevo valor a aplicar:', input.value);
                
                // 1. Actualizar el valor del input
                mainInput.value = input.value;
                
                // 2. Disparar eventos nativos de input y change
                mainInput.dispatchEvent(new Event('input', { bubbles: true }));
                mainInput.dispatchEvent(new Event('change', { bubbles: true }));
                
                // 3. Intentar actualizar el estado Livewire
                if (window.Livewire) {
                    try {
                        // Encontrar el componente Livewire más cercano
                        var wireEl = mainInput.closest('[wire\\:id]');
                        if (wireEl) {
                            var wireId = wireEl.getAttribute('wire:id');
                            var component = window.Livewire.find(wireId);
                            if (component) {
                                // Intentar diferentes estructuras de nombre para actualizar el estado
                                component.set(mainFieldName, input.value);
                                component.set('data.' + mainFieldName, input.value);
                                log('Livewire estado actualizado');
                            }
                        } else {
                            // Intento global (menos preferible pero a veces funciona)
                            if (typeof window.livewire !== 'undefined') {
                                window.livewire.emit('updateAddress', mainFieldName, input.value);
                                log('Evento global Livewire emitido');
                            }
                        }
                    } catch (e) {
                        log('Error al actualizar Livewire:', e.message);
                    }
                }
                
                // 4. Notificar éxito
                log('Campo actualizado correctamente');
            } else {
                // Fallback: buscar directamente por el ID o name específico
                var lastAttemptSelectors = [
                    'input[name="data[' + mainFieldName + ']"]',
                    'input[name="data.' + mainFieldName + '"]',
                    '#' + mainFieldName,
                    'input[data-field="' + mainFieldName + '"]'
                ];
                
                log('Último intento con selectores específicos:', lastAttemptSelectors);
                
                for (var i = 0; i < lastAttemptSelectors.length; i++) {
                    var target = document.querySelector(lastAttemptSelectors[i]);
                    if (target) {
                        target.value = input.value;
                        target.dispatchEvent(new Event('input', { bubbles: true }));
                        target.dispatchEvent(new Event('change', { bubbles: true }));
                        log('¡Encontrado en último intento!', target);
                        return;
                    }
                }
                
                alert('No se pudo actualizar el campo ' + mainFieldName + '. Por favor, copia el valor manualmente.');
                log('Fallo en todos los intentos de actualización');
            }
        });

        // Intentar sincronizar el valor inicial del input principal al auxiliar
        setTimeout(function() {
            var mainInput = findMainInput();
            if (mainInput && mainInput.value && mainInput.value !== input.value) {
                input.value = mainInput.value;
                log('Valor sincronizado del campo principal:', mainInput.value);
                
                // Centrar el mapa en esta dirección
                fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(mainInput.value))
                    .then(response => response.json())
                    .then(data => {
                        if(data && data[0]) {
                            var lat = parseFloat(data[0].lat);
                            var lon = parseFloat(data[0].lon);
                            map.setView([lat, lon], 17);
                            if(marker) map.removeLayer(marker);
                            marker = L.marker([lat, lon]).addTo(map);
                        }
                    });
            }
        }, 500); // Pequeño retraso para asegurar que todos los elementos estén cargados
    });
</script>
