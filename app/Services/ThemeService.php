<?php

namespace App\Services;

class ThemeService
{    /**
     * Configuración de colores por institución según identidades institucionales chilenas
     */
    private static array $institutionThemes = [
        'Carabineros' => [
            'primary' => '#2E6B36',           // Verde institucional
            'primary_hover' => '#3D8B46',     // Verde claro (hover)
            'secondary' => '#F5D76E',         // Dorado decorativo
            'accent' => '#EAF3EB',            // Verde pálido (fondos)
            'success' => '#43A047',           // Verde éxito
            'warning' => '#FFD700',           // Amarillo advertencia
            'danger' => '#D32F2F',            // Rojo error
            'info' => '#2196F3',              // Azul información
            'sidebar' => '#2E6B36',           // Verde institucional
            'navbar' => '#2E6B36',            // Verde institucional
            'text' => '#4A4A4A',              // Gris oscuro texto
            'text_light' => '#FFFFFF',        // Blanco
            'background' => '#FFFFFF',        // Blanco
            'background_alt' => '#F5F5F5',    // Gris claro
            'table_stripe' => '#EAF3EB',      // Verde pálido para tablas
            'border' => '#EAF3EB',            // Verde pálido bordes
        ],
        'Paz Ciudadana' => [
            'primary' => '#002060',           // Azul institucional
            'primary_hover' => '#005599',     // Hover azul claro
            'secondary' => '#0073CF',         // Azul medio
            'accent' => '#E1F0FF',            // Celeste claro (fondos)
            'success' => '#43A047',           // Verde éxito
            'warning' => '#FFD700',           // Amarillo advertencia
            'danger' => '#D32F2F',            // Rojo error
            'info' => '#0073CF',              // Azul medio
            'sidebar' => '#002060',           // Azul institucional
            'navbar' => '#002060',            // Azul institucional
            'text' => '#333333',              // Gris oscuro texto
            'text_light' => '#FFFFFF',        // Blanco
            'background' => '#FFFFFF',        // Blanco
            'background_alt' => '#F2F2F2',    // Gris claro
            'table_stripe' => '#E1F0FF',      // Celeste claro para tablas
            'border' => '#E1F0FF',            // Celeste claro bordes
        ],
        'PDI' => [
            'primary' => '#003366',           // Azul marino
            'primary_hover' => '#005599',     // Hover azul
            'secondary' => '#FFD700',         // Amarillo dorado (detalles, bordes)
            'accent' => '#E8F0FA',            // Azul muy claro
            'success' => '#43A047',           // Verde éxito
            'warning' => '#FFD700',           // Amarillo advertencia
            'danger' => '#D32F2F',            // Rojo error
            'info' => '#005599',              // Azul hover
            'sidebar' => '#003366',           // Azul marino
            'navbar' => '#003366',            // Azul marino
            'text' => '#2C2C2C',              // Gris oscuro texto
            'text_light' => '#FFFFFF',        // Blanco
            'background' => '#FFFFFF',        // Blanco
            'background_alt' => '#E0E0E0',    // Gris claro
            'table_stripe' => '#E8F0FA',      // Azul muy claro para tablas
            'border' => '#E8F0FA',            // Azul muy claro bordes
        ],
        // Tema por defecto - Gris corporativo serio
        'default' => [
            'primary' => '#1f2937',           // Gris corporativo
            'primary_hover' => '#374151',     // Gris medio hover
            'secondary' => '#374151',         // Gris medio
            'accent' => '#f9fafb',            // Blanco
            'success' => '#43A047',           // Verde éxito
            'warning' => '#FFD700',           // Amarillo advertencia
            'danger' => '#D32F2F',            // Rojo error
            'info' => '#1e40af',              // Azul información
            'sidebar' => '#111827',           // Gris muy oscuro
            'navbar' => '#1f2937',            // Gris corporativo
            'text' => '#374151',              // Gris medio texto
            'text_light' => '#FFFFFF',        // Blanco
            'background' => '#FFFFFF',        // Blanco
            'background_alt' => '#f9fafb',    // Gris muy claro
            'table_stripe' => '#f9fafb',      // Gris muy claro para tablas
            'border' => '#e5e7eb',            // Gris claro bordes
        ]
    ];

    /**
     * Obtener el tema para una institución específica
     */
    public static function getThemeForInstitution(?string $institutionName): array
    {
        if (!$institutionName) {
            return self::$institutionThemes['default'];
        }

        // Buscar coincidencia exacta o parcial
        foreach (self::$institutionThemes as $key => $theme) {
            if ($key === 'default') continue;
            
            if (stripos($institutionName, $key) !== false || stripos($key, $institutionName) !== false) {
                return $theme;
            }
        }

        return self::$institutionThemes['default'];
    }

    /**
     * Obtener el tema para el usuario actual
     */
    public static function getCurrentUserTheme(): array
    {
        $user = auth()->user();
        
        if (!$user || !$user->institucion) {
            return self::$institutionThemes['default'];
        }

        return self::getThemeForInstitution($user->institucion->nombre);
    }    /**
     * Generar CSS personalizado para la institución actual
     */
    public static function generateCustomCSS(): string
    {
        $theme = self::getCurrentUserTheme();
        
        return "
        <style>
        :root {
            --primary-color: {$theme['primary']};
            --primary-hover-color: {$theme['primary_hover']};
            --secondary-color: {$theme['secondary']};
            --accent-color: {$theme['accent']};
            --success-color: {$theme['success']};
            --warning-color: {$theme['warning']};
            --danger-color: {$theme['danger']};
            --info-color: {$theme['info']};
            --sidebar-color: {$theme['sidebar']};
            --navbar-color: {$theme['navbar']};
            --text-color: {$theme['text']};
            --text-light-color: {$theme['text_light']};
            --bg-color: {$theme['background']};
            --bg-alt-color: {$theme['background_alt']};
            --table-stripe-color: {$theme['table_stripe']};
            --border-color: {$theme['border']};
        }
        
        /* === NAVBAR === */
        .fi-topbar {
            background-color: var(--navbar-color) !important;
            border-bottom: 3px solid var(--secondary-color) !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1) !important;
        }
        
        .fi-topbar-content {
            color: var(--text-light-color) !important;
        }
        
        /* === SIDEBAR/MENÚ === */
        .fi-sidebar {
            background-color: var(--sidebar-color) !important;
            border-right: 1px solid var(--border-color) !important;
        }
        
        .fi-sidebar-nav-item {
            transition: background-color 0.2s ease !important;
        }
        
        .fi-sidebar-nav-item:hover:not(.fi-sidebar-nav-item-active) {
            background-color: var(--primary-hover-color) !important;
        }
        
        .fi-sidebar-nav-item-active {
            background-color: var(--secondary-color) !important;
            border-left: 4px solid var(--text-light-color) !important;
        }
        
        .fi-sidebar-nav-item-label {
            color: var(--text-light-color) !important;
            font-weight: 500 !important;
        }
        
        .fi-sidebar-nav-item-active .fi-sidebar-nav-item-label {
            color: var(--text-color) !important;
            font-weight: 700 !important;
        }
        
        /* === BOTONES PRIMARIOS === */
        .fi-btn-primary {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
            color: var(--text-light-color) !important;
            font-weight: 600 !important;
            transition: all 0.2s ease !important;
        }
        
        .fi-btn-primary:hover {
            background-color: var(--primary-hover-color) !important;
            border-color: var(--primary-hover-color) !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15) !important;
        }
        
        /* === BOTONES SECUNDARIOS === */
        .fi-btn-outline {
            background-color: transparent !important;
            border: 2px solid var(--primary-color) !important;
            color: var(--primary-color) !important;
            font-weight: 600 !important;
        }
        
        .fi-btn-outline:hover {
            background-color: var(--primary-color) !important;
            color: var(--text-light-color) !important;
        }
        
        /* === TABLAS === */
        .fi-ta-table {
            background-color: var(--bg-color) !important;
        }
        
        .fi-ta-header {
            background-color: var(--primary-color) !important;
            color: var(--text-light-color) !important;
        }
        
        .fi-ta-header-cell {
            background-color: var(--primary-color) !important;
            color: var(--text-light-color) !important;
            font-weight: 700 !important;
            border-bottom: 2px solid var(--secondary-color) !important;
        }
        
        .fi-ta-row:nth-child(even) {
            background-color: var(--table-stripe-color) !important;
        }
        
        .fi-ta-row:nth-child(odd) {
            background-color: var(--bg-color) !important;
        }
        
        .fi-ta-row:hover {
            background-color: var(--accent-color) !important;
            transform: scale(1.005) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }
        
        .fi-ta-header-cell-sort-active {
            background-color: var(--secondary-color) !important;
            color: var(--text-color) !important;
        }
        
        /* === CONTENEDORES Y FONDOS === */
        .fi-main {
            background-color: var(--bg-alt-color) !important;
        }
        
        .fi-section {
            background-color: var(--bg-color) !important;
            border: 1px solid var(--border-color) !important;
            border-radius: 8px !important;
        }
        
        .fi-card {
            background-color: var(--bg-color) !important;
            border: 1px solid var(--border-color) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }
        
        /* === ENLACES === */
        .fi-link {
            color: var(--primary-color) !important;
            font-weight: 500 !important;
            text-decoration: none !important;
        }
        
        .fi-link:hover {
            color: var(--primary-hover-color) !important;
            text-decoration: underline !important;
        }
        
        /* === TEXTO === */
        .fi-header-heading {
            color: var(--primary-color) !important;
            font-weight: 700 !important;
        }
        
        .fi-section-header-heading {
            color: var(--text-color) !important;
        }
        
        /* === ESTADOS DE ALERTA === */
        .fi-notification.fi-color-success,
        .fi-alert.fi-color-success {
            background-color: var(--success-color) !important;
            color: var(--text-light-color) !important;
            border-left: 4px solid #2E7D32 !important;
        }
        
        .fi-notification.fi-color-warning,
        .fi-alert.fi-color-warning {
            background-color: var(--warning-color) !important;
            color: var(--text-color) !important;
            border-left: 4px solid #F57F17 !important;
        }
        
        .fi-notification.fi-color-danger,
        .fi-alert.fi-color-danger {
            background-color: var(--danger-color) !important;
            color: var(--text-light-color) !important;
            border-left: 4px solid #C62828 !important;
        }
        
        .fi-notification.fi-color-info,
        .fi-alert.fi-color-info {
            background-color: var(--info-color) !important;
            color: var(--text-light-color) !important;
            border-left: 4px solid #1565C0 !important;
        }
        
        /* === ELEMENTOS DE FORMULARIO === */
        .fi-input:focus,
        .fi-select:focus,
        .fi-textarea:focus {
            border-color: var(--primary-color) !important;
            box-shadow: 0 0 0 2px rgba(var(--primary-color), 0.2) !important;
        }
        
        .fi-checkbox:checked,
        .fi-radio:checked {
            background-color: var(--primary-color) !important;
            border-color: var(--primary-color) !important;
        }
        
        /* === BADGES === */
        .fi-badge-primary {
            background-color: var(--primary-color) !important;
            color: var(--text-light-color) !important;
            font-weight: 600 !important;
        }
        
        /* === TABS === */
        .fi-tabs-tab-active {
            border-bottom: 3px solid var(--primary-color) !important;
            color: var(--primary-color) !important;
            font-weight: 700 !important;
        }
        
        /* === ELEMENTOS DE ACCIÓN === */
        .fi-ac-btn-action {
            color: var(--primary-color) !important;
            font-weight: 500 !important;
        }
        
        .fi-ac-btn-action:hover {
            background-color: var(--accent-color) !important;
            color: var(--primary-hover-color) !important;
        }
        
        /* === WIDGETS DE ESTADÍSTICAS === */
        .fi-stats-card {
            background-color: var(--bg-color) !important;
            border-left: 4px solid var(--primary-color) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
        }
        
        .fi-wi-stats-overview-stat-value {
            color: var(--primary-color) !important;
            font-weight: 700 !important;
        }
        
        .fi-wi-stats-overview-stat-icon {
            color: var(--secondary-color) !important;
        }
        
        /* === LOADING INDICATORS === */
        .fi-loading-indicator {
            border-top-color: var(--primary-color) !important;
        }
        
        /* === CLASES ESPECÍFICAS POR INSTITUCIÓN === */
        .institution-badge {
            background: var(--primary-color) !important;
            color: var(--text-light-color) !important;
            padding: 0.5rem 1rem !important;
            border-radius: 6px !important;
            font-size: 0.875rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            border: 2px solid var(--secondary-color) !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.15) !important;
        }
        
        /* === TRANSICIONES SUAVES === */
        * {
            transition: background-color 0.2s ease, 
                       border-color 0.2s ease, 
                       color 0.2s ease, 
                       box-shadow 0.2s ease,
                       transform 0.2s ease !important;
        }
        </style>
        ";
    }

    /**
     * Obtener el logo específico de la institución (si existe)
     */
    public static function getInstitutionLogo(?string $institutionName): ?string
    {
        $logoMap = [
            'Paz Ciudadana' => '/images/logos/paz-ciudadana.png',
            'Carabineros' => '/images/logos/carabineros.png',
            'PDI' => '/images/logos/pdi.png',
        ];

        if (!$institutionName) {
            return null;
        }

        foreach ($logoMap as $key => $logo) {
            if (stripos($institutionName, $key) !== false || stripos($key, $institutionName) !== false) {
                // Verificar si el archivo existe
                if (file_exists(public_path($logo))) {
                    return $logo;
                }
            }
        }

        return null;
    }

    /**
     * Obtener la clase CSS del tema para aplicar al body
     */
    public static function getThemeClassName(?string $institutionName = null): string
    {
        $institution = $institutionName ?: (auth()->user()->institucion?->nombre ?? null);
        
        if (!$institution) {
            return 'theme-default';
        }

        // Mapeo de instituciones a clases CSS
        $themeMap = [
            'Carabineros' => 'theme-carabineros',
            'Paz Ciudadana' => 'theme-paz-ciudadana',
            'PDI' => 'theme-pdi',
        ];

        foreach ($themeMap as $key => $themeClass) {
            if (stripos($institution, $key) !== false || stripos($key, $institution) !== false) {
                return $themeClass;
            }
        }

        return 'theme-default';
    }

    /**
     * Obtener el icono específico de la institución
     */
    public static function getInstitutionIcon(?string $institutionName = null): string
    {
        $institution = $institutionName ?: (auth()->user()->institucion?->nombre ?? null);
        
        $iconMap = [
            'Carabineros' => '🛡️',
            'Paz Ciudadana' => '🏛️',
            'PDI' => '🔍',
        ];

        if (!$institution) {
            return '📋';
        }

        foreach ($iconMap as $key => $icon) {
            if (stripos($institution, $key) !== false || stripos($key, $institution) !== false) {
                return $icon;
            }
        }

        return '📋';
    }
}
