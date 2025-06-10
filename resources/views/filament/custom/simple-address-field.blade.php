<div class="space-y-4">
    {{-- Campo de texto para la ubicación --}}
    <div class="w-full">
        <input 
            type="text" 
            name="{{ $name }}" 
            id="{{ $id }}" 
            value="{{ $value ?? '' }}"
            placeholder="{{ $placeholder ?? 'Ingrese la dirección o ubicación' }}"
            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
            {{ $required ? 'required' : '' }}
        >
    </div>
    
    {{-- Información de ayuda --}}
    <div class="bg-blue-50 border border-blue-200 rounded-md p-3">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-400 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm text-blue-700">
                <strong>💡 Consejos para ubicaciones:</strong><br>
                • Incluya calle, número y comuna (ej: "Av. Libertador 1234, Las Condes")<br>
                • Sea lo más específico posible<br>
                • Use referencias conocidas si es necesario
            </div>
        </div>
    </div>
    
    {{-- Preview de la ubicación si ya hay una --}}
    @if(!empty($value))
        <div class="bg-gray-50 border border-gray-200 rounded-md p-3">
            <div class="flex items-center">
                <div class="text-2xl mr-2">📍</div>
                <div>
                    <div class="text-sm font-medium text-gray-900">Ubicación actual:</div>
                    <div class="text-sm text-gray-600">{{ $value }}</div>
                </div>
            </div>
        </div>
    @endif
</div>
