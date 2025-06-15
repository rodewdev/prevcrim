@php
    use Filament\Support\Enums\IconPosition;
@endphp

<x-filament-panels::page>

    @push('scripts')
        <link href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css' rel='stylesheet' />
        <script src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js'></script>
    @endpush

    <div class="flex items-center justify-between mb-6">
        <h2 class="text-xl font-bold tracking-tight sm:text-2xl">
            {{ __('Análisis de Zonas Conflictivas') }}
        </h2>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 mb-6">
        <x-filament::section>
            <div class="flex flex-col items-center justify-center h-full">
                <div class="text-3xl font-bold text-primary-600 dark:text-primary-500">{{ $totalZonas }}</div>
                <div class="text-sm text-gray-500">{{ __('Zonas identificadas') }}</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex flex-col items-center justify-center h-full">
                <div class="text-3xl font-bold text-warning-600 dark:text-warning-500">{{ $zonasAltoRiesgo }}</div>
                <div class="text-sm text-gray-500">{{ __('Zonas de alto riesgo') }}</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="flex flex-col items-center justify-center h-full">
                <div class="text-3xl font-bold text-success-600 dark:text-success-500">{{ number_format($porcentajePatrullaje, 1) }}%</div>
                <div class="text-sm text-gray-500">{{ __('Con patrullaje asignado') }}</div>
            </div>
        </x-filament::section>
    </div>

    {{ $table }}
    
    <div class="mt-6">
        <x-filament::section>
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">
                {{ __('Información sobre Índice de Conflictividad') }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                El índice de conflictividad se calcula considerando:
            </p>
            <ul class="list-disc list-inside text-sm text-gray-500 dark:text-gray-400 ml-4 mt-2">
                <li>Total de delitos en el período seleccionado</li>
                <li>Gravedad de los delitos (según su tipificación)</li>
                <li>Concentración temporal (frecuencia)</li>
                <li>Reincidencia en la zona</li>
            </ul>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">
                <strong>Niveles de riesgo:</strong>
            </p>
            <ul class="list-inside text-sm ml-4 mt-1">
                <li class="flex items-center">
                    <span class="inline-block w-3 h-3 rounded-full bg-success-500 mr-2"></span>
                    <span>Bajo (1-3): Monitoreo estándar</span>
                </li>
                <li class="flex items-center mt-1">
                    <span class="inline-block w-3 h-3 rounded-full bg-warning-500 mr-2"></span>
                    <span>Medio (4-7): Vigilancia incrementada</span>
                </li>
                <li class="flex items-center mt-1">
                    <span class="inline-block w-3 h-3 rounded-full bg-danger-500 mr-2"></span>
                    <span>Alto (8-10): Intervención prioritaria</span>
                </li>
            </ul>
        </x-filament::section>
    </div>

    @if($this->mostrarMapa)
    <div class="mt-6">
        <x-filament::section>
            <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-gray-100 mb-4">
                {{ __('Visualización Geográfica') }}
            </h3>
            <div id="mapa-zonas-conflictivas" class="h-96 w-full rounded-lg overflow-hidden">
                <!-- El mapa se cargará aquí mediante JavaScript -->
            </div>
            <div class="mt-4">
                <x-filament::button wire:click="$set('mostrarMapa', false)" color="gray">
                    {{ __('Cerrar Mapa') }}
                </x-filament::button>
            </div>
        </x-filament::section>
    </div>
    
    <script>
        document.addEventListener('livewire:initialized', function () {
            // Esperar a que los datos estén disponibles en el componente
            @this.on('mapaListo', function(datos) {
                if (!window.mapboxgl) {
                    console.error('Mapbox GL JS no está cargado.');
                    return;
                }
                
                // Inicializar el mapa
                mapboxgl.accessToken = '{{ env('MAPBOX_TOKEN', '') }}';
                
                const map = new mapboxgl.Map({
                    container: 'mapa-zonas-conflictivas',
                    style: 'mapbox://styles/mapbox/dark-v11',
                    center: [-70.6506, -33.4378], // Santiago, Chile como centro por defecto
                    zoom: 10
                });
                
                map.on('load', function() {
                    // Añadir controles
                    map.addControl(new mapboxgl.NavigationControl(), 'top-right');
                    map.addControl(new mapboxgl.FullscreenControl(), 'top-right');
                    
                    // Crear capa de puntos para delitos
                    if (datos.delitos && datos.delitos.length > 0) {
                        // Crear capa de heatmap para los delitos
                        map.addSource('delitos', {
                            'type': 'geojson',
                            'data': {
                                'type': 'FeatureCollection',
                                'features': datos.delitos.map(d => ({
                                    'type': 'Feature',
                                    'geometry': {
                                        'type': 'Point',
                                        'coordinates': [d.longitud, d.latitud]
                                    },
                                    'properties': {
                                        'id': d.id,
                                        'descripcion': d.descripcion,
                                        'fecha': d.fecha,
                                        'codigo': d.codigo
                                    }
                                }))
                            }
                        });
                        
                        map.addLayer({
                            'id': 'delitos-heat',
                            'type': 'heatmap',
                            'source': 'delitos',
                            'maxzoom': 15,
                            'paint': {
                                'heatmap-weight': 1,
                                'heatmap-intensity': 1,
                                'heatmap-color': [
                                    'interpolate',
                                    ['linear'],
                                    ['heatmap-density'],
                                    0, 'rgba(33,102,172,0)',
                                    0.2, 'rgb(103,169,207)',
                                    0.4, 'rgb(209,229,240)',
                                    0.6, 'rgb(253,219,199)',
                                    0.8, 'rgb(239,138,98)',
                                    1, 'rgb(178,24,43)'
                                ],
                                'heatmap-radius': 15,
                                'heatmap-opacity': 0.9
                            }
                        });
                        
                        // Crear capa de puntos para zoom cercano
                        map.addLayer({
                            'id': 'delitos-point',
                            'type': 'circle',
                            'source': 'delitos',
                            'minzoom': 14,
                            'paint': {
                                'circle-radius': 5,
                                'circle-color': '#e74c3c',
                                'circle-stroke-width': 1,
                                'circle-stroke-color': '#ffffff'
                            }
                        });
                        
                        // Ajustar vista al área con datos
                        if (datos.bounds) {
                            map.fitBounds([
                                [datos.bounds.min_lng, datos.bounds.min_lat],
                                [datos.bounds.max_lng, datos.bounds.max_lat]
                            ], { padding: 50 });
                        }
                        
                        // Popup al hacer click en un punto
                        map.on('click', 'delitos-point', (e) => {
                            const coordinates = e.features[0].geometry.coordinates.slice();
                            const properties = e.features[0].properties;
                            
                            new mapboxgl.Popup()
                                .setLngLat(coordinates)
                                .setHTML(`
                                    <strong>Delito:</strong> ${properties.codigo}<br>
                                    <strong>Descripción:</strong> ${properties.descripcion}<br>
                                    <strong>Fecha:</strong> ${properties.fecha}
                                `)
                                .addTo(map);
                        });
                        
                        // Cambiar cursor al pasar sobre un punto
                        map.on('mouseenter', 'delitos-point', () => {
                            map.getCanvas().style.cursor = 'pointer';
                        });
                        
                        map.on('mouseleave', 'delitos-point', () => {
                            map.getCanvas().style.cursor = '';
                        });
                    }
                });
            });
        });
    </script>
    @endif
</x-filament-panels::page>
