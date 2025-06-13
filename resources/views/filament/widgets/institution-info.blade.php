<div class="fi-wi-widget-card rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="flex items-center gap-4">
        <div class="flex h-16 w-16 items-center justify-center rounded-lg text-2xl" style="background-color: {{ $theme['primary'] }}20; color: {{ $theme['primary'] }};">
            @if(str_contains(strtolower($institutionName), 'paz'))
                🏛️
            @elseif(str_contains(strtolower($institutionName), 'carabineros'))
                🚓
            @elseif(str_contains(strtolower($institutionName), 'pdi'))
                🔍
            @else
                🏢
            @endif
        </div>
        
        <div class="flex-1">
            <div class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                Institución Activa
            </div>
            <div class="text-xl font-bold text-gray-900 dark:text-white">
                {{ $institutionName }}
            </div>
            <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Sistema de Prevención Criminal
            </div>
        </div>
        
        <div class="text-right">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                Usuario
            </div>
            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                {{ $userName }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400">
                {{ $userRole }}
            </div>
        </div>
    </div>
    
    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
            <span>Estado del Sistema</span>
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded font-medium text-white" style="background-color: {{ $theme['primary'] }};">
                <span class="w-2 h-2 bg-green-400 rounded-full"></span>
                Operativo
            </span>
        </div>
    </div>
</div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-white">
                <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3">
                    <div class="text-xs uppercase tracking-wide opacity-90">Usuario</div>
                    <div class="font-semibold">{{ $userName }}</div>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3">
                    <div class="text-xs uppercase tracking-wide opacity-90">Rol</div>
                    <div class="font-semibold">{{ $userRole }}</div>
                </div>
                <div class="bg-white/20 backdrop-blur-sm rounded-lg p-3">
                    <div class="text-xs uppercase tracking-wide opacity-90">Sesión</div>
                    <div class="font-semibold">{{ now()->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>
        
        <div class="ml-6 flex flex-col items-end gap-2">
            <div class="bg-white/30 backdrop-blur-sm rounded-full px-3 py-1 text-xs font-medium text-white">
                Tema Personalizado
            </div>
            <div class="flex gap-2">
                <div class="w-4 h-4 rounded-full border-2 border-white/50" style="background-color: {{ $theme['primary'] }};" title="Color Primario"></div>
                <div class="w-4 h-4 rounded-full border-2 border-white/50" style="background-color: {{ $theme['secondary'] }};" title="Color Secundario"></div>
            </div>
        </div>
    </div>
    
    <div class="mt-4 pt-4 border-t border-white/20">
        <div class="flex items-center justify-between text-white/90 text-sm">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>Sistema configurado para {{ $institutionName }}</span>
            </div>
            <div class="text-xs opacity-75">
                v2.0 - {{ date('Y') }}
            </div>
        </div>
    </div>
</div>
