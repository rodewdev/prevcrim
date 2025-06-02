<div style="margin-bottom:1em;">
    <label for="address-{{ $id }}" style="font-weight:bold;">{{ $label }}</label>
    <input id="address-{{ $id }}" type="text" class="filament-input" style="width:100%;margin-bottom:0.5em;" name="{{ $addressField }}_mapa" value="{{ old($addressField, $record->{$addressField} ?? ($mainValue ?? '')) }}" placeholder="Escriba la dirección a buscar..." autocomplete="off" data-main-field="{{ $addressField }}">
    
    <div style="display:flex;gap:0.5em;margin-bottom:0.5em;">
        <button type="button" id="btn-search-{{ $id }}" style="background-color:#059669;color:white;padding:0.5em 1em;border-radius:0.25em;border:none;cursor:pointer;flex:2;font-size:0.875em;">🔍 Buscar en Mapa</button>
        <button type="button" id="btn-apply-{{ $id }}" style="background-color:#2563eb;color:white;padding:0.5em 1em;border-radius:0.25em;border:none;cursor:pointer;flex:2;font-size:0.875em;">✓ Aplicar a {{ ucfirst($addressField) }}</button>
        <button type="button" id="btn-debug-{{ $id }}" style="background-color:#7c3aed;color:white;padding:0.5em 1em;border-radius:0.25em;border:none;cursor:pointer;flex:1;font-size:0.875em;">🔧 Debug</button>
    </div>
    
    <div id="map-container-{{ $id }}" style="width:100%;height:350px;border-radius:8px;overflow:hidden;border:2px solid #e5e7eb;position:relative;display:none;">
        <iframe id="map-{{ $id }}" 
                width="100%" 
                height="100%" 
                style="border:0;" 
                referrerpolicy="no-referrer-when-downgrade"
                src="https://maps.google.com/maps?q=Santiago,Chile&output=embed&z=12">
        </iframe>
        
        <!-- Overlay con instrucciones para el usuario -->
        <div id="map-overlay-{{ $id }}" style="position:absolute;top:10px;left:10px;background:rgba(0,0,0,0.8);color:white;padding:8px;border-radius:4px;font-size:12px;max-width:280px;pointer-events:none;">
            💡 <strong>Cómo usar:</strong><br>
            1. Busque la ubicación en el mapa<br>
            2. Explore y encuentre la dirección exacta<br>
            3. Escriba o modifique la dirección arriba<br>
            4. Presione "Aplicar a {{ ucfirst($addressField) }}" para guardar
        </div>
    </div>
    
    <div id="address-info-{{ $id }}" style="margin-top:0.5em;padding:0.75em;background-color:#f8fafc;border-radius:0.375em;font-size:0.875em;display:none;border-left:4px solid #2563eb;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <strong>📍 Dirección seleccionada:</strong><br>
                <span id="selected-address-{{ $id }}" style="color:#374151;font-weight:500;"></span><br>
                <small style="color:#6b7280;">Lista para aplicar al campo {{ ucfirst($addressField) }}</small>
            </div>
        </div>
    </div>
    
    <div id="status-{{ $id }}" style="margin-top:0.5em;padding:0.5em;border-radius:0.25em;font-size:0.875em;display:none;">
        <!-- Mensajes de estado aparecerán aquí -->
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var input = document.getElementById('address-{{ $id }}');
        var btnSearch = document.getElementById('btn-search-{{ $id }}');
        var btnApply = document.getElementById('btn-apply-{{ $id }}');
        var btnDebug = document.getElementById('btn-debug-{{ $id }}');
        var mapContainer = document.getElementById('map-container-{{ $id }}');
        var mapIframe = document.getElementById('map-{{ $id }}');
        var mapOverlay = document.getElementById('map-overlay-{{ $id }}');
        var addressInfo = document.getElementById('address-info-{{ $id }}');
        var selectedAddress = document.getElementById('selected-address-{{ $id }}');
        var statusDiv = document.getElementById('status-{{ $id }}');
        var mainFieldName = '{{ $addressField }}';
        var debug = true;
        
        function log(message, data) {
            if (!debug) return;
            console.log('[{{ $id }}]', message, data || '');
        }
        
        function showStatus(message, type) {
            statusDiv.innerHTML = message;
            statusDiv.className = '';
            statusDiv.style.display = 'block';
            
            if (type === 'success') {
                statusDiv.style.backgroundColor = '#d1fae5';
                statusDiv.style.color = '#065f46';
                statusDiv.style.borderLeft = '4px solid #059669';
            } else if (type === 'error') {
                statusDiv.style.backgroundColor = '#fee2e2';
                statusDiv.style.color = '#991b1b';
                statusDiv.style.borderLeft = '4px solid #dc2626';
            } else {
                statusDiv.style.backgroundColor = '#fef3c7';
                statusDiv.style.color = '#92400e';
                statusDiv.style.borderLeft = '4px solid #f59e0b';
            }
            
            setTimeout(function() {
                statusDiv.style.display = 'none';
            }, 4000);
        }
        
        log('Inicializando mapa simplificado para direcciones en campo:', mainFieldName);
        
        // Función para crear URL de Google Maps (estilo WordPress, sin API key)
        function createGoogleMapsUrl(query, zoom) {
            var encodedQuery = encodeURIComponent(query || 'Santiago, Chile');
            var zoomLevel = zoom || 15;
            return 'https://maps.google.com/maps?q=' + encodedQuery + '&output=embed&z=' + zoomLevel;
        }
        
        // Función para mostrar información de dirección seleccionada
        function showSelectedAddress(address) {
            selectedAddress.textContent = address;
            addressInfo.style.display = 'block';
            log('Dirección seleccionada:', address);
        }
        
        // Buscar en el mapa
        btnSearch.addEventListener('click', function() {
            var searchQuery = input.value.trim();
            
            if (!searchQuery) {
                showStatus('⚠️ Por favor, escriba una dirección para buscar', 'error');
                input.focus();
                return;
            }
            
            log('Buscando en mapa:', searchQuery);
            showStatus('🔍 Buscando "' + searchQuery + '" en el mapa...', 'info');
            
            // Mostrar el contenedor del mapa con animación
            mapContainer.style.display = 'block';
            mapContainer.style.opacity = '0';
            setTimeout(function() {
                mapContainer.style.transition = 'opacity 0.3s ease-in-out';
                mapContainer.style.opacity = '1';
            }, 50);
            
            // Cargar el iframe con la búsqueda
            var mapUrl = createGoogleMapsUrl(searchQuery, 15);
            mapIframe.src = mapUrl;
            
            log('URL del mapa:', mapUrl);
            
            // Mostrar la dirección como seleccionada después de cargar el mapa
            setTimeout(function() {
                showSelectedAddress(searchQuery);
                showStatus('✅ Mapa cargado. Puede explorar y ajustar la dirección si es necesario', 'success');
                
                // Ocultar overlay después de unos segundos
                setTimeout(function() {
                    if (mapOverlay) {
                        mapOverlay.style.transition = 'opacity 0.5s ease-out';
                        mapOverlay.style.opacity = '0.3';
                    }
                }, 4000);
            }, 1500);
        });
        
        // Función mejorada para encontrar el campo principal donde aplicar la dirección
        function findMainInput() {
            log('=== INICIANDO BÚSQUEDA DEL CAMPO ===');
            log('Campo objetivo:', mainFieldName);
            
            // Excluir nuestro propio campo de búsqueda
            var ourSearchField = document.getElementById('address-{{ $id }}');
            
            // Debug: Mostrar todos los inputs disponibles antes de buscar
            var allInputsDebug = document.querySelectorAll('input');
            log('TOTAL DE INPUTS EN LA PÁGINA:', allInputsDebug.length);
            
            for (var i = 0; i < Math.min(allInputsDebug.length, 15); i++) {
                var inp = allInputsDebug[i];
                log('Input #' + i + ':', {
                    name: inp.name || 'SIN NOMBRE',
                    id: inp.id || 'SIN ID',
                    type: inp.type,
                    value: inp.value,
                    wireModel: inp.getAttribute('wire:model') || 'NO',
                    statePath: inp.getAttribute('data-state-path') || 'NO',
                    isOurField: inp === ourSearchField ? 'SÍ' : 'NO'
                });
            }
            
            // ESTRATEGIA 1: Buscar por wire:model exacto (Filament usa esto mucho)
            var wireExact = document.querySelector('input[wire\\:model="' + mainFieldName + '"]');
            if (wireExact && wireExact !== ourSearchField) {
                log('✅ ENCONTRADO por wire:model exacto:', wireExact.getAttribute('wire:model'));
                return wireExact;
            }
            
            // ESTRATEGIA 2: Buscar por wire:model con data. prefix
            var wireData = document.querySelector('input[wire\\:model="data.' + mainFieldName + '"]');
            if (wireData && wireData !== ourSearchField) {
                log('✅ ENCONTRADO por wire:model con data.:', wireData.getAttribute('wire:model'));
                return wireData;
            }
            
            // ESTRATEGIA 3: Buscar por nombre exacto (excluyendo campos _mapa)
            var exactMatch = document.querySelector('input[name="' + mainFieldName + '"]');
            if (exactMatch && exactMatch !== ourSearchField) {
                log('✅ ENCONTRADO por name exacto:', exactMatch.name);
                return exactMatch;
            }
            
            // ESTRATEGIA 4: Buscar entre inputs de texto (excluyendo nuestro campo y campos _mapa)
            var textInputs = document.querySelectorAll('input[type="text"], input:not([type])');
            log('Buscando entre ' + textInputs.length + ' inputs de texto...');
            
            for (var i = 0; i < textInputs.length; i++) {
                var inp = textInputs[i];
                
                // Saltar nuestro propio campo
                if (inp === ourSearchField) continue;
                
                // Saltar campos que terminen en _mapa
                if (inp.name && inp.name.endsWith('_mapa')) continue;
                
                // Buscar por wire:model que contenga el field name (pero no sea _mapa)
                var wireModel = inp.getAttribute('wire:model');
                if (wireModel && wireModel.includes(mainFieldName) && !wireModel.includes('_mapa')) {
                    log('✅ ENCONTRADO por wire:model que contiene el campo:', wireModel);
                    return inp;
                }
                
                // Buscar por name que contenga el field name (pero no sea _mapa)
                if (inp.name && inp.name.includes(mainFieldName) && !inp.name.includes('_mapa')) {
                    log('✅ ENCONTRADO por name que contiene el campo:', inp.name);
                    return inp;
                }
                
                // Buscar por data-state-path
                var statePath = inp.getAttribute('data-state-path');
                if (statePath && statePath.includes(mainFieldName)) {
                    log('✅ ENCONTRADO por data-state-path:', statePath);
                    return inp;
                }
            }
            
            // ESTRATEGIA 5: Buscar en todos los inputs pero con más restricciones
            var allInputs = document.querySelectorAll('input');
            for (var i = 0; i < allInputs.length; i++) {
                var inp = allInputs[i];
                
                // Saltar nuestro propio campo
                if (inp === ourSearchField) continue;
                
                // Buscar por atributos que contengan exactamente el nombre del campo
                var attributes = ['wire:model', 'data-state-path', 'x-model', 'name'];
                for (var j = 0; j < attributes.length; j++) {
                    var attr = inp.getAttribute(attributes[j]);
                    if (attr && attr === mainFieldName) {
                        log('✅ ENCONTRADO por atributo exacto ' + attributes[j] + ':', attr);
                        return inp;
                    }
                }
            }
            
            // ESTRATEGIA 6: Buscar por contenido de etiquetas (para campos Filament)
            var labels = document.querySelectorAll('label');
            for (var i = 0; i < labels.length; i++) {
                var label = labels[i];
                if (label.textContent.toLowerCase().includes(mainFieldName.toLowerCase())) {
                    var forAttr = label.getAttribute('for');
                    if (forAttr) {
                        var labelInput = document.getElementById(forAttr);
                        if (labelInput && labelInput !== ourSearchField) {
                            log('✅ ENCONTRADO por etiqueta asociada:', labelInput);
                            return labelInput;
                        }
                    }
                }
            }
            
            log('❌ NO SE ENCONTRÓ EL CAMPO');
            return null;
        }
        
        // Botón Debug - para depurar qué campos encuentra
        btnDebug.addEventListener('click', function() {
            log('=== MODO DEBUG ACTIVADO ===');
            
            var debugInfo = '';
            var allInputs = document.querySelectorAll('input');
            
            debugInfo += 'INFORMACIÓN DE DEBUG:\n';
            debugInfo += 'Campo objetivo: ' + mainFieldName + '\n';
            debugInfo += 'Total de inputs encontrados: ' + allInputs.length + '\n\n';
            
            for (var i = 0; i < allInputs.length; i++) {
                var inp = allInputs[i];
                debugInfo += 'Input #' + i + ':\n';
                debugInfo += '  name: ' + (inp.name || 'SIN NOMBRE') + '\n';
                debugInfo += '  id: ' + (inp.id || 'SIN ID') + '\n';
                debugInfo += '  type: ' + inp.type + '\n';
                debugInfo += '  value: ' + inp.value + '\n';
                debugInfo += '  wire:model: ' + (inp.getAttribute('wire:model') || 'NO') + '\n';
                debugInfo += '  data-state-path: ' + (inp.getAttribute('data-state-path') || 'NO') + '\n';
                debugInfo += '  x-model: ' + (inp.getAttribute('x-model') || 'NO') + '\n';
                debugInfo += '\n';
            }
            
            // Intentar encontrar el campo
            var foundField = findMainInput();
            if (foundField) {
                debugInfo += 'CAMPO ENCONTRADO:\n';
                debugInfo += '  name: ' + foundField.name + '\n';
                debugInfo += '  id: ' + foundField.id + '\n';
                debugInfo += '  value: ' + foundField.value + '\n';
            } else {
                debugInfo += 'CAMPO NO ENCONTRADO\n';
            }
            
            // Mostrar en consola y alert
            console.log(debugInfo);
            alert('Debug info mostrado en la consola del navegador. Presione F12 > Console para verlo.');
            
            showStatus('🔧 Información de debug mostrada en la consola del navegador', 'info');
        });
        
        // Aplicar dirección al campo principal
        btnApply.addEventListener('click', function() {
            log('Botón Aplicar clickeado');
            
            var addressToApply = input.value.trim();
            
            if (!addressToApply) {
                showStatus('⚠️ Por favor, ingrese una dirección antes de aplicar', 'error');
                input.focus();
                return;
            }
            
            var mainInput = findMainInput();
            
            if (!mainInput) {
                // ESTRATEGIA DE EMERGENCIA: Buscar de forma más agresiva
                log('Campo no encontrado con método normal, intentando búsqueda de emergencia...');
                
                var allInputs = document.querySelectorAll('input');
                var ourField = document.getElementById('address-{{ $id }}');
                
                for (var i = 0; i < allInputs.length; i++) {
                    var inp = allInputs[i];
                    
                    // Saltar nuestro campo
                    if (inp === ourField) continue;
                    
                    // Saltar campos _mapa
                    if (inp.name && inp.name.includes('_mapa')) continue;
                    
                    // Buscar en el HTML circundante
                    var parentElement = inp.closest('div');
                    if (parentElement) {
                        var parentHTML = parentElement.innerHTML.toLowerCase();
                        if (parentHTML.includes(mainFieldName.toLowerCase())) {
                            log('✅ EMERGENCIA: Campo encontrado por contexto HTML:', inp);
                            mainInput = inp;
                            break;
                        }
                    }
                    
                    // Buscar por placeholder que contenga el campo
                    if (inp.placeholder && inp.placeholder.toLowerCase().includes(mainFieldName.toLowerCase())) {
                        log('✅ EMERGENCIA: Campo encontrado por placeholder:', inp);
                        mainInput = inp;
                        break;
                    }
                }
            }
            
            if (mainInput) {
                log('Campo principal encontrado:', mainInput);
                log('Valor actual del campo:', mainInput.value);
                log('Nueva dirección a aplicar:', addressToApply);
                
                // Quitar readonly temporalmente si existe
                var wasReadonly = mainInput.hasAttribute('readonly');
                if (wasReadonly) {
                    mainInput.removeAttribute('readonly');
                }
                
                // Aplicar la dirección directamente
                mainInput.value = addressToApply;
                
                // Foco en el campo para activar eventos
                mainInput.focus();
                
                // Disparar eventos múltiples para asegurar que Filament/Livewire/Alpine detecte el cambio
                var events = ['input', 'change', 'blur', 'keyup', 'keydown'];
                events.forEach(function(eventType) {
                    var event = new Event(eventType, { 
                        bubbles: true, 
                        cancelable: true 
                    });
                    mainInput.dispatchEvent(event);
                });
                
                // Eventos específicos para diferentes frameworks
                mainInput.dispatchEvent(new CustomEvent('alpine:updated', { 
                    bubbles: true,
                    detail: { value: addressToApply }
                }));
                
                if (mainInput.hasAttribute('wire:model')) {
                    mainInput.dispatchEvent(new CustomEvent('livewire:updated', { 
                        bubbles: true 
                    }));
                    
                    // Disparar también el evento de Livewire
                    var livewireComponent = mainInput.closest('[wire\\:id]');
                    if (livewireComponent && window.Livewire) {
                        var wireModel = mainInput.getAttribute('wire:model');
                        if (wireModel) {
                            // Intentar actualizar directamente el componente Livewire
                            try {
                                window.Livewire.find(livewireComponent.getAttribute('wire:id')).set(wireModel, addressToApply);
                                log('✅ Livewire component actualizado directamente');
                            } catch (e) {
                                log('⚠️ No se pudo actualizar Livewire directamente:', e);
                            }
                        }
                    }
                }
                
                // Restaurar readonly si estaba presente
                if (wasReadonly) {
                    setTimeout(function() {
                        mainInput.setAttribute('readonly', '');
                    }, 100);
                }
                
                // Forzar actualización de Alpine.js si existe
                if (window.Alpine && mainInput.hasAttribute('x-model')) {
                    var alpineComponent = mainInput.closest('[x-data]');
                    if (alpineComponent) {
                        var xModel = mainInput.getAttribute('x-model');
                        if (xModel && alpineComponent._x_dataStack) {
                            try {
                                var data = alpineComponent._x_dataStack[0];
                                if (data && typeof data === 'object') {
                                    data[xModel] = addressToApply;
                                    log('✅ Alpine.js data actualizada directamente');
                                }
                            } catch (e) {
                                log('⚠️ No se pudo actualizar Alpine.js directamente:', e);
                            }
                        }
                    }
                }
                
                log('Campo "' + mainFieldName + '" actualizado correctamente con:', addressToApply);
                showStatus('✅ Dirección aplicada al campo "' + mainFieldName + '": ' + addressToApply, 'success');
                
                // Feedback visual en el botón
                btnApply.textContent = '✅ ¡Aplicado!';
                btnApply.style.backgroundColor = '#059669';
                
                setTimeout(function() {
                    btnApply.textContent = '✓ Aplicar a ' + mainFieldName.charAt(0).toUpperCase() + mainFieldName.slice(1);
                    btnApply.style.backgroundColor = '#2563eb';
                }, 2500);
                
            } else {
                showStatus('❌ No se pudo encontrar el campo "' + mainFieldName + '". Use el botón Debug para más información.', 'error');
                
                // Como alternativa, copiar al portapapeles
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(addressToApply).then(function() {
                        showStatus('📋 Dirección copiada al portapapeles: ' + addressToApply + '. Péguelo manualmente en el campo.', 'info');
                    }).catch(function() {
                        showStatus('❌ No se pudo copiar automáticamente. Copie manualmente: ' + addressToApply, 'error');
                    });
                } else {
                    showStatus('❌ No se pudo copiar automáticamente. Copie manualmente: ' + addressToApply, 'error');
                }
                
                log('Fallo en la actualización - campo no encontrado. Use el botón Debug para más detalles.');
            }
        });
        
        // Búsqueda automática mejorada al escribir
        var searchTimeout;
        input.addEventListener('input', function(e) {
            clearTimeout(searchTimeout);
            var value = e.target.value.trim();
            
            // Actualizar la dirección seleccionada en tiempo real
            if (value && addressInfo.style.display !== 'none') {
                showSelectedAddress(value);
            }
            
            // Auto-actualizar el mapa si está visible
            if (value.length > 3 && mapContainer.style.display !== 'none') {
                searchTimeout = setTimeout(function() {
                    log('Auto-actualizando mapa para:', value);
                    if (value === input.value.trim()) { // Solo si el usuario no sigue escribiendo
                        showStatus('🔄 Actualizando vista del mapa...', 'info');
                        mapIframe.src = createGoogleMapsUrl(value, 15);
                        showSelectedAddress(value);
                    }
                }, 2000);
            }
        });
        
        // Manejar Enter para búsqueda rápida
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                btnSearch.click();
            }
        });
        
        log('Mapa simplificado para direcciones inicializado correctamente');
        
        // Sincronización inicial con el campo principal
        setTimeout(function() {
            var mainInput = findMainInput();
            if (mainInput && mainInput.value && mainInput.value !== input.value) {
                input.value = mainInput.value;
                log('Valor sincronizado del campo principal:', mainInput.value);
                
                if (mainInput.value.trim()) {
                    showStatus('💡 Dirección actual: "' + mainInput.value + '". Puede buscar en el mapa para modificarla.', 'info');
                    showSelectedAddress(mainInput.value);
                }
            }
        }, 500);
    });
</script>
