<div>
    @if($showModal)
    <div
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
    >
        <div class="flex min-h-screen items-center justify-center p-4">
            <!-- Background overlay -->
            <div
                class="fixed inset-0 bg-black/50 transition-opacity"
                wire:click="closeModal"
            ></div>

            <!-- Modal panel -->
            <div class="relative w-full max-w-5xl transform rounded-xl bg-white dark:bg-gray-900 shadow-2xl transition-all">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white" id="modal-title">
                            Crear Transacciones Masivas
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            Carga múltiples transacciones con los mismos datos base
                        </p>
                    </div>
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors"
                    >
                        <x-heroicon-o-x-mark class="h-6 w-6" />
                    </button>
                </div>

                <!-- Content -->
                <div class="px-6 py-5 max-h-[70vh] overflow-y-auto">
                    <!-- SECCIÓN 1: Datos Comunes -->
                    <div class="mb-6 rounded-lg bg-gray-50 dark:bg-gray-800 p-5 border border-gray-200 dark:border-gray-700">
                        <h3 class="mb-4 text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide">
                            Datos Comunes
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Cuenta -->
                            <div>
                                <label for="account_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                                    Cuenta <span class="text-red-600">*</span>
                                </label>
                                <select
                                    id="account_id"
                                    wire:model="account_id"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="">Seleccionar cuenta...</option>
                                    @foreach($this->accounts as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('account_id')
                                    <p class="mt-1 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Tipo de Transacción -->
                            <div>
                                <label for="transaction_type" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                                    Tipo <span class="text-red-600">*</span>
                                </label>
                                <select
                                    id="transaction_type"
                                    wire:model.live="transaction_type"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                >
                                    <option value="">Seleccionar tipo...</option>
                                    <option value="expense">Egreso</option>
                                    <option value="income">Ingreso</option>
                                </select>
                                @error('transaction_type')
                                    <p class="mt-1 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Categoría -->
                            <div>
                                <label for="category_id" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                                    Categoría <span class="text-red-600">*</span>
                                </label>
                                <select
                                    id="category_id"
                                    wire:model="category_id"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 disabled:bg-gray-100 dark:disabled:bg-gray-800 disabled:text-gray-500"
                                    @if(!$transaction_type) disabled @endif
                                >
                                    <option value="">{{ $transaction_type ? 'Seleccionar categoría...' : 'Primero selecciona un tipo' }}</option>
                                    @foreach($this->categories as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="mt-1 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Descripción -->
                            <div>
                                <label for="description" class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">
                                    Descripción <span class="text-red-600">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="description"
                                    wire:model="description"
                                    placeholder="Ej: Supermercado DÍA"
                                    maxlength="255"
                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 placeholder-gray-400"
                                >
                                @error('description')
                                    <p class="mt-1 text-sm font-medium text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: Líneas de Transacciones -->
                    <div class="mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wide">
                                Transacciones
                            </h3>

                            <!-- Botones de fecha rápida para todas las líneas -->
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm text-gray-600 dark:text-gray-300 hidden sm:inline">Aplicar a todas:</span>
                                <div class="flex flex-wrap gap-1.5">
                                    <button
                                        type="button"
                                        wire:click="setDateForAllLines('{{ now()->format('Y-m-d') }}')"
                                        class="px-3 py-1.5 text-sm font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700 transition-colors"
                                    >
                                        Hoy
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="setDateForAllLines('{{ now()->subDay()->format('Y-m-d') }}')"
                                        class="px-3 py-1.5 text-sm font-medium rounded-md bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                    >
                                        Ayer
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="setDateForAllLines('{{ now()->subWeek()->format('Y-m-d') }}')"
                                        class="px-3 py-1.5 text-sm font-medium rounded-md bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                    >
                                        -1 sem
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="setDateForAllLines('{{ now()->subMonth()->format('Y-m-d') }}')"
                                        class="px-3 py-1.5 text-sm font-medium rounded-md bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors"
                                    >
                                        -1 mes
                                    </button>
                                </div>
                            </div>
                        </div>

                        @error('lines')
                            <div class="mb-4 p-3 rounded-lg bg-red-100 dark:bg-red-900/30 border border-red-300 dark:border-red-700">
                                <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ $message }}</p>
                            </div>
                        @enderror

                        <!-- Desktop Table -->
                        <div class="hidden md:block overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
                            <table class="w-full">
                                <thead class="bg-gray-100 dark:bg-gray-800">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-sm font-bold text-gray-700 dark:text-gray-200 uppercase w-52">Fecha *</th>
                                        <th class="px-4 py-3 text-left text-sm font-bold text-gray-700 dark:text-gray-200 uppercase w-40">Monto *</th>
                                        <th class="px-4 py-3 text-left text-sm font-bold text-gray-700 dark:text-gray-200 uppercase">Notas</th>
                                        <th class="px-4 py-3 w-14"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                    @foreach($lines as $index => $line)
                                        <tr wire:key="line-{{ $line['id'] }}">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2" x-data="{ showQuick: false }">
                                                    <input
                                                        type="date"
                                                        wire:model="lines.{{ $index }}.date"
                                                        max="{{ now()->format('Y-m-d') }}"
                                                        class="flex-1 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                                    >
                                                    <div class="relative">
                                                        <button
                                                            type="button"
                                                            @click="showQuick = !showQuick"
                                                            class="p-2 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                                            title="Fechas rápidas"
                                                        >
                                                            <x-heroicon-o-calendar-days class="h-5 w-5" />
                                                        </button>
                                                        <div
                                                            x-show="showQuick"
                                                            @click.outside="showQuick = false"
                                                            x-transition
                                                            class="absolute right-0 top-full mt-1 z-20 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-600 py-1 min-w-[140px]"
                                                        >
                                                            <button type="button" wire:click="setDateForLine({{ $index }}, '{{ now()->format('Y-m-d') }}')" @click="showQuick = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Hoy</button>
                                                            <button type="button" wire:click="setDateForLine({{ $index }}, '{{ now()->subDay()->format('Y-m-d') }}')" @click="showQuick = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Ayer</button>
                                                            <button type="button" wire:click="setDateForLine({{ $index }}, '{{ now()->subWeek()->format('Y-m-d') }}')" @click="showQuick = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Hace 1 semana</button>
                                                            <button type="button" wire:click="setDateForLine({{ $index }}, '{{ now()->subMonth()->format('Y-m-d') }}')" @click="showQuick = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Hace 1 mes</button>
                                                            <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                                                            <button type="button" wire:click="setDateForLine({{ $index }}, '{{ now()->startOfMonth()->format('Y-m-d') }}')" @click="showQuick = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Inicio de mes</button>
                                                            <button type="button" wire:click="setDateForLine({{ $index }}, '{{ now()->subMonth()->startOfMonth()->format('Y-m-d') }}')" @click="showQuick = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">Mes anterior</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <div class="relative">
                                                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-400 font-medium pointer-events-none">$</span>
                                                    <input
                                                        type="number"
                                                        wire:model="lines.{{ $index }}.amount"
                                                        step="0.01"
                                                        min="0.01"
                                                        placeholder="0.00"
                                                        class="w-full pl-8 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-right"
                                                    >
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <input
                                                    type="text"
                                                    wire:model="lines.{{ $index }}.notes"
                                                    placeholder="Opcional"
                                                    maxlength="500"
                                                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 placeholder-gray-400"
                                                >
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if(count($lines) > 1)
                                                    <button
                                                        type="button"
                                                        wire:click="removeLine({{ $index }})"
                                                        class="p-2 rounded-lg text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 transition-colors"
                                                        title="Eliminar línea"
                                                    >
                                                        <x-heroicon-o-trash class="h-5 w-5" />
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Mobile Cards -->
                        <div class="md:hidden space-y-4">
                            @foreach($lines as $index => $line)
                                <div wire:key="card-{{ $line['id'] }}" class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">Transacción #{{ $index + 1 }}</span>
                                        @if(count($lines) > 1)
                                            <button
                                                type="button"
                                                wire:click="removeLine({{ $index }})"
                                                class="p-1.5 text-red-600 hover:bg-red-100 dark:hover:bg-red-900/30 rounded-lg transition-colors"
                                                title="Eliminar"
                                            >
                                                <x-heroicon-o-trash class="h-5 w-5" />
                                            </button>
                                        @endif
                                    </div>
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Fecha *</label>
                                            <input
                                                type="date"
                                                wire:model="lines.{{ $index }}.date"
                                                max="{{ now()->format('Y-m-d') }}"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            >
                                            <div class="flex flex-wrap gap-2 mt-2">
                                                <button type="button" wire:click="setDateForLine({{ $index }}, '{{ now()->format('Y-m-d') }}')" class="px-3 py-1.5 text-sm font-medium rounded-md bg-blue-600 text-white hover:bg-blue-700">Hoy</button>
                                                <button type="button" wire:click="setDateForLine({{ $index }}, '{{ now()->subDay()->format('Y-m-d') }}')" class="px-3 py-1.5 text-sm font-medium rounded-md bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-500">Ayer</button>
                                                <button type="button" wire:click="setDateForLine({{ $index }}, '{{ now()->subWeek()->format('Y-m-d') }}')" class="px-3 py-1.5 text-sm font-medium rounded-md bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-500">-1 sem</button>
                                                <button type="button" wire:click="setDateForLine({{ $index }}, '{{ now()->subMonth()->format('Y-m-d') }}')" class="px-3 py-1.5 text-sm font-medium rounded-md bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-white hover:bg-gray-300 dark:hover:bg-gray-500">-1 mes</button>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Monto *</label>
                                            <div class="relative">
                                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500 dark:text-gray-400 font-medium">$</span>
                                                <input
                                                    type="number"
                                                    wire:model="lines.{{ $index }}.amount"
                                                    step="0.01"
                                                    min="0.01"
                                                    placeholder="0.00"
                                                    class="w-full pl-8 rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 text-right"
                                                >
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Notas</label>
                                            <input
                                                type="text"
                                                wire:model="lines.{{ $index }}.notes"
                                                placeholder="Opcional"
                                                maxlength="500"
                                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 placeholder-gray-400"
                                            >
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Botón agregar línea -->
                        <button
                            type="button"
                            wire:click="addLine"
                            class="mt-4 w-full md:w-auto inline-flex items-center justify-center gap-2 rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:border-blue-500 hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                        >
                            <x-heroicon-o-plus class="h-5 w-5" />
                            Agregar línea
                        </button>
                    </div>

                    <!-- SECCIÓN 3: Resumen -->
                    <div class="rounded-lg bg-blue-50 dark:bg-blue-900/20 p-4 border border-blue-200 dark:border-blue-800">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div class="text-gray-800 dark:text-gray-200">
                                <span class="font-medium">Total de transacciones:</span>
                                <span class="ml-2 text-xl font-bold text-blue-700 dark:text-blue-400">{{ $this->totalCount }}</span>
                            </div>
                            <div class="text-gray-800 dark:text-gray-200">
                                <span class="font-medium">Monto total:</span>
                                <span class="ml-2 text-2xl font-bold text-blue-700 dark:text-blue-400">
                                    ${{ number_format($this->totalAmount, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 border-t border-gray-200 dark:border-gray-700 px-6 py-4 bg-gray-50 dark:bg-gray-800">
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="px-5 py-2.5 rounded-lg text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-2 px-6 py-2.5 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm"
                        @if($saving) disabled @endif
                    >
                        <span wire:loading wire:target="save" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Guardando...
                        </span>
                        <span wire:loading.remove wire:target="save" class="flex items-center gap-2">
                            <x-heroicon-o-check class="h-5 w-5" />
                            Crear {{ $this->totalCount }} Transacciones
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
