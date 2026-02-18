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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            {{-- Balance Actual --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Balance Actual</p>
                        <p class="text-2xl font-semibold text-zinc-900 dark:text-white mt-2">
                            ${{ number_format($currentBalance, 2) }}
                        </p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">
                            {{ $accountsCount }} cuenta{{ $accountsCount !== 1 ? 's' : '' }} incluida{{ $accountsCount !== 1 ? 's' : '' }}
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 flex items-center justify-center">
                        <x-heroicon-o-wallet class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                </div>
            </div>

            {{-- Balance Proyectado --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Balance Proyectado</p>
                        <p class="text-2xl font-semibold {{ $projectedBalance >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} mt-2">
                            ${{ number_format($projectedBalance, 2) }}
                        </p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">
                            @if(count($pendingExpenses) > 0)
                                Despues de {{ count($pendingExpenses) }} gasto{{ count($pendingExpenses) !== 1 ? 's' : '' }} pendiente{{ count($pendingExpenses) !== 1 ? 's' : '' }}
                            @else
                                No hay gastos recurrentes pendientes
                            @endif
                        </p>
                    </div>
                    <div class="w-10 h-10 rounded-lg {{ $projectedBalance >= 0 ? 'bg-sky-50 dark:bg-sky-950/30' : 'bg-amber-50 dark:bg-amber-950/30' }} flex items-center justify-center">
                        @if($projectedBalance >= 0)
                            <x-heroicon-o-chart-bar class="w-5 h-5 text-sky-600 dark:text-sky-400" />
                        @else
                            <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-600 dark:text-amber-400" />
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
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-clock class="w-5 h-5 text-primary-500" />
                            <span>Gastos Recurrentes Pendientes</span>
                        </div>
                        <span class="text-base font-semibold text-rose-600 dark:text-rose-400">
                            Total: ${{ number_format($totalPendingExpenses, 2) }}
                        </span>
                    </div>
                </x-slot>

                <x-slot name="description">
                    Estos gastos aun no se han registrado este mes
                </x-slot>

                <div class="space-y-2">
                    @foreach($pendingExpenses as $expense)
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                            <div class="flex-1">
                                <div class="flex items-center gap-2.5">
                                    <x-heroicon-o-banknotes class="w-5 h-5 text-rose-500 flex-shrink-0" />
                                    <div>
                                        <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                            {{ $expense['name'] }}
                                            @if($expense['isEstimated'])
                                                <span class="text-xs text-amber-600 dark:text-amber-400">*</span>
                                            @endif
                                        </p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300">
                                                {{ $expense['category'] }}
                                            </span>
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $expense['account'] }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right ml-4">
                                <p class="text-base font-semibold text-rose-600 dark:text-rose-400">
                                    ${{ number_format($expense['amount'], 2) }}
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Notas adicionales --}}
                @if($hasEstimated || !empty($unknownTemplates))
                    <div class="mt-4 p-3 bg-amber-50 dark:bg-amber-950/20 rounded-lg border border-amber-200 dark:border-amber-800/50">
                        <div class="flex items-start gap-2 text-xs text-amber-800 dark:text-amber-300">
                            <x-heroicon-m-information-circle class="w-4 h-4 flex-shrink-0 mt-0.5" />
                            <div class="space-y-1">
                                @if($hasEstimated)
                                    <p><strong>* Monto estimado</strong> basado en el gasto del mes anterior</p>
                                @endif
                                @if(!empty($unknownTemplates))
                                    <p class="text-amber-700 dark:text-amber-400">
                                        <strong>Sin estimacion disponible:</strong> {{ implode(', ', $unknownTemplates) }}
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
                    <x-heroicon-o-check-circle class="w-12 h-12 text-emerald-500 mx-auto mb-4" />
                    <p class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">
                        Todo al dia!
                    </p>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        No hay gastos recurrentes pendientes este mes
                    </p>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-widgets::widget>
