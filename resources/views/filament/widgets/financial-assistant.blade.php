<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-primary-500 to-violet-600 flex items-center justify-center">
                    <x-heroicon-m-sparkles class="w-3.5 h-3.5 text-white" />
                </div>
                Asistente Financiero
            </div>
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
            {{-- Messages Container --}}
            <div class="h-96 overflow-y-auto space-y-4 p-4 bg-zinc-50 dark:bg-zinc-950 rounded-xl border border-zinc-200 dark:border-zinc-800"
                 id="chat-messages"
                 x-data
                 x-init="$el.scrollTop = $el.scrollHeight"
                 x-on:livewire:update.window="setTimeout(() => $el.scrollTop = $el.scrollHeight, 50)">

                @forelse($messages as $msg)
                    @if($msg['role'] === 'user')
                        {{-- User message --}}
                        <div class="flex justify-end">
                            <div class="max-w-[80%]">
                                <div class="bg-gradient-to-br from-primary-500 to-primary-600 text-white rounded-2xl rounded-tr-sm px-4 py-3 shadow-sm">
                                    <div class="text-sm whitespace-pre-wrap leading-relaxed">{{ $msg['content'] }}</div>
                                </div>
                                <p class="text-xs text-zinc-400 mt-1 text-right">
                                    {{ \Carbon\Carbon::parse($msg['created_at'])->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @else
                        {{-- Assistant message --}}
                        <div class="flex justify-start gap-2.5">
                            <div class="flex-shrink-0 w-7 h-7 rounded-full bg-gradient-to-br from-primary-500 to-violet-600 flex items-center justify-center mt-0.5">
                                <x-heroicon-m-cpu-chip class="w-4 h-4 text-white" />
                            </div>
                            <div class="max-w-[80%]">
                                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm">
                                    <div class="text-sm whitespace-pre-wrap text-zinc-800 dark:text-zinc-100 leading-relaxed">{{ $msg['content'] }}</div>
                                </div>
                                <p class="text-xs text-zinc-400 mt-1">
                                    {{ \Carbon\Carbon::parse($msg['created_at'])->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                    @endif
                @empty
                    {{-- Empty state --}}
                    <div class="flex flex-col items-center justify-center h-full text-center py-6">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary-500 to-violet-600 flex items-center justify-center mb-4 shadow-lg">
                            <x-heroicon-o-sparkles class="w-7 h-7 text-white" />
                        </div>
                        <p class="text-base font-semibold text-zinc-800 dark:text-zinc-200 mb-1">Hola! Soy tu asistente financiero</p>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-5">Preguntame sobre tus finanzas o pedime que cree transacciones</p>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 w-full max-w-sm text-left">
                            @foreach([
                                ['icon' => 'heroicon-m-plus-circle', 'color' => 'text-emerald-500', 'text' => '"Gaste 500 en café ayer"'],
                                ['icon' => 'heroicon-m-chart-bar', 'color' => 'text-sky-500', 'text' => '"¿Cuánto gasté este mes?"'],
                                ['icon' => 'heroicon-m-wallet', 'color' => 'text-amber-500', 'text' => '"¿Cuál es mi balance?"'],
                                ['icon' => 'heroicon-m-tag', 'color' => 'text-violet-500', 'text' => '"Top gastos de restaurantes"'],
                            ] as $suggestion)
                                <button
                                    wire:click="$set('message', {{ json_encode(trim($suggestion['text'], '"')) }})"
                                    class="flex items-center gap-2 p-2.5 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 hover:border-primary-300 dark:hover:border-primary-700 hover:bg-primary-50 dark:hover:bg-primary-950/20 transition-colors text-left group"
                                >
                                    <x-dynamic-component :component="$suggestion['icon']" class="w-4 h-4 {{ $suggestion['color'] }} flex-shrink-0" />
                                    <span class="text-xs text-zinc-600 dark:text-zinc-400 group-hover:text-zinc-900 dark:group-hover:text-zinc-200">{{ $suggestion['text'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforelse

                @if($isLoading)
                    <div class="flex justify-start gap-2.5">
                        <div class="flex-shrink-0 w-7 h-7 rounded-full bg-gradient-to-br from-primary-500 to-violet-600 flex items-center justify-center">
                            <x-heroicon-m-cpu-chip class="w-4 h-4 text-white" />
                        </div>
                        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm">
                            <div class="flex items-center gap-1.5">
                                <span class="w-2 h-2 bg-primary-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                                <span class="w-2 h-2 bg-primary-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                                <span class="w-2 h-2 bg-primary-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Input Form --}}
            <form wire:submit="sendMessage" class="flex gap-2">
                <div class="flex-1 relative">
                    <input
                        type="text"
                        wire:model="message"
                        placeholder="Escribe tu mensaje..."
                        class="w-full rounded-xl border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 pr-4 py-3"
                        @if($isLoading) disabled @endif
                    />
                </div>
                <x-filament::button
                    type="submit"
                    :disabled="$isLoading"
                    icon="heroicon-o-paper-airplane"
                    class="rounded-xl px-4"
                >
                    Enviar
                </x-filament::button>
            </form>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
