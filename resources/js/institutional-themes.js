/**
 * Utilidades JavaScript para gestión de temas institucionales
 * Sistema para Carabineros de Chile, Paz Ciudadana y PDI
 */

class InstitutionalThemeManager {
    constructor() {
        this.themes = {
            'Carabineros': 'theme-carabineros',
            'Paz Ciudadana': 'theme-paz-ciudadana', 
            'PDI': 'theme-pdi'
        };
        
        this.institutionIcons = {
            'Carabineros': '🛡️',
            'Paz Ciudadana': '🏛️',
            'PDI': '🔍'
        };
        
        this.init();
    }

    /**
     * Inicializar el gestor de temas
     */
    init() {
        document.addEventListener('DOMContentLoaded', () => {
            this.applyCurrentTheme();
            this.createInstitutionBadge();
            this.createVisualIndicator();
            this.enhanceInteractivity();
        });
    }

    /**
     * Aplicar tema basado en la institución del usuario
     */
    applyCurrentTheme() {
        const institutionName = this.getInstitutionName();
        
        if (institutionName) {
            // Remover temas existentes
            Object.values(this.themes).forEach(themeClass => {
                document.body.classList.remove(themeClass);
            });
            
            // Aplicar tema correspondiente
            const themeClass = this.getThemeClass(institutionName);
            if (themeClass) {
                document.body.classList.add(themeClass);
            }
            
            // Agregar clase específica de institución
            const institutionClass = this.normalizeInstitutionName(institutionName);
            document.body.classList.add(`institution-${institutionClass}`);
        }
    }

    /**
     * Obtener nombre de institución del usuario actual
     */
    getInstitutionName() {
        // Esta función debe ser sobrescrita con la lógica específica del sistema
        // Por ejemplo, desde una variable global o meta tag
        return window.userInstitution || null;
    }

    /**
     * Obtener clase de tema para una institución
     */
    getThemeClass(institutionName) {
        for (const [key, themeClass] of Object.entries(this.themes)) {
            if (institutionName.includes(key) || key.includes(institutionName)) {
                return themeClass;
            }
        }
        return null;
    }

    /**
     * Normalizar nombre de institución para CSS
     */
    normalizeInstitutionName(institutionName) {
        return institutionName
            .toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^a-z0-9-]/g, '');
    }

    /**
     * Crear badge de institución en el header
     */
    createInstitutionBadge() {
        const institutionName = this.getInstitutionName();
        
        if (!institutionName) return;
        
        const header = document.querySelector('.fi-topbar');
        if (header && !document.querySelector('.institution-badge-container')) {
            const badgeContainer = document.createElement('div');
            badgeContainer.className = 'institution-badge-container flex items-center ml-auto mr-4';
            
            const icon = this.institutionIcons[institutionName] || '📋';
            
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
    }

    /**
     * Crear indicador visual sutil
     */
    createVisualIndicator() {
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

    /**
     * Mejorar interactividad de elementos
     */
    enhanceInteractivity() {
        // Mejorar hover en botones
        const buttons = document.querySelectorAll('.fi-btn, .fi-link, .fi-ac-btn-action');
        buttons.forEach(element => {
            element.addEventListener('mouseenter', function() {
                this.style.transform = this.style.transform.includes('scale') ? 
                    this.style.transform : 'translateY(-1px)';
            });
            
            element.addEventListener('mouseleave', function() {
                this.style.transform = this.style.transform.replace('translateY(-1px)', '');
            });
        });

        // Mejorar hover en cards de estadísticas
        const statsCards = document.querySelectorAll('.fi-stats-card');
        statsCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px) scale(1.02)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
            });
        });

        // Mejorar hover en filas de tabla
        const tableRows = document.querySelectorAll('.fi-ta-row');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.005)';
                this.style.zIndex = '10';
            });
            
            row.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
                this.style.zIndex = 'auto';
            });
        });
    }

    /**
     * Cambiar tema manualmente (útil para testing o cambio dinámico)
     */
    changeTheme(institutionName) {
        // Remover temas existentes
        Object.values(this.themes).forEach(themeClass => {
            document.body.classList.remove(themeClass);
        });
        
        // Aplicar nuevo tema
        const themeClass = this.getThemeClass(institutionName);
        if (themeClass) {
            document.body.classList.add(themeClass);
        }
        
        // Actualizar badge
        const existingBadge = document.querySelector('.institution-badge-container');
        if (existingBadge) {
            existingBadge.remove();
        }
        
        // Simular cambio de institución
        window.userInstitution = institutionName;
        this.createInstitutionBadge();
    }

    /**
     * Obtener variables CSS del tema actual
     */
    getCurrentThemeVariables() {
        const computedStyle = getComputedStyle(document.documentElement);
        return {
            primary: computedStyle.getPropertyValue('--primary-color').trim(),
            primaryHover: computedStyle.getPropertyValue('--primary-hover-color').trim(),
            secondary: computedStyle.getPropertyValue('--secondary-color').trim(),
            accent: computedStyle.getPropertyValue('--accent-color').trim(),
            success: computedStyle.getPropertyValue('--success-color').trim(),
            warning: computedStyle.getPropertyValue('--warning-color').trim(),
            danger: computedStyle.getPropertyValue('--danger-color').trim(),
            info: computedStyle.getPropertyValue('--info-color').trim()
        };
    }

    /**
     * Aplicar tema desde configuración personalizada
     */
    applyCustomTheme(themeConfig) {
        const root = document.documentElement;
        
        Object.entries(themeConfig).forEach(([key, value]) => {
            const cssVar = `--${key.replace(/([A-Z])/g, '-$1').toLowerCase()}-color`;
            root.style.setProperty(cssVar, value);
        });
    }
}

// Instancia global del gestor de temas
window.themeManager = new InstitutionalThemeManager();

// Función de utilidad para cambio manual de tema
window.changeInstitutionTheme = function(institutionName) {
    window.themeManager.changeTheme(institutionName);
};

// Función para obtener variables del tema actual
window.getCurrentTheme = function() {
    return window.themeManager.getCurrentThemeVariables();
};

// Exportar para uso en módulos
if (typeof module !== 'undefined' && module.exports) {
    module.exports = InstitutionalThemeManager;
}
