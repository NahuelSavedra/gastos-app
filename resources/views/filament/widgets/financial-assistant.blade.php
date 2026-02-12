<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🤖 Asistente Financiero
        </x-slot>

        <x-slot name="description">
            Puedo ayudarte a crear transacciones, consultar gastos y más
        </x-slot>

        <x-slot name="headerEnd">
            <x-filament::button
                color="gray"
                size="sm"
                wire:click="clearConversation"
                icon="heroicon-o-arrow-path"
            >
                Nueva conversación
            </x-filament::button>
        </x-slot>

        <div class="space-y-4">
            <!-- Messages Container -->
            <div class="h-96 overflow-y-auto space-y-3 p-4 bg-gray-50 dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
                @forelse($messages as $msg)
                    <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] rounded-lg px-4 py-3 {{ $msg['role'] === 'user' ? 'bg-primary-600 text-white' : 'bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700' }}">
                            @if($msg['role'] === 'user')
                                <div class="text-sm font-medium mb-1 text-primary-100">Tú</div>
                            @else
                                <div class="text-sm font-medium mb-1 text-gray-600 dark:text-gray-400">🤖 Asistente</div>
                            @endif

                            <div class="text-sm whitespace-pre-wrap {{ $msg['role'] === 'user' ? 'text-white' : 'text-gray-900 dark:text-gray-100' }}">
                                {{ $msg['content'] }}
                            </div>

                            <div class="text-xs mt-2 {{ $msg['role'] === 'user' ? 'text-primary-200' : 'text-gray-500 dark:text-gray-500' }}">
                                {{ \Carbon\Carbon::parse($msg['created_at'])->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-gray-500 dark:text-gray-400 py-12">
                        <div class="text-6xl mb-4">👋</div>
                        <p class="text-lg font-medium mb-2">¡Hola! Soy tu asistente financiero</p>
                        <div class="text-sm space-y-2 max-w-md mx-auto">
                            <p class="font-medium">Puedo ayudarte a:</p>
                            <ul class="text-left space-y-1">
                                <li>✅ <strong>Crear transacciones:</strong> "Gasté 500 en café ayer"</li>
                                <li>📊 <strong>Consultar gastos:</strong> "¿Cuánto gasté en restaurantes este mes?"</li>
                                <li>💰 <strong>Ver balances:</strong> "¿Cuál es mi balance actual?"</li>
                                <li>❓ <strong>Responder preguntas:</strong> sobre tus finanzas</li>
                            </ul>
                        </div>
                    </div>
                @endforelse

                @if($isLoading)
                    <div class="flex justify-start">
                        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-3">
                            <div class="flex items-center space-x-2">
                                <svg class="animate-spin h-4 w-4 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Pensando...</span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Input Form -->
            <form wire:submit="sendMessage" class="flex gap-2">
                <input
                    type="text"
                    wire:model="message"
                    placeholder="Escribe tu mensaje..."
                    class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    @if($isLoading) disabled @endif
                    autofocus
                />
                <x-filament::button
                    type="submit"
                    :disabled="$isLoading"
                    icon="heroicon-o-paper-airplane"
                >
                    Enviar
                </x-filament::button>
            </form>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
