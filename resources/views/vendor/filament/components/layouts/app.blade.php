<x-filament::layouts.base
    :livewire="$livewire"
    class="filament-app-layout"
>
    <div class="flex w-full min-h-screen overflow-x-clip">
        <div
            x-cloak
            x-data="{}"
            x-on:click="$store.sidebar.close()"
            x-show="$store.sidebar.isOpen"
            x-transition.opacity.500ms
            class="filament-sidebar-close-overlay fixed inset-0 z-20 w-full h-full bg-gray-900/50 lg:hidden"
        ></div>

        <x-filament::layouts.app.sidebar />

        <div
            @if (config('filament.layout.sidebar.is_collapsible_on_desktop'))
                x-data="{}"
                x-bind:class="{
                    'lg:pl-[var(--collapsed-sidebar-width)] rtl:lg:pr-[var(--collapsed-sidebar-width)]': ! $store.sidebar.isOpen,
                    'lg:pl-[var(--sidebar-width)] rtl:lg:pr-[var(--sidebar-width)]': $store.sidebar.isOpen,
                }"
                x-bind:style="'display: flex'" 
            @endif
            @class([
                'flex-col gap-y-6 w-screen flex-1 rtl:lg:pl-0',
                'hidden h-full transition-all' => config('filament.layout.sidebar.is_collapsible_on_desktop'),
                'flex lg:pl-[var(--sidebar-width)] rtl:lg:pr-[var(--sidebar-width)]' => ! config('filament.layout.sidebar.is_collapsible_on_desktop'),
            ])
        >
            <header @class([
                'filament-main-topbar sticky top-0 z-10 flex h-16 w-full shrink-0 items-center border-b bg-white',
                'dark:bg-gray-800 dark:border-gray-700' => config('filament.dark_mode'),
            ])>
                <div @class([
                    'flex items-center w-full px-2 sm:px-4 md:px-6 lg:px-8',
                ])>
                    <button
                        x-cloak
                        x-data="{}"
                        x-on:click="$store.sidebar.isOpen ? $store.sidebar.close() : $store.sidebar.open()"
                        @class([
                            'filament-sidebar-open-button shrink-0 flex items-center justify-center w-10 h-10 text-primary-500 rounded-full outline-none hover:bg-gray-500/5 focus:bg-primary-500/10',
                            'lg:hidden' => ! config('filament.layout.sidebar.is_collapsible_on_desktop'),
                            'lg:mr-4 rtl:lg:mr-0 rtl:lg:ml-4' => config('filament.layout.sidebar.is_collapsible_on_desktop'),
                        ])
                    >
                        <x-heroicon-o-bars-3 class="w-6 h-6" />
                    </button>

                    <div class="flex items-center justify-between flex-1">
                        <x-filament::layouts.app.topbar.breadcrumbs />

                        @livewire('filament.core.global-search')

                        <x-filament::layouts.app.topbar.user-menu />
                    </div>
                </div>
            </header>

            <div @class([
                'filament-main-content flex-1 w-full px-4 mx-auto md:px-6 lg:px-8',
                match (config('filament.layout.max_content_width')) {
                    'xl' => 'max-w-xl',
                    '2xl' => 'max-w-2xl',
                    '3xl' => 'max-w-3xl',
                    '4xl' => 'max-w-4xl',
                    '5xl' => 'max-w-5xl',
                    '6xl' => 'max-w-6xl',
                    '7xl' => 'max-w-7xl',
                    default => 'max-w-full',
                },
            ])>
                {{ \Filament\Facades\Filament::renderHook('content.start') }}

                {{ $slot }}

                {{ \Filament\Facades\Filament::renderHook('content.end') }}
            </div>

            <div class="filament-main-footer py-4 shrink-0">
                <x-filament::footer />
            </div>
        </div>
    </div>
</x-filament::layouts.base>
