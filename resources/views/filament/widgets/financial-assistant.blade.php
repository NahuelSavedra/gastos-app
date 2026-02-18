<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-cpu-chip class="w-5 h-5 text-primary-500" />
                Asistente Financiero
            </div>
        </x-slot>

        <x-slot name="description">
            Puedo ayudarte a crear transacciones, consultar gastos y mas
        </x-slot>

        <x-slot name="headerEnd">
            <x-filament::button
                color="gray"
                size="sm"
                wire:click="clearConversation"
                icon="heroicon-o-arrow-path"
            >
                Nueva conversacion
            </x-filament::button>
        </x-slot>

        <div class="space-y-4">
            <!-- Messages Container -->
            <div class="h-96 overflow-y-auto space-y-3 p-4 bg-zinc-50 dark:bg-zinc-950 rounded-lg border border-zinc-200 dark:border-zinc-800">
                @forelse($messages as $msg)
                    <div class="flex {{ $msg['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[85%] rounded-lg px-4 py-3 {{ $msg['role'] === 'user' ? 'bg-zinc-700 dark:bg-zinc-600 text-white' : 'bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800' }}">
                            @if($msg['role'] === 'user')
                                <div class="text-sm font-medium mb-1 text-zinc-300">Tu</div>
                            @else
                                <div class="flex items-center gap-1.5 text-sm font-medium mb-1 text-zinc-600 dark:text-zinc-400">
                                    <x-heroicon-m-cpu-chip class="w-3.5 h-3.5" />
                                    Asistente
                                </div>
                            @endif

                            <div class="text-sm whitespace-pre-wrap {{ $msg['role'] === 'user' ? 'text-white' : 'text-zinc-900 dark:text-zinc-100' }}">
                                {{ $msg['content'] }}
                            </div>

                            <div class="text-xs mt-2 {{ $msg['role'] === 'user' ? 'text-zinc-400' : 'text-zinc-500 dark:text-zinc-500' }}">
                                {{ \Carbon\Carbon::parse($msg['created_at'])->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-zinc-500 dark:text-zinc-400 py-12">
                        <x-heroicon-o-hand-raised class="w-12 h-12 text-zinc-400 mx-auto mb-4" />
                        <p class="text-lg font-medium mb-2">Hola! Soy tu asistente financiero</p>
                        <div class="text-sm space-y-2 max-w-md mx-auto">
                            <p class="font-medium">Puedo ayudarte a:</p>
                            <ul class="text-left space-y-1.5">
                                <li class="flex items-start gap-2">
                                    <x-heroicon-m-plus-circle class="w-4 h-4 text-emerald-500 mt-0.5 flex-shrink-0" />
                                    <span><strong>Crear transacciones:</strong> "Gaste 500 en cafe ayer"</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <x-heroicon-m-chart-bar class="w-4 h-4 text-sky-500 mt-0.5 flex-shrink-0" />
                                    <span><strong>Consultar gastos:</strong> "Cuanto gaste en restaurantes este mes?"</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <x-heroicon-m-wallet class="w-4 h-4 text-amber-500 mt-0.5 flex-shrink-0" />
                                    <span><strong>Ver balances:</strong> "Cual es mi balance actual?"</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <x-heroicon-m-question-mark-circle class="w-4 h-4 text-zinc-500 mt-0.5 flex-shrink-0" />
                                    <span><strong>Responder preguntas:</strong> sobre tus finanzas</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                @endforelse

                @if($isLoading)
                    <div class="flex justify-start">
                        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg px-4 py-3">
                            <div class="flex items-center space-x-2">
                                <svg class="animate-spin h-4 w-4 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">Pensando...</span>
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
                    class="flex-1 rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
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
