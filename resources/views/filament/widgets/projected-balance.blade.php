<x-filament-widgets::widget>
    @php
        $data = $this->getViewData();
        $currentBalance = $data['currentBalance'];
        $projectedBalance = $data['projectedBalance'];
        $accountsCount = $data['accountsCount'];
        $pendingExpenses = $data['pendingExpenses'];
        $totalPendingExpenses = $data['totalPendingExpenses'];
        $hasEstimated = $data['hasEstimated'];
        $unknownTemplates = $data['unknownTemplates'];
    @endphp

    <div class="space-y-4">
        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Balance Actual --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">💰 Balance Actual</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">
                            ${{ number_format($currentBalance, 2) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            {{ $accountsCount }} cuenta{{ $accountsCount !== 1 ? 's' : '' }} incluida{{ $accountsCount !== 1 ? 's' : '' }}
                        </p>
                    </div>
                    <div class="text-4xl">💵</div>
                </div>
            </div>

            {{-- Balance Proyectado --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">🔮 Balance Proyectado</p>
                        <p class="text-3xl font-bold {{ $projectedBalance >= 0 ? 'text-green-600' : 'text-red-600' }} mt-2">
                            ${{ number_format($projectedBalance, 2) }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                            @if(count($pendingExpenses) > 0)
                                Después de {{ count($pendingExpenses) }} gasto{{ count($pendingExpenses) !== 1 ? 's' : '' }} pendiente{{ count($pendingExpenses) !== 1 ? 's' : '' }}
                            @else
                                ✅ No hay gastos recurrentes pendientes
                            @endif
                        </p>
                    </div>
                    <div class="text-4xl">
                        @if($projectedBalance >= 0)
                            📈
                        @else
                            ⚠️
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Gastos Pendientes --}}
        @if(count($pendingExpenses) > 0)
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between">
                        <span>⏳ Gastos Recurrentes Pendientes</span>
                        <span class="text-lg font-bold text-red-600">
                            Total: ${{ number_format($totalPendingExpenses, 2) }}
                        </span>
                    </div>
                </x-slot>

                <x-slot name="description">
                    Estos gastos aún no se han registrado este mes
                </x-slot>

                <div class="space-y-3">
                    @foreach($pendingExpenses as $expense)
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-900 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">💸</span>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                            {{ $expense['name'] }}
                                            @if($expense['isEstimated'])
                                                <span class="text-xs text-orange-600 dark:text-orange-400">*</span>
                                            @endif
                                        </p>
                                        <div class="flex items-center gap-3 mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                                {{ $expense['category'] }}
                                            </span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                                📍 {{ $expense['account'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right ml-4">
                                <p class="text-lg font-bold text-red-600 dark:text-red-400">
                                    ${{ number_format($expense['amount'], 2) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Notas adicionales --}}
                @if($hasEstimated || !empty($unknownTemplates))
                    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                        <div class="flex items-start gap-2 text-xs text-yellow-800 dark:text-yellow-300">
                            <div class="flex-shrink-0 mt-0.5">ℹ️</div>
                            <div class="space-y-1">
                                @if($hasEstimated)
                                    <p><strong>* Monto estimado</strong> basado en el gasto del mes anterior</p>
                                @endif
                                @if(!empty($unknownTemplates))
                                    <p class="text-orange-700 dark:text-orange-400">
                                        <strong>⚠️ Sin estimación disponible:</strong> {{ implode(', ', $unknownTemplates) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </x-filament::section>
        @else
            {{-- Sin gastos pendientes --}}
            <x-filament::section>
                <div class="text-center py-8">
                    <div class="text-6xl mb-4">✅</div>
                    <p class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                        ¡Todo al día!
                    </p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        No hay gastos recurrentes pendientes este mes
                    </p>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-widgets::widget>
