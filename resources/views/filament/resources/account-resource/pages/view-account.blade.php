<x-filament-panels::page>
    @php
        $viewData = $this->getViewData();
        $account = $viewData['account'];
        $currentBalance = $viewData['currentBalance'];
        $monthBalance = $viewData['monthBalance'];
        $monthStats = $viewData['monthStats'];
        $last7Days = $viewData['last7Days'];
        $categoryBreakdown = $viewData['categoryBreakdown'];
        $recentTransactions = $viewData['recentTransactions'];
        $monthlyTrend = $viewData['monthlyTrend'];
        $topCategories = $viewData['topCategories'];
        $transfersSummary = $viewData['transfersSummary'];
    @endphp

    {{-- Header con Metricas Principales --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Balance Actual --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Balance Actual</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white mt-1">
                        ${{ number_format($currentBalance, 2) }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-lg {{ $currentBalance >= 0 ? 'bg-emerald-50 dark:bg-emerald-950/30' : 'bg-rose-50 dark:bg-rose-950/30' }} flex items-center justify-center">
                    @if($currentBalance >= 0)
                        <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                    @else
                        <x-heroicon-o-arrow-trending-down class="w-5 h-5 text-rose-600 dark:text-rose-400" />
                    @endif
                </div>
            </div>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">
                Saldo inicial: ${{ number_format($account->initial_balance, 2) }}
            </p>
        </div>

        {{-- Balance del Mes --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Balance del Mes</p>
                    <p class="text-2xl font-semibold {{ $monthBalance >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} mt-1">
                        ${{ number_format($monthBalance, 2) }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-lg {{ $monthBalance >= 0 ? 'bg-emerald-50 dark:bg-emerald-950/30' : 'bg-amber-50 dark:bg-amber-950/30' }} flex items-center justify-center">
                    @if($monthBalance >= 0)
                        <x-heroicon-o-check-circle class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                    @else
                        <x-heroicon-o-exclamation-triangle class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                    @endif
                </div>
            </div>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">
                {{ now()->format('F Y') }}
            </p>
        </div>

        {{-- Ingresos del Mes --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Ingresos</p>
                    <p class="text-2xl font-semibold text-emerald-600 dark:text-emerald-400 mt-1">
                        ${{ number_format($monthStats['income'], 2) }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 flex items-center justify-center">
                    <x-heroicon-o-banknotes class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                </div>
            </div>
            <p class="text-xs {{ $monthStats['income_change'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} mt-2">
                {{ $monthStats['income_change'] >= 0 ? '+' : '' }}{{ number_format($monthStats['income_change'], 1) }}% vs mes anterior
            </p>
        </div>

        {{-- Gastos del Mes --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Gastos</p>
                    <p class="text-2xl font-semibold text-rose-600 dark:text-rose-400 mt-1">
                        ${{ number_format($monthStats['expense'], 2) }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-rose-50 dark:bg-rose-950/30 flex items-center justify-center">
                    <x-heroicon-o-credit-card class="w-5 h-5 text-rose-600 dark:text-rose-400" />
                </div>
            </div>
            <p class="text-xs {{ $monthStats['expense_change'] <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} mt-2">
                {{ $monthStats['expense_change'] >= 0 ? '+' : '' }}{{ number_format($monthStats['expense_change'], 1) }}% vs mes anterior
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna Principal (2/3) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Evolucion Ultimos 7 Dias --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm">
                <div class="p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <h3 class="flex items-center gap-2 text-base font-semibold text-zinc-900 dark:text-white">
                        <x-heroicon-o-chart-bar class="w-5 h-5 text-primary-500" />
                        Evolucion Ultimos 7 Dias
                    </h3>
                </div>
                <div class="p-5">
                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                            <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Fecha</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Ingresos</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Gastos</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Balance</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($last7Days as $day)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $day['date'] }}</span>
                                        <span class="text-xs text-zinc-500 dark:text-zinc-400 ml-1">({{ $day['day'] }})</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-emerald-600 dark:text-emerald-400 font-medium">
                                        ${{ number_format($day['income'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-rose-600 dark:text-rose-400 font-medium">
                                        ${{ number_format($day['expense'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-semibold {{ $day['balance'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $day['balance'] >= 0 ? '+' : '' }}${{ number_format($day['balance'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Tendencia Mensual (Ultimos 6 Meses) --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm">
                <div class="p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <h3 class="flex items-center gap-2 text-base font-semibold text-zinc-900 dark:text-white">
                        <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-primary-500" />
                        Tendencia Mensual (Ultimos 6 Meses)
                    </h3>
                </div>
                <div class="p-5">
                    <div class="rounded-lg border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                            <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Mes</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Ingresos</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Gastos</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase">Balance</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($monthlyTrend as $month)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $month['month'] }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-emerald-600 dark:text-emerald-400 font-medium">
                                        ${{ number_format($month['income'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-rose-600 dark:text-rose-400 font-medium">
                                        ${{ number_format($month['expense'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-semibold {{ $month['balance'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $month['balance'] >= 0 ? '+' : '' }}${{ number_format($month['balance'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Transacciones Recientes --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm">
                <div class="p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-base font-semibold text-zinc-900 dark:text-white">
                            <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-primary-500" />
                            Transacciones Recientes
                        </h3>
                        <a href="{{ route('filament.app.resources.transactions.index', ['tableFilters[account_id][value]' => $account->id]) }}"
                           class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 font-medium">
                            Ver todas
                        </a>
                    </div>
                </div>
                <div class="p-5">
                    <div class="space-y-2">
                        @forelse($recentTransactions as $transaction)
                            <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg hover:bg-zinc-100 dark:hover:bg-zinc-800 transition-colors">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2.5">
                                        @if($transaction->category->type === 'income')
                                            <x-heroicon-m-arrow-trending-up class="w-4 h-4 text-emerald-500 flex-shrink-0" />
                                        @else
                                            <x-heroicon-m-arrow-trending-down class="w-4 h-4 text-rose-500 flex-shrink-0" />
                                        @endif
                                        <div>
                                            <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $transaction->title }}
                                            </p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $transaction->category->name }} · {{ $transaction->date->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold {{ $transaction->category->type === 'income' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                        {{ $transaction->category->type === 'income' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-zinc-500 dark:text-zinc-400 py-8">
                                No hay transacciones registradas
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Columna Lateral (1/3) --}}
        <div class="space-y-6">

            {{-- Informacion de la Cuenta --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm">
                <div class="p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <h3 class="flex items-center gap-2 text-base font-semibold text-zinc-900 dark:text-white">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-primary-500" />
                        Informacion de la Cuenta
                    </h3>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Nombre</p>
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $account->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Saldo Inicial</p>
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">${{ number_format($account->initial_balance, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Balance Actual</p>
                        <p class="text-sm font-semibold {{ $currentBalance >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            ${{ number_format($currentBalance, 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Transacciones este mes</p>
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $monthStats['transaction_count'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Fecha de creacion</p>
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $account->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>

            {{-- Resumen de Transferencias --}}
            @if($transfersSummary['count_incoming'] > 0 || $transfersSummary['count_outgoing'] > 0)
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm">
                    <div class="p-5 border-b border-zinc-100 dark:border-zinc-800">
                        <h3 class="flex items-center gap-2 text-base font-semibold text-zinc-900 dark:text-white">
                            <x-heroicon-o-arrows-right-left class="w-5 h-5 text-primary-500" />
                            Transferencias del Mes
                        </h3>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <x-heroicon-m-arrow-down-tray class="w-4 h-4 text-emerald-500" />
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">Recibidas</span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-emerald-600 dark:text-emerald-400">
                                    ${{ number_format($transfersSummary['incoming'], 2) }}
                                </p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $transfersSummary['count_incoming'] }} transferencia{{ $transfersSummary['count_incoming'] !== 1 ? 's' : '' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <x-heroicon-m-arrow-up-tray class="w-4 h-4 text-rose-500" />
                                <span class="text-sm text-zinc-600 dark:text-zinc-400">Enviadas</span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-rose-600 dark:text-rose-400">
                                    ${{ number_format($transfersSummary['outgoing'], 2) }}
                                </p>
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    {{ $transfersSummary['count_outgoing'] }} transferencia{{ $transfersSummary['count_outgoing'] !== 1 ? 's' : '' }}
                                </p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Balance Neto</span>
                                <span class="text-sm font-semibold {{ $transfersSummary['net'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                    {{ $transfersSummary['net'] >= 0 ? '+' : '' }}${{ number_format($transfersSummary['net'], 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Top Categorias --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm">
                <div class="p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <h3 class="flex items-center gap-2 text-base font-semibold text-zinc-900 dark:text-white">
                        <x-heroicon-o-trophy class="w-5 h-5 text-primary-500" />
                        Top Categorias de Gasto
                    </h3>
                </div>
                <div class="p-5">
                    @if(count($topCategories) > 0)
                        <div class="space-y-3">
                            @foreach($topCategories as $index => $category)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3 flex-1">
                                        <span class="text-sm font-bold text-zinc-400 w-5 text-center">{{ $index + 1 }}</span>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-zinc-900 dark:text-white">
                                                {{ $category['name'] }}
                                            </p>
                                            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                {{ $category['count'] }} transaccion{{ $category['count'] !== 1 ? 'es' : '' }}
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-semibold text-rose-600 dark:text-rose-400">
                                        ${{ number_format($category['amount'], 2) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-zinc-500 dark:text-zinc-400 py-4">
                            No hay gastos este mes
                        </p>
                    @endif
                </div>
            </div>

            {{-- Desglose por Categorias --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm">
                <div class="p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <h3 class="flex items-center gap-2 text-base font-semibold text-zinc-900 dark:text-white">
                        <x-heroicon-o-chart-pie class="w-5 h-5 text-primary-500" />
                        Desglose por Categorias
                    </h3>
                </div>
                <div class="p-5">
                    {{-- Gastos --}}
                    @if($categoryBreakdown['expenses']->count() > 0)
                        <div class="mb-6">
                            <h4 class="flex items-center gap-1.5 text-sm font-semibold text-rose-600 dark:text-rose-400 mb-3">
                                <x-heroicon-m-arrow-trending-down class="w-4 h-4" />
                                Gastos
                            </h4>
                            <div class="space-y-2">
                                @foreach($categoryBreakdown['expenses'] as $expense)
                                    <div>
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="text-zinc-600 dark:text-zinc-400">{{ $expense['name'] }}</span>
                                            <span class="font-medium text-zinc-900 dark:text-white">
                                                ${{ number_format($expense['amount'], 2) }}
                                            </span>
                                        </div>
                                        <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-1.5">
                                            <div class="bg-rose-500 h-1.5 rounded-full transition-all"
                                                 style="width: {{ $expense['percentage'] }}%">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Ingresos --}}
                    @if($categoryBreakdown['income']->count() > 0)
                        <div>
                            <h4 class="flex items-center gap-1.5 text-sm font-semibold text-emerald-600 dark:text-emerald-400 mb-3">
                                <x-heroicon-m-arrow-trending-up class="w-4 h-4" />
                                Ingresos
                            </h4>
                            <div class="space-y-2">
                                @foreach($categoryBreakdown['income'] as $income)
                                    <div>
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="text-zinc-600 dark:text-zinc-400">{{ $income['name'] }}</span>
                                            <span class="font-medium text-zinc-900 dark:text-white">
                                                ${{ number_format($income['amount'], 2) }}
                                            </span>
                                        </div>
                                        <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-1.5">
                                            <div class="bg-emerald-500 h-1.5 rounded-full transition-all"
                                                 style="width: {{ $income['percentage'] }}%">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($categoryBreakdown['expenses']->count() === 0 && $categoryBreakdown['income']->count() === 0)
                        <p class="text-center text-zinc-500 dark:text-zinc-400 py-4">
                            No hay transacciones este mes
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
