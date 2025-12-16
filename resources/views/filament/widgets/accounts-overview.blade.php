<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🏦 Resumen de Cuentas
        </x-slot>

        <x-slot name="description">
            Vista general del balance de todas tus cuentas
        </x-slot>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach($this->getViewData()['accounts'] as $account)
                <div class="relative rounded-xl border-2 bg-white dark:bg-gray-800 overflow-hidden hover:shadow-xl transition-all duration-300"
                     style="border-color: {{ $account['color'] ?? '#3B82F6' }};">

                    {{-- Header con Tipo y Nombre --}}
                    <div class="p-5 pb-4" style="background: linear-gradient(135deg, {{ $account['color'] ?? '#3B82F6' }}15 0%, {{ $account['color'] ?? '#3B82F6' }}05 100%);">
                        {{-- Badge del Tipo --}}
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded-full"
                                  style="background-color: {{ $account['color'] ?? '#3B82F6' }}; color: white;">
                                {{ $account['type_label'] ?? '🏦 Cuenta' }}
                            </span>

                            @if(!($account['include_in_totals'] ?? true))
                                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-md bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                                    📊 Excluida
                                </span>
                            @endif
                        </div>

                        {{-- Nombre de la Cuenta --}}
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span class="text-3xl">{{ $account['icon'] ?? '🏦' }}</span>
                            <span class="truncate">{{ $account['name'] }}</span>
                        </h3>
                    </div>

                    {{-- Body con Balances --}}
                    <div class="p-5 pt-4 space-y-4">
                        {{-- Balance Actual --}}
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                Balance Actual
                            </p>
                            <p class="text-3xl font-bold {{ $account['current_balance'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                ${{ number_format($account['current_balance'], 2) }}
                            </p>
                        </div>

                        {{-- Separador --}}
                        <div class="border-t border-gray-200 dark:border-gray-700"></div>

                        {{-- Balance del Mes --}}
                        <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-600 dark:text-gray-300">
                                    Balance del Mes
                                </span>
                                <span class="text-lg font-bold {{ $account['month_balance'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $account['month_balance'] >= 0 ? '+' : '' }}${{ number_format($account['month_balance'], 2) }}
                                </span>
                            </div>

                            {{-- Ingresos y Gastos del Mes --}}
                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">
                                        📈 Ingresos
                                    </span>
                                    <span class="text-sm font-semibold text-green-600 dark:text-green-400">
                                        ${{ number_format($account['month_income'], 2) }}
                                    </span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">
                                        📉 Gastos
                                    </span>
                                    <span class="text-sm font-semibold text-red-600 dark:text-red-400">
                                        ${{ number_format($account['month_expense'], 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Estadísticas Adicionales --}}
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                                <span class="text-base">💳</span>
                                <span>{{ $account['transaction_count'] }} transacc.</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                                <span class="text-base">🏦</span>
                                <span>${{ number_format($account['initial_balance'], 0) }} inicial</span>
                            </div>
                        </div>
                    </div>

                    {{-- Footer con Botón --}}
                    <div class="p-4 pt-0">
                        <a href="{{ route('filament.app.resources.accounts.view', ['record' => $account['id']]) }}"
                           class="block w-full text-center px-4 py-2.5 rounded-lg font-semibold text-sm transition-all duration-200 hover:shadow-md"
                           style="background-color: {{ $account['color'] ?? '#3B82F6' }}; color: white;">
                            👁️ Ver Detalles Completos
                        </a>
                    </div>
                </div>
            @endforeach

            {{-- Estado vacío --}}
            @if(count($this->getViewData()['accounts']) === 0)
                <div class="col-span-full">
                    <div class="text-center py-16 bg-gray-50 dark:bg-gray-800 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600">
                        <div class="text-6xl mb-4">🏦</div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            No tienes cuentas creadas
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                            Crea tu primera cuenta para comenzar a gestionar tus finanzas
                        </p>
                        <a href="{{ route('filament.app.resources.accounts.create') }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-lg shadow-sm transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            Crear Primera Cuenta
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
