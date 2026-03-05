<x-filament-panels::page>
    @php
        $viewData = $this->getViewData();
        $account = $viewData['account'];
        $creditCard = $viewData['creditCard'];
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
        <div class="relative overflow-hidden rounded-xl shadow-sm text-white p-5
            {{ $currentBalance >= 0 ? 'bg-gradient-to-br from-emerald-500 to-emerald-700' : 'bg-gradient-to-br from-rose-500 to-rose-700' }}">
            <div class="absolute -right-5 -top-5 w-20 h-20 rounded-full bg-white/10"></div>
            <p class="text-xs font-medium {{ $currentBalance >= 0 ? 'text-emerald-100' : 'text-rose-100' }} relative">Balance Actual</p>
            <p class="text-2xl font-bold mt-1 relative">${{ number_format($currentBalance, 2) }}</p>
            <p class="text-xs {{ $currentBalance >= 0 ? 'text-emerald-100' : 'text-rose-100' }} mt-2 relative">
                Inicial: ${{ number_format($account->initial_balance, 0) }}
            </p>
        </div>

        {{-- Balance del Mes --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Balance del Mes</p>
                <div class="w-8 h-8 rounded-lg {{ $monthBalance >= 0 ? 'bg-sky-50 dark:bg-sky-950/30' : 'bg-amber-50 dark:bg-amber-950/30' }} flex items-center justify-center">
                    @if($monthBalance >= 0)
                        <x-heroicon-m-check-circle class="w-4 h-4 text-sky-600 dark:text-sky-400" />
                    @else
                        <x-heroicon-m-exclamation-triangle class="w-4 h-4 text-amber-600 dark:text-amber-400" />
                    @endif
                </div>
            </div>
            <p class="text-2xl font-bold {{ $monthBalance >= 0 ? 'text-sky-600 dark:text-sky-400' : 'text-amber-600 dark:text-amber-400' }}">
                {{ $monthBalance >= 0 ? '+' : '' }}${{ number_format($monthBalance, 2) }}
            </p>
            <p class="text-xs text-zinc-400 mt-2">{{ now()->format('F Y') }}</p>
        </div>

        {{-- Ingresos del Mes --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Ingresos</p>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/30 flex items-center justify-center">
                    <x-heroicon-m-arrow-trending-up class="w-4 h-4 text-emerald-600 dark:text-emerald-400" />
                </div>
            </div>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">
                ${{ number_format($monthStats['income'], 2) }}
            </p>
            <p class="text-xs {{ $monthStats['income_change'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }} mt-2 font-medium">
                {{ $monthStats['income_change'] >= 0 ? '+' : '' }}{{ number_format($monthStats['income_change'], 1) }}% vs anterior
            </p>
        </div>

        {{-- Gastos del Mes --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm p-5">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Gastos</p>
                <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-950/30 flex items-center justify-center">
                    <x-heroicon-m-arrow-trending-down class="w-4 h-4 text-rose-600 dark:text-rose-400" />
                </div>
            </div>
            <p class="text-2xl font-bold text-rose-600 dark:text-rose-400">
                ${{ number_format($monthStats['expense'], 2) }}
            </p>
            <p class="text-xs {{ $monthStats['expense_change'] <= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} mt-2 font-medium">
                {{ $monthStats['expense_change'] >= 0 ? '+' : '' }}{{ number_format($monthStats['expense_change'], 1) }}% vs anterior
            </p>
        </div>
    </div>

    {{-- Credit Card Summary (only for credit_card accounts) --}}
    @if($creditCard)
        @php
            $ccDebt = $creditCard->total_debt;
            $ccMonthly = $creditCard->monthly_payment;
            $ccAvailable = $creditCard->available_credit;
            $ccUsedPct = $creditCard->credit_limit > 0 ? ($ccDebt / $creditCard->credit_limit) * 100 : 0;
            $ccBarColor = $ccUsedPct > 80 ? 'from-rose-500 to-rose-600' : ($ccUsedPct > 50 ? 'from-amber-400 to-amber-500' : 'from-emerald-400 to-emerald-500');
            $activeCount = $creditCard->installmentPurchases->count();
        @endphp

        <div class="mb-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-100 dark:border-zinc-800 bg-gradient-to-r from-zinc-50 to-white dark:from-zinc-800/50 dark:to-zinc-900">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-zinc-700 to-zinc-900 flex items-center justify-center">
                        <x-heroicon-o-credit-card class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $creditCard->name }}</h3>
                        @if($creditCard->last_four)
                            <p class="text-xs text-zinc-400 tracking-widest">···· {{ $creditCard->last_four }}</p>
                        @endif
                    </div>
                </div>
                <a href="{{ route('filament.app.resources.credit-cards.view', $creditCard) }}"
                   class="text-xs font-medium text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 flex items-center gap-1">
                    Ver detalle →
                </a>
            </div>

            {{-- Metrics --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-y sm:divide-y-0 divide-zinc-100 dark:divide-zinc-800">
                <div class="p-4">
                    <p class="text-xs text-zinc-400 mb-1">Deuda total</p>
                    <p class="text-lg font-bold text-rose-600 dark:text-rose-400">${{ number_format($ccDebt, 0) }}</p>
                    <p class="text-xs text-zinc-400 mt-0.5">{{ $activeCount }} compra{{ $activeCount !== 1 ? 's' : '' }} activa{{ $activeCount !== 1 ? 's' : '' }}</p>
                </div>
                <div class="p-4">
                    <p class="text-xs text-zinc-400 mb-1">Cuota mensual</p>
                    <p class="text-lg font-bold text-amber-600 dark:text-amber-400">${{ number_format($ccMonthly, 0) }}</p>
                    <p class="text-xs text-zinc-400 mt-0.5">Próximo pago día {{ $creditCard->due_day }}</p>
                </div>
                <div class="p-4">
                    <p class="text-xs text-zinc-400 mb-1">Disponible</p>
                    <p class="text-lg font-bold {{ $ccAvailable >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                        ${{ number_format($ccAvailable, 0) }}
                    </p>
                    <p class="text-xs text-zinc-400 mt-0.5">de ${{ number_format($creditCard->credit_limit, 0) }}</p>
                </div>
                <div class="p-4">
                    <p class="text-xs text-zinc-400 mb-2">Uso del límite</p>
                    <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2 overflow-hidden mb-1">
                        <div class="bg-gradient-to-r {{ $ccBarColor }} h-2 rounded-full transition-all"
                             style="width: {{ min(100, $ccUsedPct) }}%"></div>
                    </div>
                    <p class="text-xs font-semibold {{ $ccUsedPct > 80 ? 'text-rose-500' : ($ccUsedPct > 50 ? 'text-amber-500' : 'text-emerald-500') }}">
                        {{ number_format($ccUsedPct, 1) }}%
                    </p>
                </div>
            </div>

            {{-- Active installments preview --}}
            @if($activeCount > 0)
                <div class="px-5 py-3 border-t border-zinc-100 dark:border-zinc-800 bg-zinc-50/50 dark:bg-zinc-800/30">
                    <div class="flex items-center gap-3 overflow-x-auto pb-1">
                        <span class="text-xs font-medium text-zinc-400 whitespace-nowrap flex-shrink-0">Compras activas:</span>
                        @foreach($creditCard->installmentPurchases->take(5) as $purchase)
                            <div class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-1.5 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg text-xs">
                                <span class="font-medium text-zinc-900 dark:text-white">{{ str($purchase->title)->limit(20) }}</span>
                                <span class="text-zinc-400">·</span>
                                <span class="font-semibold text-rose-600 dark:text-rose-400">${{ number_format($purchase->installment_amount, 0) }}/mes</span>
                                <span class="text-zinc-300 dark:text-zinc-600">·</span>
                                <span class="text-zinc-400">{{ $purchase->paid_installments }}/{{ $purchase->installments_count }}</span>
                            </div>
                        @endforeach
                        @if($activeCount > 5)
                            <a href="{{ route('filament.app.resources.credit-cards.view', $creditCard) }}"
                               class="flex-shrink-0 text-xs text-primary-600 dark:text-primary-400 font-medium whitespace-nowrap">
                                +{{ $activeCount - 5 }} más →
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna Principal (2/3) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Grafico: Actividad del periodo (barras por dia) --}}
            @php
                $barLabels = array_map(fn($d) => $d['date'] . ' ' . $d['day'], $last7Days);
                $barIncome  = array_column($last7Days, 'income');
                $barExpense = array_column($last7Days, 'expense');
            @endphp

            <div
                x-data="{
                    chart: null,
                    labels: {{ json_encode($barLabels) }},
                    income: {{ json_encode($barIncome) }},
                    expense: {{ json_encode($barExpense) }},
                    isDark() {
                        return document.documentElement.classList.contains('dark');
                    },
                    gridColor() {
                        return this.isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
                    },
                    textColor() {
                        return this.isDark() ? '#a1a1aa' : '#71717a';
                    },
                    init() {
                        this.chart = new Chart(this.$refs.canvas, {
                            type: 'bar',
                            data: {
                                labels: this.labels,
                                datasets: [
                                    {
                                        label: 'Ingresos',
                                        data: this.income,
                                        backgroundColor: 'rgba(16,185,129,0.75)',
                                        borderColor: '#10b981',
                                        borderWidth: 1.5,
                                        borderRadius: 5,
                                    },
                                    {
                                        label: 'Gastos',
                                        data: this.expense,
                                        backgroundColor: 'rgba(239,68,68,0.75)',
                                        borderColor: '#ef4444',
                                        borderWidth: 1.5,
                                        borderRadius: 5,
                                    },
                                ],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                plugins: {
                                    legend: {
                                        position: 'top',
                                        labels: { color: this.textColor(), font: { size: 12 } },
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: (ctx) => ' $' + ctx.parsed.y.toLocaleString('es-AR', { minimumFractionDigits: 0 }),
                                        },
                                    },
                                },
                                scales: {
                                    x: {
                                        grid: { color: this.gridColor() },
                                        ticks: { color: this.textColor(), font: { size: 11 } },
                                    },
                                    y: {
                                        grid: { color: this.gridColor() },
                                        ticks: {
                                            color: this.textColor(),
                                            font: { size: 11 },
                                            callback: (v) => '$' + v.toLocaleString('es-AR'),
                                        },
                                        beginAtZero: true,
                                    },
                                },
                            },
                        });
                    },
                }"
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm"
            >
                <div class="px-5 py-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center gap-2">
                    <x-heroicon-o-chart-bar class="w-5 h-5 text-primary-500" />
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-white">
                        Actividad del periodo — últimos {{ count($last7Days) }} días
                    </h3>
                </div>
                <div class="p-5">
                    <canvas x-ref="canvas" style="max-height: 260px;"></canvas>
                </div>
            </div>

            {{-- Grafico: Tendencia 6 meses (líneas) --}}
            @php
                $lineLabels  = array_column($monthlyTrend, 'month');
                $lineIncome  = array_column($monthlyTrend, 'income');
                $lineExpense = array_column($monthlyTrend, 'expense');
                $lineBalance = array_column($monthlyTrend, 'balance');
            @endphp

            <div
                x-data="{
                    chart: null,
                    labels:  {{ json_encode($lineLabels) }},
                    income:  {{ json_encode($lineIncome) }},
                    expense: {{ json_encode($lineExpense) }},
                    balance: {{ json_encode($lineBalance) }},
                    isDark() {
                        return document.documentElement.classList.contains('dark');
                    },
                    gridColor() {
                        return this.isDark() ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
                    },
                    textColor() {
                        return this.isDark() ? '#a1a1aa' : '#71717a';
                    },
                    init() {
                        this.chart = new Chart(this.$refs.canvas, {
                            type: 'line',
                            data: {
                                labels: this.labels,
                                datasets: [
                                    {
                                        label: 'Ingresos',
                                        data: this.income,
                                        borderColor: '#10b981',
                                        backgroundColor: 'rgba(16,185,129,0.08)',
                                        pointBackgroundColor: '#10b981',
                                        borderWidth: 2.5,
                                        pointRadius: 4,
                                        tension: 0.35,
                                        fill: false,
                                    },
                                    {
                                        label: 'Gastos',
                                        data: this.expense,
                                        borderColor: '#ef4444',
                                        backgroundColor: 'rgba(239,68,68,0.08)',
                                        pointBackgroundColor: '#ef4444',
                                        borderWidth: 2.5,
                                        pointRadius: 4,
                                        tension: 0.35,
                                        fill: false,
                                    },
                                    {
                                        label: 'Balance',
                                        data: this.balance,
                                        borderColor: '#6366f1',
                                        backgroundColor: 'rgba(99,102,241,0.08)',
                                        pointBackgroundColor: '#6366f1',
                                        borderWidth: 2,
                                        pointRadius: 4,
                                        tension: 0.35,
                                        borderDash: [5, 3],
                                        fill: false,
                                    },
                                ],
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                interaction: { mode: 'index', intersect: false },
                                plugins: {
                                    legend: {
                                        position: 'top',
                                        labels: { color: this.textColor(), font: { size: 12 }, usePointStyle: true, pointStyleWidth: 10 },
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: (ctx) => ' ' + ctx.dataset.label + ': $' + ctx.parsed.y.toLocaleString('es-AR', { minimumFractionDigits: 0 }),
                                        },
                                    },
                                },
                                scales: {
                                    x: {
                                        grid: { color: this.gridColor() },
                                        ticks: { color: this.textColor(), font: { size: 11 } },
                                    },
                                    y: {
                                        grid: { color: this.gridColor() },
                                        ticks: {
                                            color: this.textColor(),
                                            font: { size: 11 },
                                            callback: (v) => '$' + v.toLocaleString('es-AR'),
                                        },
                                    },
                                },
                            },
                        });
                    },
                }"
                class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm"
            >
                <div class="px-5 py-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center gap-2">
                    <x-heroicon-o-arrow-trending-up class="w-5 h-5 text-primary-500" />
                    <h3 class="text-base font-semibold text-zinc-900 dark:text-white">
                        Tendencia mensual — últimos 6 meses
                    </h3>
                </div>
                <div class="p-5">
                    <canvas x-ref="canvas" style="max-height: 260px;"></canvas>
                </div>
            </div>

            {{-- Transacciones Recientes --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm">
                <div class="p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-base font-semibold text-zinc-900 dark:text-white">
                            <x-heroicon-o-clipboard-document-list class="w-5 h-5 text-primary-500" />
                            Transacciones del mes
                        </h3>
                        <a href="{{ \App\Filament\Resources\TransactionResource::getUrl('index', ['tableFilters[account_id][values][0]' => $account->id]) }}"
                           class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-lg bg-primary-50 dark:bg-primary-950/30 text-primary-700 dark:text-primary-400 hover:bg-primary-100 dark:hover:bg-primary-950/50 transition-colors">
                            <x-heroicon-m-funnel class="w-3.5 h-3.5" />
                            Ver todas con filtro
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
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm">
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
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm">
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
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm">
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
                                        <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2 overflow-hidden">
                                            <div class="bg-gradient-to-r from-rose-400 to-rose-600 h-2 rounded-full transition-all"
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
                                        <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2 overflow-hidden">
                                            <div class="bg-gradient-to-r from-emerald-400 to-emerald-600 h-2 rounded-full transition-all"
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

@once
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
@endonce
