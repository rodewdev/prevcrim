<div class="p-6 text-center">
    <div class="mb-4">
        <div class="text-4xl mb-2">📍</div>
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Ubicación del Delito</h3>
    </div>
    
    <div class="bg-gray-50 rounded-lg p-4 mb-6">
        <div class="text-sm text-gray-600 mb-1">Dirección:</div>
        <div class="text-lg font-medium text-gray-900 break-words">{{ $address }}</div>
    </div>
    
    {{-- Información adicional sin dependencias externas --}}
    <div class="bg-blue-50 rounded-lg p-4">
        <div class="flex items-center justify-center mb-2">
            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm font-medium text-blue-800">Información de Ubicación</div>
        </div>
        <div class="text-xs text-blue-700">
            Esta ubicación fue registrada el {{ now()->format('d/m/Y') }}<br>
            Para ver en mapas externos, copie la dirección y búsquela manualmente
        </div>
    </div>
</div>
