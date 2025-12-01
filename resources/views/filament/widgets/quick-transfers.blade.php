<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-bolt class="w-5 h-5 text-primary-500" />
                Transferencias Rápidas
            </div>
        </x-slot>

        <div class="space-y-4">
            @if($this->getTemplates()->isEmpty())
                <div class="text-center py-8">
                    <x-heroicon-o-arrow-path class="w-12 h-12 text-gray-400 mx-auto mb-3" />
                    <p class="text-gray-500">No hay templates configurados</p>
                    <p class="text-sm text-gray-400 mt-1">
                        Crea templates en <a href="{{ route('filament.app.resources.transfer-templates.index') }}" class="text-primary-600 hover:underline">Configuración</a>
                    </p>
                </div>
            @else
                @if(!$selectedTemplateId)
                    {{-- Selección de Template --}}
                    <div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                            Selecciona una transferencia:
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($this->getTemplates() as $template)
                                <button
                                    wire:click="selectTemplate({{ $template->id }})"
                                    class="group relative flex items-center gap-3 p-4 rounded-lg border-2 border-gray-200 dark:border-gray-700 hover:border-{{ $template->color }}-500 hover:bg-{{ $template->color }}-50 dark:hover:bg-{{ $template->color }}-950/20 transition-all"
                                >
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 rounded-full bg-{{ $template->color }}-100 dark:bg-{{ $template->color }}-950 flex items-center justify-center group-hover:scale-110 transition-transform">
                                            <x-icon name="{{ $template->icon }}" class="w-6 h-6 text-{{ $template->color }}-600 dark:text-{{ $template->color }}-400" />
                                        </div>
                                    </div>
                                    <div class="flex-1 text-left">
                                        <p class="font-semibold text-gray-900 dark:text-white">
                                            {{ $template->name }}
                                        </p>
                                        <div class="flex items-center gap-1 text-xs text-gray-500 mt-1">
                                            <span class="truncate">{{ $template->fromAccount->name }}</span>
                                            <x-heroicon-o-arrow-right class="w-3 h-3 flex-shrink-0" />
                                            <span class="truncate">{{ $template->toAccount->name }}</span>
                                        </div>
                                        @if($template->default_amount)
                                            <p class="text-xs text-gray-400 mt-1">
                                                Monto sugerido: ${{ number_format($template->default_amount, 2) }}
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
                        <div class="flex items-center justify-between p-4 bg-{{ $template->color }}-50 dark:bg-{{ $template->color }}-950/20 rounded-lg border border-{{ $template->color }}-200 dark:border-{{ $template->color }}-800">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-{{ $template->color }}-100 dark:bg-{{ $template->color }}-950 flex items-center justify-center">
                                    <x-icon name="{{ $template->icon }}" class="w-6 h-6 text-{{ $template->color }}-600" />
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ $template->name }}
                                    </p>
                                    <div class="flex items-center gap-2 text-sm">
                                        <span class="text-danger-600 font-medium">{{ $template->fromAccount->name }}</span>
                                        <x-heroicon-o-arrow-right class="w-4 h-4 text-gray-400" />
                                        <span class="text-success-600 font-medium">{{ $template->toAccount->name }}</span>
                                    </div>
                                </div>
                            </div>
                            <button
                                wire:click="cancelTransfer"
                                type="button"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <x-heroicon-o-x-mark class="w-5 h-5" />
                            </button>
                        </div>

                        {{-- Formulario --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Monto *
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500">$</span>
                                    <input
                                        type="number"
                                        wire:model="amount"
                                        step="0.01"
                                        min="0.01"
                                        class="pl-8 w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-lg font-semibold"
                                        placeholder="0.00"
                                        autofocus
                                    />
                                </div>
                                @error('amount')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Fecha *
                                </label>
                                <input
                                    type="date"
                                    wire:model="date"
                                    max="{{ now()->format('Y-m-d') }}"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                                />
                                @error('date')
                                <p class="text-sm text-danger-600 mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Descripción (opcional)
                                </label>
                                <textarea
                                    wire:model="description"
                                    rows="2"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900"
                                    placeholder="Agrega una nota sobre esta transferencia..."
                                ></textarea>
                            </div>
                        </div>

                        {{-- Botones de acción --}}
                        <div class="flex gap-3 justify-end pt-2">
                            <button
                                wire:click="cancelTransfer"
                                type="button"
                                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 rounded-lg transition"
                            >
                                Cancelar
                            </button>
                            <button
                                wire:click="executeTransfer"
                                type="button"
                                class="px-6 py-2 text-sm font-medium text-white bg-{{ $template->color }}-600 hover:bg-{{ $template->color }}-700 rounded-lg transition flex items-center gap-2"
                            >
                                <x-heroicon-o-check class="w-5 h-5" />
                                Transferir ${{ number_format($amount ?? 0, 2) }}
                            </button>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
