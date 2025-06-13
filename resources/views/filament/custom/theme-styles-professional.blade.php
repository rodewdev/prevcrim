{{-- Estilos profesionales por institución chilena --}}
@if(auth()->check())
    <style>
    :root {
        @php 
            $theme = \App\Services\ThemeService::getCurrentUserTheme(); 
        @endphp
        --primary-color: {{ $theme['primary'] }};
        --primary-hover-color: {{ $theme['primary_hover'] }};
        --secondary-color: {{ $theme['secondary'] }};
        --accent-color: {{ $theme['accent'] }};
        --success-color: {{ $theme['success'] }};
        --warning-color: {{ $theme['warning'] }};
        --danger-color: {{ $theme['danger'] }};
        --info-color: {{ $theme['info'] }};
        --sidebar-color: {{ $theme['sidebar'] }};
        --navbar-color: {{ $theme['navbar'] }};
        --text-color: {{ $theme['text'] }};
        --text-light-color: {{ $theme['text_light'] }};
        --bg-color: {{ $theme['background'] }};
        --bg-alt-color: {{ $theme['background_alt'] }};
        --table-stripe-color: {{ $theme['table_stripe'] }};
        --border-color: {{ $theme['border'] }};
    }
    
    /* FORZAR ESTILOS CON ALTA ESPECIFICIDAD */
    
    /* === SIDEBAR === */
    .fi-sidebar,
    .fi-sidebar-nav,
    [x-data*="sidebar"],
    aside.fi-sidebar {
        background-color: var(--sidebar-color) !important;
        background-image: none !important;
        background: var(--sidebar-color) !important;
    }
    
    /* Items del sidebar */
    .fi-sidebar-nav-item,
    .fi-sidebar-nav-item > a,
    .fi-sidebar-nav-item-button {
        color: rgba(255, 255, 255, 0.9) !important;
        transition: all 0.2s ease !important;
    }
    
    .fi-sidebar-nav-item:hover:not(.fi-sidebar-nav-item-active),
    .fi-sidebar-nav-item:hover:not(.fi-sidebar-nav-item-active) > a {
        background-color: var(--primary-hover-color) !important;
        color: white !important;
    }
    
    .fi-sidebar-nav-item-active,
    .fi-sidebar-nav-item-active > a,
    .fi-sidebar-nav-item[aria-current="page"],
    .fi-sidebar-nav-item[aria-current="page"] > a {
        background-color: var(--secondary-color) !important;
        color: var(--text-color) !important;
        font-weight: 700 !important;
        border-left: 4px solid white !important;
    }
    
    .fi-sidebar-nav-item-label {
        color: inherit !important;
    }
    
    /* === NAVBAR/TOPBAR === */
    .fi-topbar,
    header.fi-topbar,
    .fi-header {
        background-color: var(--navbar-color) !important;
        background-image: none !important;
        background: var(--navbar-color) !important;
        border-bottom: 3px solid var(--secondary-color) !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15) !important;
    }
    
    .fi-topbar *,
    .fi-topbar-content *,
    .fi-header * {
        color: var(--text-light-color) !important;
    }    
    /* === BOTONES === */
    .fi-btn,
    button[type="submit"],
    .fi-btn-primary,
    .fi-ac-btn-action,
    .fi-btn[data-style="primary"] {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
        color: var(--text-light-color) !important;
        font-weight: 600 !important;
        border-radius: 6px !important;
    }
    
    .fi-btn:hover,
    button[type="submit"]:hover,
    .fi-btn-primary:hover,
    .fi-btn[data-style="primary"]:hover {
        background-color: var(--primary-hover-color) !important;
        border-color: var(--primary-hover-color) !important;
        transform: translateY(-1px) !important;
    }
    
    /* Botones secundarios */
    .fi-btn-outline,
    .fi-btn[data-style="outline"] {
        background-color: transparent !important;
        border: 2px solid var(--primary-color) !important;
        color: var(--primary-color) !important;
    }
    
    .fi-btn-outline:hover,
    .fi-btn[data-style="outline"]:hover {
        background-color: var(--primary-color) !important;
        color: var(--text-light-color) !important;
    }
    
    /* === TABLAS === */
    .fi-ta-table,
    table.fi-ta-table {
        background-color: var(--bg-color) !important;
        border-radius: 8px !important;
        overflow: hidden !important;
    }
    
    /* Headers de tabla */
    .fi-ta-header,
    .fi-ta-header-cell,
    thead th,
    .fi-ta-header-cell-content {
        background-color: var(--primary-color) !important;
        background-image: none !important;
        background: var(--primary-color) !important;
        color: var(--text-light-color) !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        border-bottom: 2px solid var(--secondary-color) !important;
        padding: 12px 16px !important;
    }
    
    .fi-ta-header-cell button,
    .fi-ta-header-cell a,
    .fi-ta-header-cell span {
        color: var(--text-light-color) !important;
    }
    
    /* Filas de tabla */
    .fi-ta-row,
    tbody tr {
        transition: all 0.2s ease !important;
    }
    
    .fi-ta-row:nth-child(even),
    tbody tr:nth-child(even) {
        background-color: var(--table-stripe-color) !important;
    }
    
    .fi-ta-row:nth-child(odd),
    tbody tr:nth-child(odd) {
        background-color: var(--bg-color) !important;
    }
    
    .fi-ta-row:hover,
    tbody tr:hover {
        background-color: var(--accent-color) !important;
        transform: scale(1.002) !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
    }
    
    /* Enlaces en tablas */
    .fi-ta-row a,
    tbody tr a {
        color: var(--primary-color) !important;
    }
    
    .fi-ta-row a:hover,
    tbody tr a:hover {
        color: var(--primary-hover-color) !important;
    }    
    /* === CONTENEDORES Y FONDOS === */
    .fi-main,
    main {
        background-color: var(--bg-alt-color) !important;
    }
    
    .fi-section,
    .fi-card,
    .fi-page,
    .fi-resource-table {
        background-color: var(--bg-color) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 8px !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
    }
    
    /* === HEADERS Y TÍTULOS === */
    .fi-header-heading,
    h1, h2, h3 {
        color: var(--primary-color) !important;
        font-weight: 700 !important;
    }
    
    .fi-section-header-heading {
        color: var(--text-color) !important;
        font-weight: 600 !important;
    }
    
    /* === ENLACES === */
    .fi-link,
    a:not(.fi-btn):not([class*="fi-ta"]) {
        color: var(--primary-color) !important;
        font-weight: 500 !important;
        text-decoration: none !important;
        transition: all 0.2s ease !important;
    }
    
    .fi-link:hover,
    a:not(.fi-btn):not([class*="fi-ta"]):hover {
        color: var(--primary-hover-color) !important;
        text-decoration: underline !important;
    }
    
    /* === FORMULARIOS === */
    .fi-input,
    .fi-select,
    .fi-textarea,
    input, select, textarea {
        border-color: var(--border-color) !important;
        transition: all 0.2s ease !important;
    }
    
    .fi-input:focus,
    .fi-select:focus,
    .fi-textarea:focus,
    input:focus, select:focus, textarea:focus {
        border-color: var(--primary-color) !important;
        box-shadow: 0 0 0 3px rgba(46, 107, 54, 0.1) !important;
        outline: none !important;
    }
    
    .fi-checkbox:checked,
    .fi-radio:checked,
    input[type="checkbox"]:checked,
    input[type="radio"]:checked {
        background-color: var(--primary-color) !important;
        border-color: var(--primary-color) !important;
    }
    
    /* === BADGES Y ELEMENTOS DE ESTADO === */
    .fi-badge,
    .fi-badge-primary,
    .badge {
        background-color: var(--primary-color) !important;
        color: var(--text-light-color) !important;
        font-weight: 600 !important;
        border-radius: 4px !important;
    }
    
    /* === NOTIFICACIONES Y ALERTAS === */
    .fi-notification.fi-color-success,
    .fi-alert.fi-color-success,
    .alert-success {
        background-color: var(--success-color) !important;
        color: var(--text-light-color) !important;
        border-left: 4px solid #2E7D32 !important;
    }
    
    .fi-notification.fi-color-warning,
    .fi-alert.fi-color-warning,
    .alert-warning {
        background-color: var(--warning-color) !important;
        color: var(--text-color) !important;
        border-left: 4px solid #F57F17 !important;
    }
    
    .fi-notification.fi-color-danger,
    .fi-alert.fi-color-danger,
    .alert-danger {
        background-color: var(--danger-color) !important;
        color: var(--text-light-color) !important;
        border-left: 4px solid #C62828 !important;
    }    
    /* === TRANSICIONES Y ANIMACIONES === */
    * {
        transition: background-color 0.2s ease, 
                   border-color 0.2s ease, 
                   color 0.2s ease, 
                   box-shadow 0.2s ease,
                   transform 0.2s ease !important;
    }
    
    @keyframes shimmer {
        0% { left: -100%; }
        100% { left: 100%; }
    }
    
    /* === BADGE DE INSTITUCIÓN === */
    .institution-badge {
        background: linear-gradient(135deg, var(--primary-color), var(--primary-hover-color)) !important;
        color: var(--text-light-color) !important;
        padding: 8px 16px !important;
        border-radius: 8px !important;
        font-size: 0.875rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.1em !important;
        border: 2px solid var(--secondary-color) !important;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2) !important;
        position: relative !important;
        overflow: hidden !important;
    }
    
    .institution-badge::before {
        content: '' !important;
        position: absolute !important;
        top: 0 !important;
        left: -100% !important;
        width: 100% !important;
        height: 100% !important;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent) !important;
        animation: shimmer 2s infinite !important;
    }
    
    /* === INDICADORES POR INSTITUCIÓN === */
    body.institution-carabineros .institution-indicator {
        background-color: #2E6B36 !important;
    }
    
    body.institution-paz-ciudadana .institution-indicator {
        background-color: #002060 !important;
    }
    
    body.institution-pdi .institution-indicator {
        background-color: #003366 !important;
    }
    
    /* === FILTROS Y BÚSQUEDA === */
    .fi-ta-search,
    .fi-ta-filters {
        background-color: var(--bg-color) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 6px !important;
    }
    
    .fi-ta-search input {
        border: none !important;
        background: transparent !important;
    }
    
    .fi-ta-search input:focus {
        box-shadow: none !important;
        outline: none !important;
    }
    
    /* === ACCIONES DE TABLA === */
    .fi-ta-actions {
        background-color: var(--bg-color) !important;
    }
    
    .fi-dropdown-list {
        background-color: var(--bg-color) !important;
        border: 1px solid var(--border-color) !important;
        border-radius: 6px !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
    }
    
    .fi-dropdown-list-item {
        color: var(--text-color) !important;
        transition: all 0.2s ease !important;
    }
    
    .fi-dropdown-list-item:hover {
        background-color: var(--accent-color) !important;
        color: var(--primary-color) !important;
    }
    
    /* === BREADCRUMBS === */
    .fi-breadcrumbs {
        margin-bottom: 1rem !important;
    }
    
    .fi-breadcrumbs-item {
        color: var(--text-color) !important;
    }
    
    .fi-breadcrumbs-item-active {
        color: var(--primary-color) !important;
        font-weight: 600 !important;
    }
    
    /* === MODALES === */
    .fi-modal,
    .fi-slide-over {
        background-color: var(--bg-color) !important;
    }
    
    .fi-modal-header {
        border-bottom: 1px solid var(--border-color) !important;
        background-color: var(--accent-color) !important;
    }
    
    .fi-modal-heading {
        color: var(--primary-color) !important;
        font-weight: 700 !important;
    }
    
    /* === TOOLTIPS === */
    .fi-tooltip {
        background-color: var(--primary-color) !important;
        color: var(--text-light-color) !important;
        border-radius: 4px !important;
    }
    
    /* === ELEMENTOS ESPECÍFICOS DE FILAMENT === */
    
    /* Panel principal */
    .fi-panel {
        background-color: var(--bg-color) !important;
    }
    
    /* Iconos */
    .fi-icon {
        color: inherit !important;
    }
    
    /* Separadores */
    .fi-hr {
        border-color: var(--border-color) !important;
    }
    
    /* Estados de carga */
    .fi-spinner {
        border-color: var(--border-color) !important;
        border-top-color: var(--primary-color) !important;
    }
    
    /* === OVERRIDE ESPECÍFICOS PARA MAYOR ESPECIFICIDAD === */
    
    /* Asegurar que los botones de acción usen colores correctos */
    .fi-ta-actions .fi-btn,
    .fi-ta-bulk-actions .fi-btn,
    [data-sortable] .fi-btn {
        background-color: var(--primary-color) !important;
        color: var(--text-light-color) !important;
    }
    
    /* Asegurar headers de tabla */
    .fi-ta-header-cell[data-sortable] {
        background-color: var(--primary-color) !important;
        color: var(--text-light-color) !important;
    }
    
    .fi-ta-header-cell[data-sortable]:hover {
        background-color: var(--primary-hover-color) !important;
    }
    
    /* Sidebar específico */
    .fi-sidebar-group-label {
        color: rgba(255, 255, 255, 0.7) !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
    }
    
    /* Asegurar que todos los elementos del navbar sean blancos */
    .fi-topbar .fi-btn,
    .fi-topbar button,
    .fi-topbar a,
    .fi-topbar span {
        color: var(--text-light-color) !important;
    }
    
    .fi-topbar .fi-btn:hover,
    .fi-topbar button:hover,
    .fi-topbar a:hover {
        background-color: rgba(255, 255, 255, 0.1) !important;
    }
    </style>
    </style>    
    {{-- JavaScript mejorado para gestión de temas institucionales --}}
    <script>
    // Configurar datos globales para el gestor de temas
    window.userInstitution = @json(auth()->user()->institucion?->nombre ?? null);
    window.institutionThemeClass = @json(\App\Services\ThemeService::getThemeClassName());
    window.institutionIcon = @json(\App\Services\ThemeService::getInstitutionIcon());
    
    document.addEventListener('DOMContentLoaded', function() {
        const institutionName = window.userInstitution;
        const themeClass = window.institutionThemeClass;
        
        if (institutionName && themeClass) {
            // Aplicar clase de tema al body
            document.body.classList.add(themeClass);
            
            // Agregar clase específica de institución
            const institutionClass = institutionName
                .toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/[^a-z0-9-]/g, '');
            document.body.classList.add(`institution-${institutionClass}`);
            
            // Crear y agregar badge de institución mejorado
            const header = document.querySelector('.fi-topbar');
            if (header && !document.querySelector('.institution-badge-container')) {
                const badgeContainer = document.createElement('div');
                badgeContainer.className = 'institution-badge-container flex items-center ml-auto mr-4';
                
                const icon = window.institutionIcon || '📋';
                
                badgeContainer.innerHTML = `
                    <div class="institution-badge">
                        <span class="mr-2">${icon}</span>
                        ${institutionName}
                    </div>
                `;
                
                const headerContent = header.querySelector('.fi-topbar-content') || header;
                if (headerContent) {
                    headerContent.appendChild(badgeContainer);
                }
            }
            
            // Agregar indicador visual sutil en la esquina
            if (!document.querySelector('.institution-indicator')) {
                const indicator = document.createElement('div');
                indicator.className = 'institution-indicator';
                indicator.style.cssText = `
                    position: fixed;
                    top: 0;
                    right: 0;
                    width: 4px;
                    height: 100vh;
                    background: var(--primary-color);
                    z-index: 1000;
                    pointer-events: none;
                    opacity: 0.7;
                `;
                document.body.appendChild(indicator);
            }
        }
        
        // Mejorar experiencia de hover en elementos interactivos
        const interactiveElements = document.querySelectorAll('.fi-btn, .fi-link, .fi-ac-btn-action');
        interactiveElements.forEach(element => {
            element.addEventListener('mouseenter', function() {
                if (!this.style.transform.includes('scale')) {
                    this.style.transform = 'translateY(-1px)';
                }
            });
            
            element.addEventListener('mouseleave', function() {
                this.style.transform = this.style.transform.replace('translateY(-1px)', '');
            });
        });
        
        // Mejorar hover en cards de estadísticas
        const statsCards = document.querySelectorAll('.fi-stats-card');
        statsCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px) scale(1.01)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });
        
        // Observer para elementos dinámicos
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === 1) { // Element node
                            // Re-aplicar mejoras de hover a nuevos elementos
                            const newButtons = node.querySelectorAll('.fi-btn, .fi-link, .fi-ac-btn-action');
                            newButtons.forEach(btn => {
                                btn.addEventListener('mouseenter', function() {
                                    this.style.transform = 'translateY(-1px)';
                                });
                                btn.addEventListener('mouseleave', function() {
                                    this.style.transform = 'translateY(0)';
                                });
                            });
                        }
                    });
                }
            });
        });
        
        observer.observe(document.body, { childList: true, subtree: true });
    });
    
    // Función global para cambiar tema dinámicamente (útil para testing)
    window.changeInstitutionTheme = function(institutionName) {
        // Remover clases existentes
        document.body.classList.remove('theme-carabineros', 'theme-paz-ciudadana', 'theme-pdi', 'theme-default');
        
        // Mapeo de instituciones a clases
        const themeMap = {
            'Carabineros': 'theme-carabineros',
            'Paz Ciudadana': 'theme-paz-ciudadana',
            'PDI': 'theme-pdi'
        };
        
        const themeClass = themeMap[institutionName] || 'theme-default';
        document.body.classList.add(themeClass);
        
        // Actualizar variables globales
        window.userInstitution = institutionName;
        
        // Actualizar badge si existe
        const existingBadge = document.querySelector('.institution-badge-container');
        if (existingBadge) {
            const iconMap = {
                'Carabineros': '🛡️',
                'Paz Ciudadana': '🏛️',
                'PDI': '🔍'
            };
            const icon = iconMap[institutionName] || '📋';
            existingBadge.innerHTML = `
                <div class="institution-badge">
                    <span class="mr-2">${icon}</span>
                    ${institutionName}
                </div>
            `;
        }
    };
    </script>
@endif
