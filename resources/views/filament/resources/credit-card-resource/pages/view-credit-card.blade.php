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
    @endphp

    {{-- Header con Métricas Principales --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Límite de crédito --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Límite de Crédito</p>
                    <p class="text-2xl font-semibold text-zinc-900 dark:text-white mt-1">
                        ${{ number_format($card->credit_limit, 2) }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 dark:bg-blue-950/30 flex items-center justify-center">
                    <x-heroicon-o-credit-card class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
            </div>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">
                Cierra día {{ $card->closing_day }}, vence día {{ $card->due_day }}
            </p>
        </div>

        {{-- Deuda Total --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Deuda Total</p>
                    <p class="text-2xl font-semibold text-rose-600 dark:text-rose-400 mt-1">
                        ${{ number_format($totalDebt, 2) }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-rose-50 dark:bg-rose-950/30 flex items-center justify-center">
                    <x-heroicon-o-arrow-trending-down class="w-5 h-5 text-rose-600 dark:text-rose-400" />
                </div>
            </div>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">
                {{ $activePurchases->count() }} compra{{ $activePurchases->count() !== 1 ? 's' : '' }} activa{{ $activePurchases->count() !== 1 ? 's' : '' }}
            </p>
        </div>

        {{-- Crédito Disponible --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Crédito Disponible</p>
                    <p class="text-2xl font-semibold {{ $availableCredit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} mt-1">
                        ${{ number_format($availableCredit, 2) }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-lg {{ $availableCredit >= 0 ? 'bg-emerald-50 dark:bg-emerald-950/30' : 'bg-rose-50 dark:bg-rose-950/30' }} flex items-center justify-center">
                    <x-heroicon-o-check-circle class="w-5 h-5 {{ $availableCredit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}" />
                </div>
            </div>
            <div class="mt-2">
                <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-1.5">
                    <div class="bg-rose-500 h-1.5 rounded-full transition-all"
                         style="width: {{ min(100, $usedPercentage) }}%"></div>
                </div>
                <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">{{ number_format($usedPercentage, 1) }}% utilizado</p>
            </div>
        </div>

        {{-- Pago Mensual Estimado --}}
        <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pago Mensual Estimado</p>
                    <p class="text-2xl font-semibold text-amber-600 dark:text-amber-400 mt-1">
                        ${{ number_format($monthlyPayment, 2) }}
                    </p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 dark:bg-amber-950/30 flex items-center justify-center">
                    <x-heroicon-o-calendar-days class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                </div>
            </div>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-2">
                Suma de cuotas activas
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Columna Principal (2/3) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Compras Activas --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm">
                <div class="p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <div class="flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-base font-semibold text-zinc-900 dark:text-white">
                            <x-heroicon-o-shopping-cart class="w-5 h-5 text-primary-500" />
                            Compras Activas
                            @if($activePurchases->count() > 0)
                                <span class="text-xs bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 px-2 py-0.5 rounded-full">
                                    {{ $activePurchases->count() }}
                                </span>
                            @endif
                        </h3>
                        <a href="{{ route('filament.app.resources.installment-purchases.create', ['credit_card_id' => $card->id]) }}"
                           class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium">
                            + Nueva compra
                        </a>
                    </div>
                </div>
                <div class="p-5">
                    @forelse($activePurchases as $purchase)
                        <div class="mb-4 last:mb-0 p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg border border-zinc-100 dark:border-zinc-700">
                            <div class="flex items-start justify-between mb-3">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                                            {{ $purchase->title }}
                                        </p>
                                        @if($purchase->remaining_installments === 1)
                                            <span class="text-xs bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 px-1.5 py-0.5 rounded-full">
                                                Última cuota
                                            </span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                                        @if($purchase->store){{ $purchase->store }} · @endif
                                        @if($purchase->category){{ $purchase->category->name }} · @endif
                                        Total: ${{ number_format($purchase->total_amount, 2) }}
                                    </p>
                                </div>
                                <div class="text-right ml-4 flex-shrink-0">
                                    <p class="text-sm font-bold text-rose-600 dark:text-rose-400">
                                        ${{ number_format($purchase->installment_amount, 2) }}/mes
                                    </p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        Restante: ${{ number_format($purchase->remaining_amount, 2) }}
                                    </p>
                                </div>
                            </div>

                            {{-- Barra de progreso --}}
                            <div class="mb-2">
                                <div class="flex items-center justify-between text-xs text-zinc-500 dark:text-zinc-400 mb-1">
                                    <span>{{ $purchase->paid_installments }} de {{ $purchase->installments_count }} cuotas pagadas</span>
                                    <span>{{ number_format($purchase->progress_percentage, 0) }}%</span>
                                </div>
                                <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                                    <div class="bg-primary-500 h-2 rounded-full transition-all"
                                         style="width: {{ $purchase->progress_percentage }}%"></div>
                                </div>
                            </div>

                            @if($purchase->next_payment_date)
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                    Próx. cuota: <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ $purchase->next_payment_date->format('d/m/Y') }}</span>
                                </p>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-10 text-zinc-500 dark:text-zinc-400">
                            <x-heroicon-o-shopping-cart class="w-10 h-10 mx-auto mb-3 opacity-30" />
                            <p class="text-sm">No hay compras en cuotas activas</p>
                            <a href="{{ route('filament.app.resources.installment-purchases.create', ['credit_card_id' => $card->id]) }}"
                               class="mt-2 inline-block text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 font-medium">
                                Registrar primera compra
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Compras Completadas --}}
            @if($completedPurchases->count() > 0)
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm">
                    <div class="p-5 border-b border-zinc-100 dark:border-zinc-800">
                        <h3 class="flex items-center gap-2 text-base font-semibold text-zinc-900 dark:text-white">
                            <x-heroicon-o-check-badge class="w-5 h-5 text-emerald-500" />
                            Compras Completadas
                            <span class="text-xs bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 px-2 py-0.5 rounded-full">
                                {{ $completedPurchases->count() }}
                            </span>
                        </h3>
                    </div>
                    <div class="p-5">
                        <div class="space-y-3">
                            @foreach($completedPurchases as $purchase)
                                <div class="flex items-center justify-between p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg opacity-70">
                                    <div>
                                        <p class="text-sm font-medium text-zinc-900 dark:text-white line-through">
                                            {{ $purchase->title }}
                                        </p>
                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $purchase->installments_count }} cuotas · ${{ number_format($purchase->total_amount, 2) }}
                                        </p>
                                    </div>
                                    <span class="text-xs bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 px-2 py-1 rounded-full font-medium">
                                        Pagado
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Columna Lateral (1/3) --}}
        <div class="space-y-6">

            {{-- Información de la Tarjeta --}}
            <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm">
                <div class="p-5 border-b border-zinc-100 dark:border-zinc-800">
                    <h3 class="flex items-center gap-2 text-base font-semibold text-zinc-900 dark:text-white">
                        <x-heroicon-o-information-circle class="w-5 h-5 text-primary-500" />
                        Información
                    </h3>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Nombre</p>
                        <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $card->name }}</p>
                    </div>
                    @if($card->last_four)
                        <div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Número</p>
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">···· ···· ···· {{ $card->last_four }}</p>
                        </div>
                    @endif
                    @if($card->account)
                        <div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Cuenta vinculada</p>
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $card->account->name }}</p>
                        </div>
                    @endif
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Día de cierre</p>
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">Día {{ $card->closing_day }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Día de vencimiento</p>
                            <p class="text-sm font-medium text-zinc-900 dark:text-white">Día {{ $card->due_day }}</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">Estado</p>
                        <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full {{ $card->is_active ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300' : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500' }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $card->is_active ? 'bg-emerald-500' : 'bg-zinc-400' }}"></span>
                            {{ $card->is_active ? 'Activa' : 'Inactiva' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Cuotas que vencen este mes --}}
            @if($thisMonthPurchases->count() > 0)
                <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-lg shadow-sm">
                    <div class="p-5 border-b border-zinc-100 dark:border-zinc-800">
                        <h3 class="flex items-center gap-2 text-base font-semibold text-zinc-900 dark:text-white">
                            <x-heroicon-o-calendar class="w-5 h-5 text-amber-500" />
                            Cuotas de Este Mes
                        </h3>
                    </div>
                    <div class="p-5 space-y-3">
                        @foreach($thisMonthPurchases as $purchase)
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $purchase->title }}</p>
                                    <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                        Cuota {{ $purchase->paid_installments + 1 }}/{{ $purchase->installments_count }}
                                    </p>
                                </div>
                                <p class="text-sm font-semibold text-amber-600 dark:text-amber-400">
                                    ${{ number_format($purchase->installment_amount, 2) }}
                                </p>
                            </div>
                        @endforeach
                        <div class="pt-3 border-t border-zinc-100 dark:border-zinc-800">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Total del mes</span>
                                <span class="text-sm font-bold text-amber-600 dark:text-amber-400">
                                    ${{ number_format($thisMonthPurchases->sum('installment_amount'), 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Compras que terminan pronto --}}
            @if($completingSoon->count() > 0)
                <div class="bg-white dark:bg-zinc-900 border border-amber-200 dark:border-amber-800/50 rounded-lg shadow-sm">
                    <div class="p-5 border-b border-amber-100 dark:border-amber-800/30">
                        <h3 class="flex items-center gap-2 text-base font-semibold text-amber-800 dark:text-amber-300">
                            <x-heroicon-o-bell-alert class="w-5 h-5 text-amber-500" />
                            Terminan Próximamente
                        </h3>
                    </div>
                    <div class="p-5 space-y-3">
                        @foreach($completingSoon as $purchase)
                            <div class="flex items-center justify-between p-2 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
                                <div>
                                    <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $purchase->title }}</p>
                                    <p class="text-xs text-amber-600 dark:text-amber-400">Última cuota</p>
                                </div>
                                <p class="text-sm font-semibold text-zinc-700 dark:text-zinc-300">
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
