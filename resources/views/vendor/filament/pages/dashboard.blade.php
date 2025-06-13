<x-filament::page>
    <div class="mb-4">
        <h1 class="text-2xl font-bold tracking-tight">Bienvenido al Sistema PREVCRIM</h1>
        <p class="mt-1 text-gray-500 dark:text-gray-400">
            Sistema de prevención de delitos y gestión de información criminal
        </p>
    </div>

    @if ($this->hasWidgets())
        <x-filament::widgets
            :columns="$this->getColumns()"
            :data="$this->getWidgetsData()"
        />
    @endif
</x-filament::page>
