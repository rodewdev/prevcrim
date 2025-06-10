{{-- Vista solo para mostrar ubicación en mapa (sin editar) --}}
<div class="space-y-4 p-4">
    <div class="flex items-center space-x-2 text-gray-700 dark:text-gray-300">
        <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
        </svg>
        <span class="font-medium">{{ $address }}</span>
    </div>
    
    {{-- Mapa embebido usando Google Maps --}}
    <div class="w-full h-96 border border-gray-200 rounded-lg overflow-hidden">
        <iframe 
            width="100%" 
            height="100%" 
            frameborder="0" 
            style="border:0" 
            src="https://www.google.com/maps/embed/v1/place?key=AIzaSyBFw0Qbyq9zTFTd-tUY6dOWTgHz-0MSc3_Q&q={{ urlencode($address . ', Chile') }}&zoom=16"
            allowfullscreen>
        </iframe>
    </div>
    
    {{-- Enlace para abrir en Google Maps --}}
    <div class="flex justify-center">
        <a 
            href="https://www.google.com/maps/search/{{ urlencode($address . ', Chile') }}" 
            target="_blank" 
            class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors duration-200"
        >
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
            Ver en Google Maps
        </a>    </div>
</div>
