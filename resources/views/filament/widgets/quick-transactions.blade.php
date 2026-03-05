<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-bolt class="w-5 h-5 text-amber-500" />
                Accesos Rápidos
            </div>
        </x-slot>

        <x-slot name="description">
            Crea transacciones frecuentes con un solo clic
        </x-slot>

        <div class="space-y-6">
            {{-- Templates Recurrentes --}}
            @if(isset($templates['recurring']) && $templates['recurring']->isNotEmpty())
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-5 h-5 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                            <x-heroicon-m-arrow-path class="w-3 h-3 text-amber-600 dark:text-amber-400" />
                        </div>
                        <h3 class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Gastos Recurrentes</h3>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($templates['recurring'] as $template)
                            @php
                                $isIncome = $template->category->type === 'income';
                                $accentBg = $isIncome ? 'bg-emerald-50 dark:bg-emerald-950/20 border-emerald-200 dark:border-emerald-800/50' : 'bg-rose-50 dark:bg-rose-950/10 border-rose-200 dark:border-rose-800/30';
                                $accentColor = $isIncome ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400';
                                $btnColor = $isIncome ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-primary-600 hover:bg-primary-700';
                            @endphp

                            <div class="rounded-xl border {{ $accentBg }} p-4 transition-shadow hover:shadow-sm">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-zinc-900 dark:text-white truncate">{{ $template->name }}</h4>
                                        <p class="flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                            @if($isIncome)
                                                <x-heroicon-m-arrow-trending-up class="w-3 h-3 text-emerald-500 flex-shrink-0" />
                                            @else
                                                <x-heroicon-m-arrow-trending-down class="w-3 h-3 text-rose-500 flex-shrink-0" />
                                            @endif
                                            {{ $template->category->name }} · {{ $template->account->name }}
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-1.5 flex-shrink-0 ml-2">
                                        @if($template->auto_create)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 dark:bg-amber-900/30 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-400">
                                                <x-heroicon-m-bolt class="w-3 h-3" />
                                                Auto
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                @if($template->amount)
                                    <div class="text-xl font-bold {{ $accentColor }} mb-3">
                                        ${{ number_format($template->amount, 2) }}
                                    </div>
                                    <button
                                        wire:click="createFromTemplate({{ $template->id }})"
                                        wire:loading.attr="disabled"
                                        wire:loading.class="opacity-50 cursor-not-allowed"
                                        wire:target="createFromTemplate({{ $template->id }})"
                                        class="w-full inline-flex items-center justify-center rounded-lg {{ $btnColor }} px-3 py-2 text-sm font-medium text-white shadow-sm transition-colors disabled:opacity-50"
                                    >
                                        <span wire:loading.remove wire:target="createFromTemplate({{ $template->id }})" class="inline-flex items-center gap-1.5">
                                            <x-heroicon-m-plus class="w-4 h-4" />
                                            Crear Ahora
                                        </span>
                                        <span wire:loading wire:target="createFromTemplate({{ $template->id }})">Creando...</span>
                                    </button>
                                @else
                                    <div class="space-y-2">
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400 font-medium">$</span>
                                            <input
                                                type="number" step="0.01" min="0"
                                                wire:model.defer="amounts.{{ $template->id }}"
                                                placeholder="0.00"
                                                class="pl-7 block w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                            />
                                        </div>
                                        <button
                                            wire:click="createWithAmount({{ $template->id }})"
                                            wire:loading.attr="disabled"
                                            wire:loading.class="opacity-50 cursor-not-allowed"
                                            wire:target="createWithAmount({{ $template->id }})"
                                            class="w-full inline-flex items-center justify-center rounded-lg {{ $btnColor }} px-3 py-2 text-sm font-medium text-white shadow-sm transition-colors disabled:opacity-50"
                                        >
                                            <span wire:loading.remove wire:target="createWithAmount({{ $template->id }})" class="inline-flex items-center gap-1.5">
                                                <x-heroicon-m-plus class="w-4 h-4" />
                                                Crear
                                            </span>
                                            <span wire:loading wire:target="createWithAmount({{ $template->id }})">Creando...</span>
                                        </button>
                                    </div>
                                @endif

                                <div class="mt-2.5 flex items-center gap-1 text-xs text-zinc-400 dark:text-zinc-500">
                                    <x-heroicon-m-calendar class="w-3 h-3" />
                                    @switch($template->recurrence_type)
                                        @case('monthly') Cada día {{ $template->recurrence_day }} @break
                                        @case('weekly') Cada {{ ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'][$template->recurrence_day] }} @break
                                        @case('yearly') Anual @break
                                    @endswitch
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Templates de Una Vez --}}
            @if(isset($templates['oneTime']) && $templates['oneTime']->isNotEmpty())
                <div>
                    <div class="flex items-center gap-2 mb-3">
                        <div class="w-5 h-5 rounded-full bg-sky-100 dark:bg-sky-900/30 flex items-center justify-center">
                            <x-heroicon-m-light-bulb class="w-3 h-3 text-sky-600 dark:text-sky-400" />
                        </div>
                        <h3 class="text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">Transacciones Frecuentes</h3>
                    </div>

                    <div class="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                        @foreach($templates['oneTime'] as $template)
                            @php
                                $isIncome = $template->category->type === 'income';
                                $accentColor = $isIncome ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-700 dark:text-zinc-300';
                            @endphp

                            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl p-4 hover:border-primary-300 dark:hover:border-primary-700 hover:shadow-sm transition-all">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="font-semibold text-zinc-900 dark:text-white truncate">{{ $template->name }}</h4>
                                        <p class="flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                            @if($isIncome)
                                                <x-heroicon-m-arrow-trending-up class="w-3 h-3 text-emerald-500 flex-shrink-0" />
                                                <span>Ingreso</span>
                                            @else
                                                <x-heroicon-m-arrow-trending-down class="w-3 h-3 text-rose-500 flex-shrink-0" />
                                                <span>Gasto</span>
                                            @endif
                                            <span>· {{ $template->category->name }}</span>
                                        </p>
                                    </div>
                                    @if($template->amount)
                                        <span class="text-base font-bold {{ $accentColor }} flex-shrink-0 ml-2">
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
                                        <span wire:loading.remove wire:target="createFromTemplate({{ $template->id }})" class="inline-flex items-center gap-1.5">
                                            <x-heroicon-m-plus class="w-4 h-4" />
                                            Crear
                                        </span>
                                        <span wire:loading wire:target="createFromTemplate({{ $template->id }})">Creando...</span>
                                    </button>
                                @else
                                    <div class="space-y-2">
                                        <input
                                            type="date"
                                            wire:model.defer="dates.{{ $template->id }}"
                                            value="{{ now()->format('Y-m-d') }}"
                                            class="block w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                        />
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400 font-medium">$</span>
                                            <input
                                                type="number" step="0.01" min="0"
                                                wire:model.defer="amounts.{{ $template->id }}"
                                                placeholder="0.00"
                                                class="pl-7 block w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                            />
                                        </div>
                                        <button
                                            wire:click="createWithAmount({{ $template->id }})"
                                            wire:loading.attr="disabled"
                                            wire:loading.class="opacity-50 cursor-not-allowed"
                                            wire:target="createWithAmount({{ $template->id }})"
                                            class="w-full inline-flex items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800 px-3 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700 transition-colors disabled:opacity-50"
                                        >
                                            <span wire:loading.remove wire:target="createWithAmount({{ $template->id }})" class="inline-flex items-center gap-1.5">
                                                <x-heroicon-m-plus class="w-4 h-4" />
                                                Crear
                                            </span>
                                            <span wire:loading wire:target="createWithAmount({{ $template->id }})">Creando...</span>
                                        </button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Empty state --}}
            @if((!isset($templates['recurring']) || $templates['recurring']->isEmpty()) &&
                (!isset($templates['oneTime']) || $templates['oneTime']->isEmpty()))
                <div class="text-center py-12">
                    <div class="w-14 h-14 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4">
                        <x-heroicon-o-document-plus class="w-7 h-7 text-zinc-400" />
                    </div>
                    <p class="font-medium text-zinc-700 dark:text-zinc-300 mb-1">No tienes templates configurados</p>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-5">Crea atajos para tus gastos más frecuentes</p>
                    <a href="{{ \App\Filament\Resources\TransactionTemplateResource::getUrl('create') }}"
                       class="inline-flex items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-primary-700 transition-colors">
                        <x-heroicon-m-plus class="w-4 h-4" />
                        Crear tu primer template
                    </a>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
