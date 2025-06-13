{{-- Estilos personalizados por institución - Versión profesional --}}
@if(auth()->check())
    {!! \App\Services\ThemeService::generateCustomCSS() !!}
    
    {{-- Información de la institución en el header --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Agregar badge de institución si existe
        const institutionName = @json(auth()->user()->institucion?->nombre ?? null);
        
        if (institutionName) {
            // Buscar el header de Filament y agregar el badge
            const header = document.querySelector('.fi-topbar');
            if (header && !document.querySelector('.institution-badge-container')) {
                const badgeContainer = document.createElement('div');
                badgeContainer.className = 'institution-badge-container ml-4';
                badgeContainer.innerHTML = `
                    <div class="institution-badge">
                        🏢 ${institutionName}
                    </div>
                `;
                
                // Buscar un lugar apropiado para insertar el badge
                const headerContent = header.querySelector('.fi-topbar-content') || header;
                if (headerContent) {
                    headerContent.appendChild(badgeContainer);
                }
            }
        }
        
        // Agregar clase CSS basada en la institución
        const bodyElement = document.body;
        if (institutionName) {
            const institutionClass = institutionName
                .toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/[^a-z0-9-]/g, '');
            bodyElement.classList.add(`institution-${institutionClass}`);
        }
    });
    </script>
    
    {{-- Estilos profesionales específicos --}}
    <style>
    /* Badge de institución profesional */
    .institution-badge-container {
        display: flex;
        align-items: center;
        height: 100%;
    }
    
    .institution-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--primary-color);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 0.25rem;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        border: 1px solid rgba(255, 255, 255, 0.2);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    /* Estilos específicos por institución - Colores serios */
    .institution-paz-ciudadana {
        --institution-accent: #1e4620;
    }
    
    .institution-carabineros {
        --institution-accent: #0f2f1f;
    }
    
    .institution-pdi {
        --institution-accent: #0c1429;
    }
    
    /* Mejorar contraste y legibilidad */
    .fi-sidebar-nav-item-label {
        color: rgba(255, 255, 255, 0.85);
        font-weight: 500;
    }
    
    .fi-sidebar-nav-item-active .fi-sidebar-nav-item-label {
        color: white;
        font-weight: 700;
    }
    
    /* Efectos hover más discretos */
    .fi-sidebar-nav-item:hover:not(.fi-sidebar-nav-item-active) {
        background-color: rgba(255, 255, 255, 0.05);
        transition: background-color 0.15s ease-in-out;
    }
    
    /* Sin animaciones llamativas */
    .fi-sidebar-nav-item-active {
        border-left: 4px solid var(--secondary-color);
    }
    
    /* Personalización profesional de notificaciones */
    .fi-notification {
        border-radius: 0.25rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
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
    
    /* Headers más sobrios */
    .fi-header-heading {
        color: var(--primary-color);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    
    /* Botones más definidos */
    .fi-btn-primary:hover {
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        transform: translateY(-1px);
    }
    </style>
@endif
