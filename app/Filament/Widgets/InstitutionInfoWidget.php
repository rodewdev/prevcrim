<?php

namespace App\Filament\Widgets;

use App\Services\ThemeService;
use Filament\Widgets\Widget;

class InstitutionInfoWidget extends Widget
{
    protected static string $view = 'filament.widgets.institution-info';
    
    protected int | string | array $columnSpan = 'full';
    
    protected static ?int $sort = -1; // Mostrar al principio

    public function getViewData(): array
    {
        $user = auth()->user();
        $institution = $user?->institucion;
        $theme = ThemeService::getCurrentUserTheme();
        
        return [
            'user' => $user,
            'institution' => $institution,
            'theme' => $theme,
            'institutionName' => $institution?->nombre ?? 'Sin institución',
            'userName' => $user?->name ?? 'Usuario',
            'userRole' => $user?->roles->first()?->name ?? 'Sin rol',
        ];
    }

    public static function canView(): bool
    {
        return auth()->check();
    }
}
