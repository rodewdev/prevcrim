<div class="ubicacion-field-container" style="margin-bottom: 1em;">
    <label for="ubicacion-{{ $id }}" class="text-sm font-medium text-gray-700 mb-2 block">
        📍 {{ $label ?? 'Ubicación' }}
    </label>
    
    {{-- Campo principal de ubicación --}}
    <input 
        id="ubicacion-{{ $id }}" 
        type="text" 
        name="{{ $name ?? 'ubicacion' }}" 
        value="{{ $value ?? '' }}"
        placeholder="{{ $placeholder ?? 'Ej: Av. Libertador 1234, Las Condes, Región Metropolitana' }}"
        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm mb-3"
        {{ $required ? 'required' : '' }}
        autocomplete="off"
    >
    
    {{-- Herramientas de ayuda para completar la dirección --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
        <div class="flex items-start mb-3">
            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div class="text-sm">
                <strong class="text-blue-800">💡 Asistente de Ubicación</strong>
                <p class="text-blue-700 mt-1">Complete los campos para construir automáticamente la dirección:</p>
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3">
            <div>
                <label class="text-xs font-medium text-gray-600">Calle/Avenida</label>
                <input 
                    type="text" 
                    id="calle-{{ $id }}" 
                    placeholder="Ej: Av. Libertador"
                    class="w-full text-sm rounded border-gray-300 mt-1"
                >
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600">Número</label>
                <input 
                    type="text" 
                    id="numero-{{ $id }}" 
                    placeholder="Ej: 1234"
                    class="w-full text-sm rounded border-gray-300 mt-1"
                >
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600">Comuna</label>
                <input 
                    type="text" 
                    id="comuna-{{ $id }}" 
                    placeholder="Ej: Las Condes"
                    class="w-full text-sm rounded border-gray-300 mt-1"
                >
            </div>
            <div>
                <label class="text-xs font-medium text-gray-600">Región</label>
                <input 
                    type="text" 
                    id="region-{{ $id }}" 
                    placeholder="Ej: RM"
                    class="w-full text-sm rounded border-gray-300 mt-1"
                >
            </div>
        </div>
        
        <div class="mt-3 flex gap-2">
            <button 
                type="button" 
                id="btn-construir-{{ $id }}"
                class="bg-blue-600 text-white px-3 py-2 rounded text-sm font-medium hover:bg-blue-700 transition-colors"
            >
                🔧 Construir Dirección
            </button>
            <button 
                type="button" 
                id="btn-limpiar-{{ $id }}"
                class="bg-gray-500 text-white px-3 py-2 rounded text-sm font-medium hover:bg-gray-600 transition-colors"
            >
                🗑️ Limpiar Todo
            </button>
        </div>
    </div>
    
    {{-- Sugerencias comunes --}}
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 mb-4">
        <div class="text-sm font-medium text-gray-700 mb-2">📋 Direcciones Comunes en Chile:</div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
            <button type="button" class="direccion-sugerida text-left text-xs bg-white border border-gray-200 rounded p-2 hover:bg-gray-100 transition-colors" data-direccion="Av. Libertador Bernardo O'Higgins, Santiago Centro, RM">
                Av. Libertador B. O'Higgins, Santiago Centro
            </button>
            <button type="button" class="direccion-sugerida text-left text-xs bg-white border border-gray-200 rounded p-2 hover:bg-gray-100 transition-colors" data-direccion="Av. Providencia, Providencia, RM">
                Av. Providencia, Providencia
            </button>
            <button type="button" class="direccion-sugerida text-left text-xs bg-white border border-gray-200 rounded p-2 hover:bg-gray-100 transition-colors" data-direccion="Av. Las Condes, Las Condes, RM">
                Av. Las Condes, Las Condes
            </button>
            <button type="button" class="direccion-sugerida text-left text-xs bg-white border border-gray-200 rounded p-2 hover:bg-gray-100 transition-colors" data-direccion="Av. Vicuña Mackenna, Ñuñoa, RM">
                Av. Vicuña Mackenna, Ñuñoa
            </button>
        </div>
    </div>
    
    {{-- Preview de la ubicación actual --}}
    @if(!empty($value))
        <div class="bg-green-50 border border-green-200 rounded-lg p-3">
            <div class="flex items-center">
                <div class="text-2xl mr-3">✅</div>
                <div>
                    <div class="text-sm font-medium text-green-800">Ubicación Actual:</div>
                    <div class="text-sm text-green-700 font-medium">{{ $value }}</div>
                    <div class="text-xs text-green-600 mt-1">Guardado correctamente</div>
                </div>
            </div>
        </div>
    @endif
    
    {{-- Consejos para una buena ubicación --}}
    <div class="mt-3 bg-yellow-50 border border-yellow-200 rounded-lg p-3">
        <div class="flex items-start">
            <svg class="w-4 h-4 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
            </svg>
            <div class="text-xs text-yellow-800">
                <strong>💡 Para una ubicación precisa incluya:</strong><br>
                • Nombre de calle/avenida completo<br>
                • Número o altura aproximada<br>
                • Comuna y región<br>
                • Referencias cercanas si es necesario (ej: "frente al mall", "esquina con...")
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fieldId = '{{ $id }}';
    const ubicacionInput = document.getElementById('ubicacion-' + fieldId);
    const calleInput = document.getElementById('calle-' + fieldId);
    const numeroInput = document.getElementById('numero-' + fieldId);
    const comunaInput = document.getElementById('comuna-' + fieldId);
    const regionInput = document.getElementById('region-' + fieldId);
    const btnConstruir = document.getElementById('btn-construir-' + fieldId);
    const btnLimpiar = document.getElementById('btn-limpiar-' + fieldId);
    const direccionesSugeridas = document.querySelectorAll('.direccion-sugerida');
    
    // Función para construir la dirección automáticamente
    btnConstruir.addEventListener('click', function() {
        const partes = [];
        
        if (calleInput.value.trim()) {
            if (numeroInput.value.trim()) {
                partes.push(calleInput.value.trim() + ' ' + numeroInput.value.trim());
            } else {
                partes.push(calleInput.value.trim());
            }
        }
        
        if (comunaInput.value.trim()) {
            partes.push(comunaInput.value.trim());
        }
        
        if (regionInput.value.trim()) {
            partes.push(regionInput.value.trim());
        }
        
        if (partes.length > 0) {
            ubicacionInput.value = partes.join(', ');
            ubicacionInput.focus();
            
            // Efecto visual de éxito
            ubicacionInput.style.borderColor = '#10b981';
            setTimeout(() => {
                ubicacionInput.style.borderColor = '#d1d5db';
            }, 2000);
        }
    });
    
    // Función para limpiar todos los campos
    btnLimpiar.addEventListener('click', function() {
        if (confirm('¿Está seguro de que desea limpiar todos los campos de ubicación?')) {
            ubicacionInput.value = '';
            calleInput.value = '';
            numeroInput.value = '';
            comunaInput.value = '';
            regionInput.value = '';
            ubicacionInput.focus();
        }
    });
    
    // Auto-construir mientras el usuario escribe
    [calleInput, numeroInput, comunaInput, regionInput].forEach(input => {
        input.addEventListener('input', function() {
            // Auto-construir después de una pausa de 1 segundo
            clearTimeout(window.autoConstructorTimeout);
            window.autoConstructorTimeout = setTimeout(() => {
                if (this.value.trim()) {
                    btnConstruir.click();
                }
            }, 1000);
        });
    });
    
    // Direcciones sugeridas
    direccionesSugeridas.forEach(btn => {
        btn.addEventListener('click', function() {
            const direccion = this.getAttribute('data-direccion');
            ubicacionInput.value = direccion;
            ubicacionInput.focus();
            
            // Efecto visual
            this.style.backgroundColor = '#dbeafe';
            setTimeout(() => {
                this.style.backgroundColor = '#ffffff';
            }, 1000);
        });
    });
    
    // Validación en tiempo real
    ubicacionInput.addEventListener('input', function() {
        const valor = this.value.trim();
        if (valor.length < 10) {
            this.style.borderColor = '#fbbf24';
        } else if (valor.length >= 20) {
            this.style.borderColor = '#10b981';
        } else {
            this.style.borderColor = '#d1d5db';
        }
    });
});
</script>
