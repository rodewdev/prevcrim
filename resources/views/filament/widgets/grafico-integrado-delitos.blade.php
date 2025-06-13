<x-filament::widget>
    <x-filament::section>
        <div class="space-y-4">
            <div class="flex justify-between gap-4">
                <div>
                    <label for="tipoGrafico" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Agrupar por:
                    </label>
                    <select id="tipoGrafico" wire:model.live="tipoGrafico" wire:change="actualizarGrafico"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <option value="comuna">Comuna</option>
                        <option value="region">Región</option>
                        <option value="sector">Sector de Patrullaje</option>
                    </select>
                </div>
                <div>
                    <label for="tipoVisualizacion" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Tipo de gráfico:
                    </label>
                    <select id="tipoVisualizacion" wire:model.live="tipoVisualizacion" wire:change="actualizarGrafico"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <option value="bar">Barras</option>
                        <option value="pie">Circular</option>
                        <option value="line">Líneas</option>
                    </select>
                </div>
                <div>
                    <label for="limiteDatos" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Mostrar:
                    </label>
                    <select id="limiteDatos" wire:model.live="limiteDatos" wire:change="actualizarGrafico"
                            class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 rounded-md focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm">
                        <option value="5">5 elementos</option>
                        <option value="10">10 elementos</option>
                        <option value="15">15 elementos</option>
                        <option value="20">20 elementos</option>
                    </select>
                </div>
            </div>
            
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                <div class="h-80">
                    <canvas id="grafico-delitos" style="width:100%; height:100%;"></canvas>
                </div>
            </div>
        </div>
    </x-filament::section>

    <script>
        document.addEventListener('livewire:initialized', function () {
            const renderGrafico = (datos, tipo) => {
                const ctx = document.getElementById('grafico-delitos');
                
                if (window.graficoDelitos) {
                    window.graficoDelitos.destroy();
                }
                
                const parsedData = JSON.parse(datos);
                
                window.graficoDelitos = new Chart(ctx, {
                    type: tipo,
                    data: parsedData,
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,                        plugins: {
                            title: {
                                display: true,
                                text: '{{ $titulo }}',
                                font: {
                                    size: 16
                                }
                            },
                            legend: {
                                position: tipo === 'pie' ? 'right' : 'top'
                            }
                        },
                        scales: tipo !== 'pie' ? {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Cantidad de Delitos'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: tipo === 'comuna' ? 'Comuna' : (tipo === 'region' ? 'Región' : 'Sector')
                                }
                            }
                        } : {}
                    }
                });
            };
            
            renderGrafico('{!! json_encode($datos) !!}', '{{ $tipoVisualizacion }}');
            
            Livewire.on('graficoActualizado', (datos) => {
                renderGrafico(datos.datos, datos.tipoVisualizacion);
            });
            
            @this.on('graficoActualizado', (datos) => {
                renderGrafico(datos.datos, datos.tipoVisualizacion);
            });
        });
    </script>
</x-filament::widget>
