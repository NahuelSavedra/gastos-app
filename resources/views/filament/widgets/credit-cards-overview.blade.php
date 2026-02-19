<x-filament-widgets::widget>
    @php
        $viewData = $this->getViewData();
        $cards = $viewData['cards'];
        $totalDebt = $viewData['totalDebt'];
        $totalMonthly = $viewData['totalMonthly'];
    @endphp

    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-credit-card class="w-5 h-5 text-rose-500" />
                Tarjetas de Crédito
            </div>
        </x-slot>

        {{-- Resumen global --}}
        <div class="grid grid-cols-2 gap-4 mb-5">
            <div class="p-4 bg-rose-50 dark:bg-rose-950/20 rounded-lg border border-rose-100 dark:border-rose-900/30">
                <p class="text-xs text-rose-600 dark:text-rose-400 font-medium">Deuda Total Consolidada</p>
                <p class="text-xl font-bold text-rose-700 dark:text-rose-300 mt-1">
                    ${{ number_format($totalDebt, 2) }}
                </p>
            </div>
            <div class="p-4 bg-amber-50 dark:bg-amber-950/20 rounded-lg border border-amber-100 dark:border-amber-900/30">
                <p class="text-xs text-amber-600 dark:text-amber-400 font-medium">Pago Mensual Total</p>
                <p class="text-xl font-bold text-amber-700 dark:text-amber-300 mt-1">
                    ${{ number_format($totalMonthly, 2) }}
                </p>
            </div>
        </div>

        {{-- Tarjetas individuales --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($cards as $card)
                @php
                    $debt = $card->total_debt;
                    $monthly = $card->monthly_payment;
                    $usedPct = $card->credit_limit > 0 ? ($debt / $card->credit_limit) * 100 : 0;

                    // Cuotas que vencen este mes
                    $thisMonthPayments = $card->installmentPurchases->filter(function ($p) {
                        $next = $p->next_payment_date;
                        return $next && $next->month === now()->month && $next->year === now()->year;
                    });
                    $hasAlert = $thisMonthPayments->count() > 0;
                @endphp
                <a href="{{ route('filament.app.resources.credit-cards.view', $card) }}"
                   class="block p-4 bg-white dark:bg-zinc-900 border {{ $hasAlert ? 'border-amber-300 dark:border-amber-700' : 'border-zinc-200 dark:border-zinc-800' }} rounded-lg hover:shadow-md transition-shadow">

                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-sm font-semibold text-zinc-900 dark:text-white">
                                {{ $card->name }}
                            </p>
                            @if($card->last_four)
                                <p class="text-xs text-zinc-500 dark:text-zinc-400">···· {{ $card->last_four }}</p>
                            @endif
                        </div>
                        @if($hasAlert)
                            <span class="text-xs bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300 px-2 py-0.5 rounded-full font-medium">
                                {{ $thisMonthPayments->count() }} cuota{{ $thisMonthPayments->count() !== 1 ? 's' : '' }} este mes
                            </span>
                        @endif
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-3">
                        <div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Deuda</p>
                            <p class="text-sm font-bold text-rose-600 dark:text-rose-400">${{ number_format($debt, 2) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400">Pago mensual</p>
                            <p class="text-sm font-bold text-amber-600 dark:text-amber-400">${{ number_format($monthly, 2) }}</p>
                        </div>
                    </div>

                    {{-- Barra de uso --}}
                    <div>
                        <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-1.5">
                            <div class="{{ $usedPct > 80 ? 'bg-rose-500' : ($usedPct > 50 ? 'bg-amber-500' : 'bg-emerald-500') }} h-1.5 rounded-full transition-all"
                                 style="width: {{ min(100, $usedPct) }}%"></div>
                        </div>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                            {{ number_format($usedPct, 1) }}% del límite · Vence día {{ $card->due_day }}
                        </p>
                    </div>
                </a>
            @endforeach
        </div>

        @if($cards->isEmpty())
            <div class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                <x-heroicon-o-credit-card class="w-10 h-10 mx-auto mb-3 opacity-30" />
                <p class="text-sm">No hay tarjetas de crédito registradas</p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
