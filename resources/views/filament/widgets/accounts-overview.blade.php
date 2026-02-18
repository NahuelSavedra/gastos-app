<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-building-library class="w-5 h-5 text-primary-500" />
                Resumen de Cuentas
            </div>
        </x-slot>

        <x-slot name="description">
            Movimientos de {{ $this->getViewData()['monthLabel'] }}
        </x-slot>

        <div class="grid gap-5 grid-cols-1 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($this->getViewData()['accounts'] as $account)
                <div class="relative bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow duration-200">

                    {{-- Colored left accent --}}
                    <div class="absolute inset-y-0 left-0 w-1 rounded-l-lg" style="background-color: {{ $account['color'] ?? '#64748b' }};"></div>

                    {{-- Header --}}
                    <div class="p-5 pb-4 pl-6">
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 text-xs font-medium rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300">
                                {{ $account['type_label'] ?? 'Cuenta' }}
                            </span>

                            @if(!($account['include_in_totals'] ?? true))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-md bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400">
                                    <x-heroicon-m-eye-slash class="w-3 h-3" />
                                    Excluida
                                </span>
                            @endif
                        </div>

                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white truncate">
                            {{ $account['name'] }}
                        </h3>
                    </div>

                    {{-- Body --}}
                    <div class="px-5 pb-5 pl-6 space-y-4">
                        {{-- Balance Actual --}}
                        <div>
                            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400 mb-1">
                                Balance Actual
                            </p>
                            <p class="text-2xl font-semibold {{ $account['current_balance'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                ${{ number_format($account['current_balance'], 2) }}
                            </p>
                        </div>

                        {{-- Separator --}}
                        <div class="border-t border-zinc-100 dark:border-zinc-800"></div>

                        {{-- Balance del Mes --}}
                        <div class="bg-zinc-50 dark:bg-zinc-800/50 rounded-lg p-3 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-300">
                                    Balance del Periodo
                                </span>
                                <span class="text-base font-semibold {{ $account['month_balance'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ $account['month_balance'] >= 0 ? '+' : '' }}${{ number_format($account['month_balance'], 2) }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 pt-2 border-t border-zinc-200 dark:border-zinc-700">
                                <div class="flex flex-col">
                                    <span class="flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400 mb-0.5">
                                        <x-heroicon-m-arrow-trending-up class="w-3 h-3 text-emerald-500" />
                                        Ingresos
                                    </span>
                                    <span class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                        ${{ number_format($account['month_income'], 2) }}
                                    </span>
                                </div>
                                <div class="flex flex-col">
                                    <span class="flex items-center gap-1 text-xs text-zinc-500 dark:text-zinc-400 mb-0.5">
                                        <x-heroicon-m-arrow-trending-down class="w-3 h-3 text-rose-500" />
                                        Gastos
                                    </span>
                                    <span class="text-sm font-semibold text-rose-600 dark:text-rose-400">
                                        ${{ number_format($account['month_expense'], 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Stats --}}
                        <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400">
                            <div class="flex items-center gap-1.5">
                                <x-heroicon-m-receipt-refund class="w-3.5 h-3.5" />
                                <span>{{ $account['transaction_count'] }} transacciones</span>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <x-heroicon-m-banknotes class="w-3.5 h-3.5" />
                                <span>${{ number_format($account['initial_balance'], 0) }} inicial</span>
                            </div>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="px-5 pb-5 pl-6">
                        <a href="{{ route('filament.app.resources.accounts.view', ['record' => $account['id']]) }}"
                           class="block w-full text-center px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-zinc-100 dark:bg-zinc-800 text-zinc-700 dark:text-zinc-300 hover:bg-zinc-200 dark:hover:bg-zinc-700">
                            Ver Detalles
                        </a>
                    </div>
                </div>
            @endforeach

            {{-- Empty state --}}
            @if(count($this->getViewData()['accounts']) === 0)
                <div class="col-span-full">
                    <div class="text-center py-16 bg-zinc-50 dark:bg-zinc-900 rounded-lg border border-dashed border-zinc-300 dark:border-zinc-700">
                        <x-heroicon-o-building-library class="w-12 h-12 text-zinc-400 mx-auto mb-4" />
                        <h3 class="text-lg font-semibold text-zinc-900 dark:text-white mb-2">
                            No tienes cuentas creadas
                        </h3>
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
