<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            ⚡ Accesos Rápidos a Transacciones
        </x-slot>

        <x-slot name="description">
            Crea transacciones frecuentes con un solo clic
        </x-slot>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {{-- Templates Recurrentes --}}
            @if(isset($templates['recurring']) && $templates['recurring']->isNotEmpty())
                <div class="col-span-full">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        🔄 Gastos Recurrentes
                    </h3>
                </div>

                @foreach($templates['recurring'] as $template)
                    <div class="relative rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary-500 dark:hover:border-primary-500 transition-colors">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 dark:text-white">
                                    {{ $template->name }}
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <span class="inline-flex items-center">
                                        @if($template->category->type === 'income')
                                            <span class="text-green-600">📈</span>
                                        @else
                                            <span class="text-red-600">📉</span>
                                        @endif
                                        <span class="ml-1">{{ $template->category->name }}</span>
                                    </span>
                                    <span class="mx-1">•</span>
                                    <span>🏦 {{ $template->account->name }}</span>
                                </p>
                            </div>

                            @if($template->auto_create)
                                <span class="inline-flex items-center rounded-full bg-yellow-100 dark:bg-yellow-900 px-2 py-1 text-xs font-medium text-yellow-800 dark:text-yellow-200">
                                    ⚡ Auto
                                </span>
                            @endif
                        </div>

                        @if($template->amount)
                            {{-- Template con monto fijo --}}
                            <div class="text-lg font-bold text-gray-900 dark:text-white mb-3">
                                ${{ number_format($template->amount, 2) }}
                            </div>

                            <button
                                wire:click="createFromTemplate({{ $template->id }})"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                wire:target="createFromTemplate({{ $template->id }})"
                                class="w-full inline-flex items-center justify-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 transition-colors disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="createFromTemplate({{ $template->id }})">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Crear Ahora
                                </span>
                                <span wire:loading wire:target="createFromTemplate({{ $template->id }})">
                                    Creando...
                                </span>
                            </button>
                        @else
                            {{-- Template con monto variable --}}
                            <div class="space-y-2">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    wire:model.defer="amounts.{{ $template->id }}"
                                    placeholder="Ingresa el monto"
                                    class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                />

                                <button
                                    wire:click="createWithAmount({{ $template->id }})"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    wire:target="createWithAmount({{ $template->id }})"
                                    class="w-full inline-flex items-center justify-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700 transition-colors disabled:opacity-50"
                                >
                                    <span wire:loading.remove wire:target="createWithAmount({{ $template->id }})">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Crear
                                    </span>
                                    <span wire:loading wire:target="createWithAmount({{ $template->id }})">
                                        Creando...
                                    </span>
                                </button>
                            </div>
                        @endif

                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            📅
                            @switch($template->recurrence_type)
                                @case('monthly')
                                    Cada día {{ $template->recurrence_day }}
                                    @break
                                @case('weekly')
                                    Cada {{ ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'][$template->recurrence_day] }}
                                    @break
                                @case('yearly')
                                    Anual
                                    @break
                            @endswitch
                        </div>
                    </div>
                @endforeach
            @endif

            {{-- Templates de Una Vez (NO RECURRENTES) --}}
            @if(isset($templates['oneTime']) && $templates['oneTime']->isNotEmpty())
                <div class="col-span-full mt-4">
                    <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                        💡 Transacciones Frecuentes
                    </h3>
                </div>

                @foreach($templates['oneTime'] as $template)
                    <div class="relative rounded-lg border border-gray-200 dark:border-gray-700 p-4 hover:border-primary-500 dark:hover:border-primary-500 transition-colors">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 dark:text-white">
                                    {{ $template->name }}
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    <span class="inline-flex items-center">
                                        @if($template->category->type === 'income')
                                            <span class="text-green-600">📈 Ingreso</span>
                                        @else
                                            <span class="text-red-600">📉 Gasto</span>
                                        @endif
                                        <span class="mx-2">•</span>
                                        <span>{{ $template->category->name }}</span>
                                    </span>
                                </p>
                            </div>

                            @if($template->amount)
                                <span class="text-lg font-bold text-gray-900 dark:text-white">
                                    ${{ number_format($template->amount, 2) }}
                                </span>
                            @endif
                        </div>

                        @if($template->amount)
                            {{-- Template con monto fijo --}}
                            <button
                                wire:click="createFromTemplate({{ $template->id }})"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                wire:target="createFromTemplate({{ $template->id }})"
                                class="w-full inline-flex items-center justify-center rounded-lg bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 transition-colors disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="createFromTemplate({{ $template->id }})">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Crear Transacción
                                </span>
                                <span wire:loading wire:target="createFromTemplate({{ $template->id }})">
                                    Creando...
                                </span>
                            </button>
                        @else
                            {{-- Template con monto variable + FECHA PERSONALIZADA --}}
                            <div class="space-y-2">
                                {{-- ✅ Campo de fecha --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        📅 Fecha
                                    </label>
                                    <input
                                        type="date"
                                        wire:model.defer="dates.{{ $template->id }}"
                                        value="{{ now()->format('Y-m-d') }}"
                                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    />
                                </div>

                                {{-- Campo de monto --}}
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        💵 Monto
                                    </label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        wire:model.defer="amounts.{{ $template->id }}"
                                        placeholder="Ingresa el monto"
                                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    />
                                </div>

                                {{-- Botón crear --}}
                                <button
                                    wire:click="createWithAmount({{ $template->id }})"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    wire:target="createWithAmount({{ $template->id }})"
                                    class="w-full inline-flex items-center justify-center rounded-lg bg-gray-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 transition-colors disabled:opacity-50"
                                >
                                    <span wire:loading.remove wire:target="createWithAmount({{ $template->id }})">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        Crear
                                    </span>
                                    <span wire:loading wire:target="createWithAmount({{ $template->id }})">
                                        Creando...
                                    </span>
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            @endif

            {{-- Mensaje cuando no hay templates --}}
            @if((!isset($templates['recurring']) || $templates['recurring']->isEmpty()) &&
                (!isset($templates['oneTime']) || $templates['oneTime']->isEmpty()))
                <div class="col-span-full text-center py-8">
                    <p class="text-gray-500 dark:text-gray-400 mb-4">
                        No tienes templates configurados aún
                    </p>

                    href="{{ \App\Filament\Resources\TransactionTemplateResource::getUrl('create') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700"
                    >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Crear tu primer template
                    </a>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
