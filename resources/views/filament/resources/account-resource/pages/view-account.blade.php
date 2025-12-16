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

    {{-- Header con Métricas Principales --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Balance Actual --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">💰 Balance Actual</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                        ${{ number_format($currentBalance, 2) }}
                    </p>
                </div>
                <div class="text-3xl">
                    @if($currentBalance >= 0)
                        <span class="text-green-500">📈</span>
                    @else
                        <span class="text-red-500">📉</span>
                    @endif
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                Saldo inicial: ${{ number_format($account->initial_balance, 2) }}
            </p>
        </div>

        {{-- Balance del Mes --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">💳 Balance del Mes</p>
                    <p class="text-2xl font-bold {{ $monthBalance >= 0 ? 'text-green-600' : 'text-red-600' }} mt-1">
                        ${{ number_format($monthBalance, 2) }}
                    </p>
                </div>
                <div class="text-3xl">
                    @if($monthBalance >= 0)
                        ✅
                    @else
                        ⚠️
                    @endif
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                {{ now()->format('F Y') }}
            </p>
        </div>

        {{-- Ingresos del Mes --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">📈 Ingresos</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">
                        ${{ number_format($monthStats['income'], 2) }}
                    </p>
                </div>
                <div class="text-3xl">💰</div>
            </div>
            <p class="text-xs {{ $monthStats['income_change'] >= 0 ? 'text-green-600' : 'text-red-600' }} mt-2">
                {{ $monthStats['income_change'] >= 0 ? '↗️' : '↘️' }}
                {{ number_format(abs($monthStats['income_change']), 1) }}% vs mes anterior
            </p>
        </div>

        {{-- Gastos del Mes --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">📉 Gastos</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">
                        ${{ number_format($monthStats['expense'], 2) }}
                    </p>
                </div>
                <div class="text-3xl">💸</div>
            </div>
            <p class="text-xs {{ $monthStats['expense_change'] <= 0 ? 'text-green-600' : 'text-red-600' }} mt-2">
                {{ $monthStats['expense_change'] >= 0 ? '⚠️' : '✅' }}
                {{ number_format(abs($monthStats['expense_change']), 1) }}% vs mes anterior
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna Principal (2/3) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Evolución Últimos 7 Días --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        📊 Evolución Últimos 7 Días
                    </h3>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fecha</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ingresos</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Gastos</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Balance</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($last7Days as $day)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="text-sm font-medium text-gray-900 dark:text-white">{{ $day['date'] }}</span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400 ml-1">({{ $day['day'] }})</span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-green-600 font-medium">
                                        ${{ number_format($day['income'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-red-600 font-medium">
                                        ${{ number_format($day['expense'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-semibold {{ $day['balance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $day['balance'] >= 0 ? '+' : '' }}${{ number_format($day['balance'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Tendencia Mensual (Últimos 6 Meses) --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        📈 Tendencia Mensual (Últimos 6 Meses)
                    </h3>
                </div>
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Mes</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Ingresos</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Gastos</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Balance</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach($monthlyTrend as $month)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $month['month'] }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-green-600 font-medium">
                                        ${{ number_format($month['income'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-red-600 font-medium">
                                        ${{ number_format($month['expense'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-semibold {{ $month['balance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            📋 Transacciones Recientes
                        </h3>
                        <a href="{{ route('filament.app.resources.transactions.index', ['tableFilters[account_id][value]' => $account->id]) }}"
                           class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                            Ver todas →
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        @forelse($recentTransactions as $transaction)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">
                                            @if($transaction->category->type === 'income')
                                                📈
                                            @else
                                                📉
                                            @endif
                                        </span>
                                        <div>
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $transaction->title }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $transaction->category->name }} • {{ $transaction->date->format('d/m/Y') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm font-semibold {{ $transaction->category->type === 'income' ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->category->type === 'income' ? '+' : '-' }}${{ number_format($transaction->amount, 2) }}
                                    </p>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-gray-500 dark:text-gray-400 py-8">
                                No hay transacciones registradas
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Columna Lateral (1/3) --}}
        <div class="space-y-6">

            {{-- Información de la Cuenta --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        ℹ️ Información de la Cuenta
                    </h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Nombre</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $account->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Saldo Inicial</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">${{ number_format($account->initial_balance, 2) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Balance Actual</p>
                        <p class="text-sm font-bold {{ $currentBalance >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            ${{ number_format($currentBalance, 2) }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Transacciones este mes</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $monthStats['transaction_count'] }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Fecha de creación</p>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $account->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
            </div>

            {{-- Resumen de Transferencias --}}
            @if($transfersSummary['count_incoming'] > 0 || $transfersSummary['count_outgoing'] > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            🔄 Transferencias del Mes
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-green-600">📥</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Recibidas</span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-green-600">
                                    ${{ number_format($transfersSummary['incoming'], 2) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $transfersSummary['count_incoming'] }} transferencia{{ $transfersSummary['count_incoming'] !== 1 ? 's' : '' }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-red-600">📤</span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Enviadas</span>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-semibold text-red-600">
                                    ${{ number_format($transfersSummary['outgoing'], 2) }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ $transfersSummary['count_outgoing'] }} transferencia{{ $transfersSummary['count_outgoing'] !== 1 ? 's' : '' }}
                                </p>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Balance Neto</span>
                                <span class="text-sm font-bold {{ $transfersSummary['net'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $transfersSummary['net'] >= 0 ? '+' : '' }}${{ number_format($transfersSummary['net'], 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Top Categorías --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        🏆 Top Categorías de Gasto
                    </h3>
                </div>
                <div class="p-6">
                    @if(count($topCategories) > 0)
                        <div class="space-y-3">
                            @foreach($topCategories as $index => $category)
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3 flex-1">
                                        <span class="text-lg font-bold text-gray-400">{{ $index + 1 }}</span>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ $category['name'] }}
                                            </p>
                                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                                {{ $category['count'] }} transacción{{ $category['count'] !== 1 ? 'es' : '' }}
                                            </p>
                                        </div>
                                    </div>
                                    <p class="text-sm font-semibold text-red-600">
                                        ${{ number_format($category['amount'], 2) }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-gray-500 dark:text-gray-400 py-4">
                            No hay gastos este mes
                        </p>
                    @endif
                </div>
            </div>

            {{-- Desglose por Categorías --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        📊 Desglose por Categorías
                    </h3>
                </div>
                <div class="p-6">
                    {{-- Gastos --}}
                    @if($categoryBreakdown['expenses']->count() > 0)
                        <div class="mb-6">
                            <h4 class="text-sm font-semibold text-red-600 mb-3">📉 Gastos</h4>
                            <div class="space-y-2">
                                @foreach($categoryBreakdown['expenses'] as $expense)
                                    <div>
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="text-gray-600 dark:text-gray-400">{{ $expense['name'] }}</span>
                                            <span class="font-medium text-gray-900 dark:text-white">
                                                ${{ number_format($expense['amount'], 2) }}
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-red-500 h-2 rounded-full transition-all"
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
                            <h4 class="text-sm font-semibold text-green-600 mb-3">📈 Ingresos</h4>
                            <div class="space-y-2">
                                @foreach($categoryBreakdown['income'] as $income)
                                    <div>
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="text-gray-600 dark:text-gray-400">{{ $income['name'] }}</span>
                                            <span class="font-medium text-gray-900 dark:text-white">
                                                ${{ number_format($income['amount'], 2) }}
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                            <div class="bg-green-500 h-2 rounded-full transition-all"
                                                 style="width: {{ $income['percentage'] }}%">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($categoryBreakdown['expenses']->count() === 0 && $categoryBreakdown['income']->count() === 0)
                        <p class="text-center text-gray-500 dark:text-gray-400 py-4">
                            No hay transacciones este mes
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
