@php
    $color = 'gray';
    $icon = 'heroicon-o-globe-alt';
@endphp

<div
    {{ $attributes->class([
        'flex items-center justify-center filament-footer px-2 py-4',
        'mt-auto' => config('filament.layout.footer.should_stick_to_bottom', true),
    ]) }}
>
    <div class="text-center text-xs text-gray-500 dark:text-gray-400">
        <div>
            <span>PREVCRIM - Sistema de Prevención de Delitos</span>
        </div>
        
        <div>
            <span>&copy; {{ date('Y') }} Derechos Reservados</span>
        </div>
    </div>
</div>
