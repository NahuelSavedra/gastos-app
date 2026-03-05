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
        $diff = $projectedBalance - $currentBalance;
    @endphp

    <div class="space-y-4">
        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Balance Actual --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-xl shadow-sm text-white p-5">
                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10"></div>
                <div class="absolute -right-2 -bottom-8 w-16 h-16 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-medium text-emerald-100">Balance Actual</p>
                        <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
                            <x-heroicon-o-wallet class="w-5 h-5 text-white" />
                        </div>
                    </div>
                    <p class="text-3xl font-bold">${{ number_format($currentBalance, 2) }}</p>
                    <p class="text-xs text-emerald-100 mt-2">
                        {{ $accountsCount }} cuenta{{ $accountsCount !== 1 ? 's' : '' }} incluida{{ $accountsCount !== 1 ? 's' : '' }}
                    </p>
                </div>
            </div>

            {{-- Balance Proyectado --}}
            <div class="relative overflow-hidden rounded-xl shadow-sm text-white p-5
                {{ $projectedBalance >= 0 ? 'bg-gradient-to-br from-sky-500 to-sky-700' : 'bg-gradient-to-br from-amber-500 to-amber-700' }}">
                <div class="absolute -right-6 -top-6 w-24 h-24 rounded-full bg-white/10"></div>
                <div class="absolute -right-2 -bottom-8 w-16 h-16 rounded-full bg-white/10"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-sm font-medium {{ $projectedBalance >= 0 ? 'text-sky-100' : 'text-amber-100' }}">Balance Proyectado</p>
                        <div class="w-9 h-9 rounded-lg bg-white/20 flex items-center justify-center">
                            @if($projectedBalance >= 0)
                                <x-heroicon-o-chart-bar class="w-5 h-5 text-white" />
                            @else
                                <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-white" />
                            @endif
                        </div>
                    </div>
                    <p class="text-3xl font-bold">${{ number_format($projectedBalance, 2) }}</p>
                    <div class="flex items-center gap-2 mt-2">
                        @if($diff < 0)
                            <span class="text-xs font-medium px-2 py-0.5 rounded-full bg-white/20">
                                -${{ number_format(abs($diff), 2) }} en gastos pendientes
                            </span>
                        @else
                            <p class="text-xs {{ $projectedBalance >= 0 ? 'text-sky-100' : 'text-amber-100' }}">
                                Sin gastos recurrentes pendientes
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Gastos Pendientes --}}
        @if(count($pendingExpenses) > 0)
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center justify-between w-full">
                        <div class="flex items-center gap-2">
                            <x-heroicon-o-clock class="w-5 h-5 text-amber-500" />
                            <span>Gastos Recurrentes Pendientes</span>
                        </div>
                        <span class="text-sm font-bold text-rose-600 dark:text-rose-400">
                            -${{ number_format($totalPendingExpenses, 2) }}
                        </span>
                    </div>
                </x-slot>

                <x-slot name="description">
                    Estos gastos aún no se han registrado este mes
                </x-slot>

                <div class="space-y-2">
                    @foreach($pendingExpenses as $expense)
                        <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-100 dark:border-zinc-700/50 hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                            <div class="flex items-center gap-3 flex-1">
                                <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/30 flex items-center justify-center flex-shrink-0">
                                    <x-heroicon-m-banknotes class="w-4 h-4 text-rose-500" />
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $expense['name'] }}
                                        @if($expense['isEstimated'])
                                            <span class="text-xs text-amber-500 ml-1">*estimado</span>
                                        @endif
                                    </p>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-xs px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-700 text-zinc-600 dark:text-zinc-400">
                                            {{ $expense['category'] }}
                                        </span>
                                        <span class="text-xs text-zinc-400 dark:text-zinc-500">{{ $expense['account'] }}</span>
                                    </div>
                                </div>
                            </div>
                            <p class="text-base font-bold text-rose-600 dark:text-rose-400 ml-4 flex-shrink-0">
                                -${{ number_format($expense['amount'], 2) }}
                            </p>
                        </div>
                    @endforeach
                </div>

                @if($hasEstimated || !empty($unknownTemplates))
                    <div class="mt-3 p-3 bg-amber-50 dark:bg-amber-950/20 rounded-lg border border-amber-200 dark:border-amber-800/50">
                        <div class="flex items-start gap-2 text-xs text-amber-800 dark:text-amber-300">
                            <x-heroicon-m-information-circle class="w-4 h-4 flex-shrink-0 mt-0.5" />
                            <div class="space-y-1">
                                @if($hasEstimated)
                                    <p><strong>* Monto estimado</strong> basado en el gasto del mes anterior</p>
                                @endif
                                @if(!empty($unknownTemplates))
                                    <p><strong>Sin estimación:</strong> {{ implode(', ', $unknownTemplates) }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </x-filament::section>
        @else
            <x-filament::section>
                <div class="text-center py-8">
                    <div class="w-14 h-14 rounded-full bg-emerald-50 dark:bg-emerald-950/30 flex items-center justify-center mx-auto mb-3">
                        <x-heroicon-o-check-circle class="w-8 h-8 text-emerald-500" />
                    </div>
                    <p class="text-base font-semibold text-zinc-900 dark:text-white mb-1">¡Todo al día!</p>
                    <p class="text-sm text-zinc-500 dark:text-zinc-400">
                        No hay gastos recurrentes pendientes este mes
                    </p>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-widgets::widget>
