<x-filament-widgets::widget>
    @php
        $viewData = $this->getViewData();
        $accounts = $viewData['accounts'];
        $monthLabel = $viewData['monthLabel'];
    @endphp

    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-building-library class="w-5 h-5 text-primary-500" />
                Resumen de Cuentas
            </div>
        </x-slot>

        <x-slot name="description">
            Movimientos de {{ $monthLabel }}
        </x-slot>

        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($accounts as $account)
                @php
                    $color = $account['color'] ?? '#64748b';
                    $balance = $account['current_balance'];
                    $monthBalance = $account['month_balance'];
                @endphp

                <div class="relative bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden hover:shadow-md hover:-translate-y-0.5 transition-all duration-200">

                    {{-- Colored top bar --}}
                    <div class="h-1.5 w-full" style="background: linear-gradient(90deg, {{ $color }}, {{ $color }}99);"></div>

                    {{-- Header --}}
                    <div class="p-5 pb-3">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background-color: {{ $color }}20; border: 1px solid {{ $color }}40;">
                                    <span class="text-sm font-bold" style="color: {{ $color }};">
                                        {{ mb_strtoupper(mb_substr($account['name'], 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <h3 class="text-sm font-semibold text-zinc-900 dark:text-white leading-tight truncate max-w-[140px]">
                                        {{ $account['name'] }}
                                    </h3>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $account['type_label'] ?? 'Cuenta' }}
                                    </span>
                                </div>
                            </div>

                            @if(!($account['include_in_totals'] ?? true))
                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 text-xs font-medium rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-400">
                                    <x-heroicon-m-eye-slash class="w-3 h-3" />
                                </span>
                            @endif
                        </div>

                        {{-- Balance Actual --}}
                        <div class="mt-3">
                            <p class="text-xs font-medium text-zinc-400 dark:text-zinc-500 mb-0.5">Balance Actual</p>
                            <p class="text-2xl font-bold {{ $balance >= 0 ? 'text-zinc-900 dark:text-white' : 'text-rose-600 dark:text-rose-400' }}">
                                ${{ number_format($balance, 2) }}
                            </p>
                        </div>
                    </div>

                    {{-- Stats strip --}}
                    <div class="mx-5 mb-4 bg-zinc-50 dark:bg-zinc-800/60 rounded-lg p-3">
                        <div class="flex items-center justify-between mb-2.5">
                            <span class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Periodo</span>
                            <span class="text-sm font-bold {{ $monthBalance >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                {{ $monthBalance >= 0 ? '+' : '' }}${{ number_format($monthBalance, 2) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                            <div>
                                <p class="flex items-center gap-1 text-xs text-zinc-400 dark:text-zinc-500 mb-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                                    Ingresos
                                </p>
                                <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                    ${{ number_format($account['month_income'], 2) }}
                                </p>
                            </div>
                            <div>
                                <p class="flex items-center gap-1 text-xs text-zinc-400 dark:text-zinc-500 mb-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 inline-block"></span>
                                    Gastos
                                </p>
                                <p class="text-sm font-semibold text-rose-600 dark:text-rose-400">
                                    ${{ number_format($account['month_expense'], 2) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-5 pb-4 flex items-center justify-between">
                        <span class="text-xs text-zinc-400 dark:text-zinc-500 flex items-center gap-1">
                            <x-heroicon-m-receipt-refund class="w-3 h-3" />
                            {{ $account['transaction_count'] }} transacciones
                        </span>
                        <a href="{{ route('filament.app.resources.accounts.view', ['record' => $account['id']]) }}"
                           class="text-xs font-medium px-3 py-1.5 rounded-lg transition-colors hover:text-white"
                           style="background-color: {{ $color }}20; color: {{ $color }}; border: 1px solid {{ $color }}40;"
                           onmouseover="this.style.backgroundColor='{{ $color }}'; this.style.color='white';"
                           onmouseout="this.style.backgroundColor='{{ $color }}20'; this.style.color='{{ $color }}';">
                            Ver Detalles →
                        </a>
                    </div>
                </div>
            @endforeach

            {{-- Empty state --}}
            @if(count($accounts) === 0)
                <div class="col-span-full">
                    <div class="text-center py-16 bg-zinc-50 dark:bg-zinc-900 rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700">
                        <x-heroicon-o-building-library class="w-12 h-12 text-zinc-400 mx-auto mb-4" />
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">No tienes cuentas creadas</h3>
                        <p class="text-sm text-zinc-500 dark:text-zinc-400 mb-6">
                            Crea tu primera cuenta para comenzar a gestionar tus finanzas
                        </p>
                        <a href="{{ route('filament.app.resources.accounts.create') }}"
                           class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-sm transition-colors text-sm">
                            <x-heroicon-m-plus class="w-4 h-4" />
                            Crear Primera Cuenta
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
