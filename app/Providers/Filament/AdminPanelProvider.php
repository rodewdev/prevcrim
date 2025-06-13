<?php

namespace App\Providers\Filament;

use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use App\Services\ThemeService;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('PREVCRIM')
            ->brandLogo(null)
            ->brandLogoHeight('2rem')
            ->favicon(null)
            ->colors($this->getColorsForCurrentUser())
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugin(FilamentSpatieRolesPermissionsPlugin::make())
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                'panels::head.end',
                fn () => view('filament.custom.theme-styles-professional')
            )
            ->renderHook(
                'panels::body.start',
                function () {
                    if (auth()->check() && auth()->user()->institucion) {
                        $institutionName = auth()->user()->institucion->nombre;
                        $dataAttribute = 'data-institution="' . htmlspecialchars($institutionName) . '"';
                        return '<script>document.body.setAttribute("' . $dataAttribute . '");</script>';
                    }
                    return '';
                }
            );
    }

    /**
     * Obtener colores dinámicos basados en la institución del usuario
     */
    private function getColorsForCurrentUser(): array
    {
        try {
            if (!auth()->check()) {
                return ['primary' => Color::Blue];
            }

            $theme = ThemeService::getCurrentUserTheme();
            
            return [
                'primary' => $theme['primary'],
                'success' => $theme['success'],
                'warning' => $theme['warning'],
                'danger' => $theme['danger'],
                'info' => $theme['info'],
            ];
        } catch (\Exception $e) {
            // En caso de error, usar colores por defecto
            return ['primary' => Color::Blue];
        }
    }
}
