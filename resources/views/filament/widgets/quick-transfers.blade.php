<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-arrows-right-left class="w-5 h-5 text-primary-500" />
                Transferencias Rápidas
            </div>
        </x-slot>

        <x-slot name="description">
            Mueve dinero entre cuentas con un solo clic
        </x-slot>

        <div class="space-y-4">
            @if($this->getTemplates()->isEmpty())
                <div class="text-center py-12 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-dashed border-zinc-300 dark:border-zinc-700">
                    <x-heroicon-o-arrows-right-left class="w-12 h-12 text-zinc-400 mx-auto mb-4" />
                    <p class="text-sm font-medium text-zinc-900 dark:text-white mb-1">
                        No hay templates de transferencia
                    </p>
                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mb-4">
                        Crea templates para agilizar tus transferencias habituales
                    </p>
                    <a href="{{ route('filament.app.resources.transfer-templates.index') }}"
                       class="inline-flex items-center justify-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-sm transition-colors text-sm">
                        <x-heroicon-m-plus class="w-4 h-4" />
                        Crear template
                    </a>
                </div>
            @else
                @if(!$selectedTemplateId)
                    {{-- Selección de Template --}}
                    <div>
                        <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider mb-3">
                            Selecciona una transferencia
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($this->getTemplates() as $template)
                                <button
                                    wire:click="selectTemplate({{ $template->id }})"
                                    class="group flex items-center gap-3 p-4 rounded-lg border border-zinc-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 hover:border-primary-400 dark:hover:border-primary-600 hover:shadow-sm transition-all text-left"
                                >
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-lg bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center group-hover:bg-primary-50 dark:group-hover:bg-primary-950/30 transition-colors">
                                            <x-icon name="{{ $template->icon }}" class="w-5 h-5 text-zinc-500 dark:text-zinc-400 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors" />
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-zinc-900 dark:text-white truncate">
                                            {{ $template->name }}
                                        </p>
                                        <div class="flex items-center gap-1 mt-0.5">
                                            <span class="text-xs text-rose-500 truncate max-w-[80px]">{{ $template->fromAccount->name }}</span>
                                            <x-heroicon-m-arrow-right class="w-3 h-3 text-zinc-400 flex-shrink-0" />
                                            <span class="text-xs text-emerald-500 truncate max-w-[80px]">{{ $template->toAccount->name }}</span>
                                        </div>
                                        @if($template->default_amount)
                                            <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">
                                                ${{ number_format($template->default_amount, 2) }}
                                            </p>
                                        @endif
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @else
                    {{-- Formulario de Transferencia --}}
                    <div class="space-y-4">
                        @php
                            $template = $this->getSelectedTemplate();
                        @endphp

                        {{-- Header con template seleccionado --}}
                        <div class="flex items-center justify-between p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-200 dark:border-zinc-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-lg bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 flex items-center justify-center">
                                    <x-icon name="{{ $template->icon }}" class="w-5 h-5 text-zinc-600 dark:text-zinc-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                                        {{ $template->name }}
                                    </p>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-xs font-medium text-rose-600 dark:text-rose-400">{{ $template->fromAccount->name }}</span>
                                        <x-heroicon-m-arrow-right class="w-3 h-3 text-zinc-400 flex-shrink-0" />
                                        <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400">{{ $template->toAccount->name }}</span>
                                    </div>
                                </div>
                            </div>
                            <button
                                wire:click="cancelTransfer"
                                type="button"
                                class="p-1.5 rounded-md text-zinc-400 hover:text-zinc-600 hover:bg-zinc-100 dark:hover:bg-zinc-800 dark:hover:text-zinc-300 transition-colors"
                            >
                                <x-heroicon-m-x-mark class="w-4 h-4" />
                            </button>
                        </div>

                        {{-- Formulario --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                    Monto *
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-zinc-400 font-medium">$</span>
                                    <input
                                        type="number"
                                        wire:model="amount"
                                        step="0.01"
                                        min="0.01"
                                        class="pl-7 w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white text-lg font-semibold shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                        placeholder="0.00"
                                        autofocus
                                    />
                                </div>
                                @error('amount')
                                    <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                    Fecha *
                                </label>
                                <input
                                    type="date"
                                    wire:model="date"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                                />
                                @error('date')
                                    <p class="text-xs text-rose-600 dark:text-rose-400 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium text-zinc-700 dark:text-zinc-300 mb-1.5">
                                    Descripción <span class="text-zinc-400">(opcional)</span>
                                </label>
                                <textarea
                                    wire:model="description"
                                    rows="2"
                                    class="w-full rounded-lg border-zinc-300 dark:border-zinc-700 dark:bg-zinc-800 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500 text-sm"
                                    placeholder="Agrega una nota sobre esta transferencia..."
                                ></textarea>
                            </div>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="flex gap-3 justify-end pt-2 border-t border-zinc-100 dark:border-zinc-800">
                            <button
                                wire:click="cancelTransfer"
                                type="button"
                                class="px-4 py-2 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-zinc-100 dark:bg-zinc-800 hover:bg-zinc-200 dark:hover:bg-zinc-700 rounded-lg transition-colors"
                            >
                                Cancelar
                            </button>
                            <button
                                wire:click="executeTransfer"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-50 cursor-not-allowed"
                                wire:target="executeTransfer"
                                type="button"
                                class="inline-flex items-center gap-2 px-5 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg shadow-sm transition-colors disabled:opacity-50"
                            >
                                <span wire:loading.remove wire:target="executeTransfer" class="inline-flex items-center gap-2">
                                    <x-heroicon-m-arrows-right-left class="w-4 h-4" />
                                    Transferir ${{ number_format($amount ?? 0, 2) }}
                                </span>
                                <span wire:loading wire:target="executeTransfer">
                                    Transfiriendo...
                                </span>
                            </button>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
