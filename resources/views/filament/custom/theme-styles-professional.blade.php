{{-- Estilos profesionales por institución --}}
@if(auth()->check())
    <style>
    :root {
        @php 
            $theme = \App\Services\ThemeService::getCurrentUserTheme(); 
        @endphp
        --primary-color: {{ $theme['primary'] }};
        --secondary-color: {{ $theme['secondary'] }};
        --accent-color: {{ $theme['accent'] }};
        --success-color: {{ $theme['success'] }};
        --warning-color: {{ $theme['warning'] }};
        --danger-color: {{ $theme['danger'] }};
        --info-color: {{ $theme['info'] }};
        --sidebar-color: {{ $theme['sidebar'] }};
        --navbar-color: {{ $theme['navbar'] }};
        --text-color: {{ $theme['text'] ?? '#333333' }};
        --bg-color: {{ $theme['background'] ?? '#ffffff' }};
    }
    
    /* Diseño profesional y serio para sistema de delitos */
    .fi-sidebar {
        background-color: var(--sidebar-color) !important;
        border-right: 1px solid rgba(0,0,0,0.1);
    }
    
    .fi-sidebar-nav-item-active {
        background-color: var(--primary-color) !important;
        border-radius: 0;
        border-left: 4px solid var(--secondary-color);
    }
    
    .fi-topbar {
        background-color: var(--navbar-color) !important;
        border-bottom: 2px solid var(--primary-color);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    /* Botones principales - estilo corporativo serio */
    .fi-btn-primary {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
        border-radius: 4px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.025em;
        transition: all 0.2s ease;
    }
    
    .fi-btn-primary:hover {
        background-color: var(--secondary-color) !important;
        border-color: var(--secondary-color) !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.15);
    }
    
    /* Enlaces profesionales */
    .fi-link {
        color: var(--primary-color) !important;
        text-decoration: none;
        font-weight: 500;
    }
    
    .fi-link:hover {
        color: var(--secondary-color) !important;
        text-decoration: underline;
    }
    
    /* Headers institucionales */
    .fi-header-heading {
        color: var(--primary-color) !important;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    /* Cards con identidad institucional */
    .fi-stats-card {
        border-left: 4px solid var(--primary-color);
        background-color: var(--bg-color);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    /* Elementos de navegación */
    .fi-tabs-tab-active {
        border-bottom: 3px solid var(--primary-color) !important;
        color: var(--primary-color) !important;
        font-weight: 700;
    }
    
    /* Badges institucionales */
    .fi-badge-primary {
        background-color: var(--primary-color) !important;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    /* Formularios con estilo institucional */
    .fi-input:focus,
    .fi-select:focus,
    .fi-textarea:focus {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 2px rgba(var(--primary-color), 0.2) !important;
    }
    
    /* Checkboxes y radios */
    .fi-checkbox:checked,
    .fi-radio:checked {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
    }
    
    /* Tablas profesionales */
    .fi-ta-header-cell-sort-active {
        background-color: var(--primary-color) !important;
        color: white !important;
        font-weight: 600;
    }
    
    .fi-ta-row:hover {
        background-color: rgba(var(--primary-color), 0.05) !important;
    }
    
    /* Loading indicators */
    .fi-loading-indicator {
        border-top-color: var(--primary-color) !important;
    }
    
    /* Badge de institución en header */
    .institution-badge {
        background: var(--primary-color);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 4px;
        font-size: 0.875rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        border: 1px solid rgba(255,255,255,0.2);
    }
    
    /* Elementos de acción en tablas */
    .fi-ac-btn-action {
        color: var(--primary-color) !important;
        font-weight: 500;
    }
    
    .fi-ac-btn-action:hover {
        background-color: var(--primary-color) !important;
        color: white !important;
    }
    
    /* Notificaciones con colores institucionales */
    .fi-notification {
        border-radius: 4px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .fi-notification.fi-color-success {
        border-left: 4px solid var(--success-color);
    }
    
    .fi-notification.fi-color-warning {
        border-left: 4px solid var(--warning-color);
    }
    
    .fi-notification.fi-color-danger {
        border-left: 4px solid var(--danger-color);
    }
    
    .fi-notification.fi-color-info {
        border-left: 4px solid var(--info-color);
    }
    
    /* Navegación sidebar */
    .fi-sidebar-nav-item-label {
        color: rgba(255, 255, 255, 0.9);
        font-weight: 500;
    }
    
    .fi-sidebar-nav-item-active .fi-sidebar-nav-item-label {
        color: white;
        font-weight: 700;
    }
    
    .fi-sidebar-nav-item:hover:not(.fi-sidebar-nav-item-active) {
        background-color: rgba(255, 255, 255, 0.05);
    }
    
    /* Fondo general */
    .fi-main {
        background-color: var(--bg-color);
    }
    
    /* Remover efectos llamativos - solo transiciones profesionales */
    * {
        transition: background-color 0.2s ease, border-color 0.2s ease, color 0.2s ease, box-shadow 0.2s ease;
    }
    </style>
    
    {{-- JavaScript para agregar badge institucional --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const institutionName = @json(auth()->user()->institucion?->nombre ?? null);
        
        if (institutionName) {
            // Agregar clase de institución al body
            const institutionClass = institutionName
                .toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/[^a-z0-9-]/g, '');
            document.body.classList.add(`institution-${institutionClass}`);
            
            // Agregar badge de institución al header
            const header = document.querySelector('.fi-topbar');
            if (header && !document.querySelector('.institution-badge-container')) {
                const badgeContainer = document.createElement('div');
                badgeContainer.className = 'institution-badge-container flex items-center ml-auto mr-4';
                badgeContainer.innerHTML = `
                    <div class="institution-badge">
                        📋 ${institutionName}
                    </div>
                `;
                
                const headerContent = header.querySelector('.fi-topbar-content') || header;
                if (headerContent) {
                    headerContent.appendChild(badgeContainer);
                }
            }
        }
    });
    </script>
@endif
