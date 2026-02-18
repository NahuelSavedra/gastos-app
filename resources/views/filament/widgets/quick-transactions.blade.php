<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-bolt class="w-5 h-5 text-primary-500" />
                Accesos Rapidos a Transacciones
            </div>
        </x-slot>

        <x-slot name="description">
            Crea transacciones frecuentes con un solo clic
        </x-slot>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            {{-- Templates Recurrentes --}}
            @if(isset($templates['recurring']) && $templates['recurring']->isNotEmpty())
                <div class="col-span-full">
                    <h3 class="flex items-center gap-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-3">
                        <x-heroicon-m-arrow-path class="w-4 h-4" />
                        Gastos Recurrentes
                    </h3>
                </div>

                @foreach($templates['recurring'] as $template)
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-4 hover:border-primary-400 dark:hover:border-primary-600 transition-colors">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex-1">
                                <h4 class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $template->name }}
                                </h4>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                    <span class="inline-flex items-center gap-1">
                                        @if($template->category->type === 'income')
                                            <x-heroicon-m-arrow-trending-up class="w-3 h-3 text-emerald-500" />
                                        @else
                                            <x-heroicon-m-arrow-trending-down class="w-3 h-3 text-rose-500" />
                                        @endif
                                        <span>{{ $template->category->name }}</span>
                                    </span>
                                    <span class="mx-1">·</span>
                                    <span>{{ $template->account->name }}</span>
                                </p>
                            </div>

                            @if($template->auto_create)
                                <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 dark:bg-amber-950/30 px-1.5 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-400">
                                    <x-heroicon-m-bolt class="w-3 h-3" />
                                    Auto
                                </span>
                            @endif
                        </div>

                        @if($template->amount)
                            <div class="text-lg font-bold text-zinc-900 dark:text-white mb-3">
                                ${{ number_format($template->amount, 2) }}
                            </div>

                            <button
                                wire:click="createFromTemplate({{ $template->id }})"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                wire:target="createFromTemplate({{ $template->id }})"
                                class="w-full inline-flex items-center justify-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 transition-colors disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="createFromTemplate({{ $template->id }})" class="inline-flex items-center gap-1">
                                    <x-heroicon-m-plus class="w-4 h-4" />
                                    Crear Ahora
                                </span>
                                <span wire:loading wire:target="createFromTemplate({{ $template->id }})">
                                    Creando...
                                </span>
                            </button>
                        @else
                            <div class="space-y-2">
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    wire:model.defer="amounts.{{ $template->id }}"
                                    placeholder="Ingresa el monto"
                                    class="block w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                />

                                <button
                                    wire:click="createWithAmount({{ $template->id }})"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    wire:target="createWithAmount({{ $template->id }})"
                                    class="w-full inline-flex items-center justify-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 transition-colors disabled:opacity-50"
                                >
                                    <span wire:loading.remove wire:target="createWithAmount({{ $template->id }})" class="inline-flex items-center gap-1">
                                        <x-heroicon-m-plus class="w-4 h-4" />
                                        Crear
                                    </span>
                                    <span wire:loading wire:target="createWithAmount({{ $template->id }})">
                                        Creando...
                                    </span>
                                </button>
                            </div>
                        @endif

                        <div class="mt-2 flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400">
                            <x-heroicon-m-calendar class="w-3 h-3" />
                            @switch($template->recurrence_type)
                                @case('monthly')
                                    Cada dia {{ $template->recurrence_day }}
                                    @break
                                @case('weekly')
                                    Cada {{ ['', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'][$template->recurrence_day] }}
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
                    <h3 class="flex items-center gap-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-3">
                        <x-heroicon-m-light-bulb class="w-4 h-4" />
                        Transacciones Frecuentes
                    </h3>
                </div>

                @foreach($templates['oneTime'] as $template)
                    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg p-4 hover:border-primary-400 dark:hover:border-primary-600 transition-colors">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h4 class="font-semibold text-zinc-900 dark:text-white">
                                    {{ $template->name }}
                                </h4>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                    <span class="inline-flex items-center gap-1">
                                        @if($template->category->type === 'income')
                                            <x-heroicon-m-arrow-trending-up class="w-3 h-3 text-emerald-500" />
                                            <span>Ingreso</span>
                                        @else
                                            <x-heroicon-m-arrow-trending-down class="w-3 h-3 text-rose-500" />
                                            <span>Gasto</span>
                                        @endif
                                        <span class="mx-1">·</span>
                                        <span>{{ $template->category->name }}</span>
                                    </span>
                                </p>
                            </div>

                            @if($template->amount)
                                <span class="text-lg font-bold text-zinc-900 dark:text-white">
                                    ${{ number_format($template->amount, 2) }}
                                </span>
                            @endif
                        </div>

                        @if($template->amount)
                            <button
                                wire:click="createFromTemplate({{ $template->id }})"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                wire:target="createFromTemplate({{ $template->id }})"
                                class="w-full inline-flex items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="createFromTemplate({{ $template->id }})" class="inline-flex items-center gap-1">
                                    <x-heroicon-m-plus class="w-4 h-4" />
                                    Crear Transaccion
                                </span>
                                <span wire:loading wire:target="createFromTemplate({{ $template->id }})">
                                    Creando...
                                </span>
                            </button>
                        @else
                            <div class="space-y-2">
                                {{-- Campo de fecha --}}
                                <div>
                                    <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                        Fecha
                                    </label>
                                    <input
                                        type="date"
                                        wire:model.defer="dates.{{ $template->id }}"
                                        value="{{ now()->format('Y-m-d') }}"
                                        class="block w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    />
                                </div>

                                {{-- Campo de monto --}}
                                <div>
                                    <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1">
                                        Monto
                                    </label>
                                    <input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        wire:model.defer="amounts.{{ $template->id }}"
                                        placeholder="Ingresa el monto"
                                        class="block w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm"
                                    />
                                </div>

                                <button
                                    wire:click="createWithAmount({{ $template->id }})"
                                    wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    wire:target="createWithAmount({{ $template->id }})"
                                    class="w-full inline-flex items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors disabled:opacity-50"
                                >
                                    <span wire:loading.remove wire:target="createWithAmount({{ $template->id }})" class="inline-flex items-center gap-1">
                                        <x-heroicon-m-plus class="w-4 h-4" />
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
                    <x-heroicon-o-document-plus class="w-12 h-12 text-zinc-400 mx-auto mb-4" />
                    <p class="text-zinc-500 dark:text-zinc-400 mb-4">
                        No tienes templates configurados aun
                    </p>

                    <a href="{{ \App\Filament\Resources\TransactionTemplateResource::getUrl('create') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-primary-700 transition-colors">
                        <x-heroicon-m-plus class="w-4 h-4" />
                        Crear tu primer template
                    </a>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
