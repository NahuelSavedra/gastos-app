<x-filament-panels::page>
    @php
        $viewData = $this->getViewData();
        $card = $viewData['card'];
        $activePurchases = $viewData['activePurchases'];
        $completedPurchases = $viewData['completedPurchases'];
        $thisMonthPurchases = $viewData['thisMonthPurchases'];
        $completingSoon = $viewData['completingSoon'];
        $totalDebt = $viewData['totalDebt'];
        $monthlyPayment = $viewData['monthlyPayment'];
        $availableCredit = $viewData['availableCredit'];
        $usedPercentage = $card->credit_limit > 0 ? ($totalDebt / $card->credit_limit) * 100 : 0;
        $barGradient = $usedPercentage > 80 ? 'from-rose-500 to-rose-600' : ($usedPercentage > 50 ? 'from-amber-400 to-amber-500' : 'from-emerald-400 to-emerald-500');
    @endphp

    {{-- Visual credit card + metrics --}}
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-5 mb-6">

        {{-- Credit card visual (spans 1 col) --}}
        <div class="lg:col-span-1">
            <div class="relative overflow-hidden bg-gradient-to-br from-zinc-800 via-zinc-900 to-zinc-950 rounded-2xl shadow-xl p-6 h-full min-h-[160px] flex flex-col justify-between">
                <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full bg-white/5"></div>
                <div class="absolute -left-4 -bottom-10 w-28 h-28 rounded-full bg-white/5"></div>

                <div class="flex items-start justify-between relative">
                    <div>
                        <p class="text-xs text-zinc-400 uppercase tracking-widest mb-1">Tarjeta de Crédito</p>
                        <p class="text-lg font-bold text-white">{{ $card->name }}</p>
                    </div>
                    <x-heroicon-o-credit-card class="w-7 h-7 text-zinc-400" />
                </div>

                @if($card->last_four)
                    <p class="text-sm text-zinc-400 tracking-widest relative mt-4">
                        ···· ···· ···· {{ $card->last_four }}
                    </p>
                @endif

                <div class="flex items-center justify-between relative mt-4">
                    <div>
                        <p class="text-xs text-zinc-500">Cierre</p>
                        <p class="text-sm font-medium text-zinc-300">Día {{ $card->closing_day }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-zinc-500">Vencimiento</p>
                        <p class="text-sm font-medium text-zinc-300">Día {{ $card->due_day }}</p>
                    </div>
                    <span class="absolute -bottom-1 right-0 flex h-5 w-5 items-center justify-center">
                        <span class="w-2 h-2 rounded-full {{ $card->is_active ? 'bg-emerald-400' : 'bg-zinc-500' }}"></span>
                    </span>
                </div>
            </div>
        </div>

        {{-- Metrics (span 3 cols) --}}
        <div class="lg:col-span-3 grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Deuda Total --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-rose-500 to-rose-700 rounded-xl shadow-sm text-white p-5">
                <div class="absolute -right-5 -top-5 w-20 h-20 rounded-full bg-white/10"></div>
                <p class="text-xs font-medium text-rose-100 relative">Deuda Total</p>
                <p class="text-2xl font-bold mt-1 relative">${{ number_format($totalDebt, 2) }}</p>
                <p class="text-xs text-rose-100 mt-2 relative">
                    {{ $activePurchases->count() }} compra{{ $activePurchases->count() !== 1 ? 's' : '' }} activa{{ $activePurchases->count() !== 1 ? 's' : '' }}
                </p>
            </div>

            {{-- Crédito Disponible --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm p-5">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Crédito Disponible</p>
                    <div class="w-8 h-8 rounded-lg {{ $availableCredit >= 0 ? 'bg-emerald-50 dark:bg-emerald-950/30' : 'bg-rose-50 dark:bg-rose-950/30' }} flex items-center justify-center">
                        <x-heroicon-m-check-circle class="w-4 h-4 {{ $availableCredit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}" />
                    </div>
                </div>
                <p class="text-2xl font-bold {{ $availableCredit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                    ${{ number_format($availableCredit, 2) }}
                </p>
                <div class="mt-3">
                    <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2 overflow-hidden">
                        <div class="bg-gradient-to-r {{ $barGradient }} h-2 rounded-full transition-all duration-500"
                             style="width: {{ min(100, $usedPercentage) }}%"></div>
                    </div>
                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1">{{ number_format($usedPercentage, 1) }}% utilizado</p>
                </div>
            </div>

            {{-- Pago Mensual --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-amber-500 to-amber-700 rounded-xl shadow-sm text-white p-5">
                <div class="absolute -right-5 -top-5 w-20 h-20 rounded-full bg-white/10"></div>
                <p class="text-xs font-medium text-amber-100 relative">Pago Mensual</p>
                <p class="text-2xl font-bold mt-1 relative">${{ number_format($monthlyPayment, 2) }}</p>
                <p class="text-xs text-amber-100 mt-2 relative">Suma de cuotas activas</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna Principal (2/3) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Compras Activas --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-100 dark:border-zinc-800 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-base font-semibold text-zinc-900 dark:text-white">
                        <x-heroicon-o-shopping-cart class="w-5 h-5 text-primary-500" />
                        Compras Activas
                        @if($activePurchases->count() > 0)
                            <span class="text-xs bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 px-2 py-0.5 rounded-full font-medium">
                                {{ $activePurchases->count() }}
                            </span>
                        @endif
                    </h3>
                    <a href="{{ route('filament.app.resources.installment-purchases.create', ['credit_card_id' => $card->id]) }}"
                       class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-950/20 px-3 py-1.5 rounded-lg transition-colors">
                        <x-heroicon-m-plus class="w-3.5 h-3.5" />
                        Nueva compra
                    </a>
                </div>

                <div class="p-5 space-y-3">
                    @forelse($activePurchases as $purchase)
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-xl border border-zinc-100 dark:border-zinc-700/50 hover:border-zinc-200 dark:hover:border-zinc-700 transition-colors">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $purchase->title }}</p>
                                        @if($purchase->remaining_installments === 1)
                                            <span class="text-xs bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 px-2 py-0.5 rounded-full font-medium">
                                                Última cuota
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-0.5">
                                        @if($purchase->store){{ $purchase->store }} · @endif
                                        @if($purchase->category){{ $purchase->category->name }} · @endif
                                        Total: ${{ number_format($purchase->total_amount, 2) }}
                                    </p>
                                </div>
                                <div class="text-right ml-4 flex-shrink-0">
                                    <p class="text-base font-bold text-rose-600 dark:text-rose-400">
                                        ${{ number_format($purchase->installment_amount, 2) }}<span class="text-xs font-medium">/mes</span>
                                    </p>
                                    <p class="text-xs text-zinc-400">Restante: ${{ number_format($purchase->remaining_amount, 2) }}</p>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between text-xs text-zinc-400 mb-1.5">
                                    <span>{{ $purchase->paid_installments }} de {{ $purchase->installments_count }} cuotas</span>
                                    <span class="font-semibold text-primary-600 dark:text-primary-400">{{ number_format($purchase->progress_percentage, 0) }}%</span>
                                </div>
                                <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r from-primary-400 to-primary-600 h-2 rounded-full transition-all duration-500"
                                         style="width: {{ $purchase->progress_percentage }}%"></div>
                                </div>
                            </div>

                            @if($purchase->next_payment_date)
                                <p class="text-xs text-zinc-400 mt-2">
                                    Próx. cuota: <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ $purchase->next_payment_date->format('d/m/Y') }}</span>
                                </p>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-12 text-zinc-500 dark:text-zinc-400">
                            <x-heroicon-o-shopping-cart class="w-10 h-10 mx-auto mb-3 opacity-30" />
                            <p class="text-sm mb-2">No hay compras en cuotas activas</p>
                            <a href="{{ route('filament.app.resources.installment-purchases.create', ['credit_card_id' => $card->id]) }}"
                               class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium">
                                Registrar primera compra →
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Compras Completadas --}}
            @if($completedPurchases->count() > 0)
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
                        <h3 class="flex items-center gap-2 text-base font-semibold text-zinc-900 dark:text-white">
                            <x-heroicon-o-check-badge class="w-5 h-5 text-emerald-500" />
                            Compras Completadas
                            <span class="text-xs bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 px-2 py-0.5 rounded-full">
                                {{ $completedPurchases->count() }}
                            </span>
                        </h3>
                    </div>
                    <div class="p-5 space-y-2">
                        @foreach($completedPurchases as $purchase)
                            <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800/30 rounded-lg opacity-60">
                                <div>
                                    <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400 line-through">{{ $purchase->title }}</p>
                                    <p class="text-xs text-zinc-400">{{ $purchase->installments_count }} cuotas · ${{ number_format($purchase->total_amount, 2) }}</p>
                                </div>
                                <span class="text-xs bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 px-2.5 py-1 rounded-full font-semibold">
                                    ✓ Pagado
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Columna Lateral (1/3) --}}
        <div class="space-y-5">

            {{-- Información de la Tarjeta --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
                    <h3 class="flex items-center gap-2 text-sm font-semibold text-zinc-900 dark:text-white">
                        <x-heroicon-o-information-circle class="w-4 h-4 text-primary-500" />
                        Información
                    </h3>
                </div>
                <div class="p-5 space-y-3">
                    @if($card->last_four)
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-zinc-400">Número</p>
                            <p class="text-sm font-mono font-medium text-zinc-900 dark:text-white">···· ···· ···· {{ $card->last_four }}</p>
                        </div>
                    @endif
                    @if($card->account)
                        <div class="flex items-center justify-between">
                            <p class="text-xs text-zinc-400">Cuenta</p>
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $card->account->name }}</p>
                        </div>
                    @endif
                    <div class="flex items-center justify-between">
                        <p class="text-xs text-zinc-400">Límite</p>
                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">${{ number_format($card->credit_limit, 2) }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-3 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <div class="text-center p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                            <p class="text-xs text-zinc-400 mb-0.5">Cierre</p>
                            <p class="text-base font-bold text-zinc-900 dark:text-white">{{ $card->closing_day }}</p>
                        </div>
                        <div class="text-center p-2 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                            <p class="text-xs text-zinc-400 mb-0.5">Vencimiento</p>
                            <p class="text-base font-bold text-zinc-900 dark:text-white">{{ $card->due_day }}</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-1">
                        <p class="text-xs text-zinc-400">Estado</p>
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full
                            {{ $card->is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $card->is_active ? 'bg-emerald-500' : 'bg-zinc-400' }}"></span>
                            {{ $card->is_active ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Cuotas que vencen este mes --}}
            @if($thisMonthPurchases->count() > 0)
                <div class="bg-white dark:bg-zinc-900 border border-amber-200 dark:border-amber-800/50 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-amber-100 dark:border-amber-800/30 bg-amber-50/50 dark:bg-amber-950/10">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-amber-800 dark:text-amber-300">
                            <x-heroicon-o-calendar class="w-4 h-4 text-amber-500" />
                            Cuotas de Este Mes
                        </h3>
                    </div>
                    <div class="p-5 space-y-3">
                        @foreach($thisMonthPurchases as $purchase)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $purchase->title }}</p>
                                    <p class="text-xs text-zinc-400">Cuota {{ $purchase->paid_installments + 1 }}/{{ $purchase->installments_count }}</p>
                                </div>
                                <p class="text-sm font-bold text-amber-600 dark:text-amber-400">
                                    ${{ number_format($purchase->installment_amount, 2) }}
                                </p>
                            </div>
                        @endforeach
                        <div class="flex items-center justify-between pt-3 border-t border-zinc-100 dark:border-zinc-800">
                            <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">Total del mes</span>
                            <span class="text-base font-bold text-amber-600 dark:text-amber-400">
                                ${{ number_format($thisMonthPurchases->sum('installment_amount'), 2) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Terminan próximamente --}}
            @if($completingSoon->count() > 0)
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-xl shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-zinc-100 dark:border-zinc-800">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-zinc-900 dark:text-white">
                            <x-heroicon-o-bell-alert class="w-4 h-4 text-amber-500" />
                            Terminan Próximamente
                        </h3>
                    </div>
                    <div class="p-5 space-y-2">
                        @foreach($completingSoon as $purchase)
                            <div class="flex items-center justify-between p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg border border-amber-100 dark:border-amber-800/30">
                                <div>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $purchase->title }}</p>
                                    <p class="text-xs text-amber-600 dark:text-amber-400 font-medium">Última cuota</p>
                                </div>
                                <p class="text-sm font-bold text-zinc-700 dark:text-zinc-300">
                                    ${{ number_format($purchase->installment_amount, 2) }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
