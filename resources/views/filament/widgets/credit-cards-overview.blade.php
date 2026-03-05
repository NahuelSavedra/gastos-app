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
            <div class="relative overflow-hidden p-4 bg-gradient-to-br from-rose-500 to-rose-700 rounded-xl text-white shadow-sm">
                <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
                <div class="absolute -right-2 -bottom-6 w-14 h-14 rounded-full bg-white/10"></div>
                <p class="text-xs font-medium text-rose-100 relative">Deuda Total Consolidada</p>
                <p class="text-2xl font-bold mt-1 relative">${{ number_format($totalDebt, 2) }}</p>
            </div>
            <div class="relative overflow-hidden p-4 bg-gradient-to-br from-amber-500 to-amber-700 rounded-xl text-white shadow-sm">
                <div class="absolute -right-4 -top-4 w-20 h-20 rounded-full bg-white/10"></div>
                <div class="absolute -right-2 -bottom-6 w-14 h-14 rounded-full bg-white/10"></div>
                <p class="text-xs font-medium text-amber-100 relative">Pago Mensual Total</p>
                <p class="text-2xl font-bold mt-1 relative">${{ number_format($totalMonthly, 2) }}</p>
            </div>
        </div>

        {{-- Tarjetas individuales --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($cards as $card)
                @php
                    $debt = $card->total_debt;
                    $monthly = $card->monthly_payment;
                    $usedPct = $card->credit_limit > 0 ? ($debt / $card->credit_limit) * 100 : 0;
                    $barColor = $usedPct > 80 ? 'from-rose-500 to-rose-600' : ($usedPct > 50 ? 'from-amber-400 to-amber-500' : 'from-emerald-400 to-emerald-500');

                    $thisMonthPayments = $card->installmentPurchases->filter(function ($p) {
                        $next = $p->next_payment_date;
                        return $next && $next->month === now()->month && $next->year === now()->year;
                    });
                    $hasAlert = $thisMonthPayments->count() > 0;
                @endphp

                <a href="{{ route('filament.app.resources.credit-cards.view', $card) }}"
                   class="group block bg-white dark:bg-zinc-900 border {{ $hasAlert ? 'border-amber-300 dark:border-amber-700' : 'border-zinc-200 dark:border-zinc-800' }} rounded-xl hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 overflow-hidden">

                    {{-- Card gradient header --}}
                    <div class="relative px-5 pt-5 pb-4 bg-gradient-to-br from-zinc-800 to-zinc-900 dark:from-zinc-950 dark:to-zinc-900">
                        {{-- Card chip decoration --}}
                        <div class="absolute top-4 right-4 w-8 h-6 rounded bg-gradient-to-br from-amber-300 to-amber-500 opacity-80"></div>
                        <div class="absolute top-6 right-6 w-4 h-3 rounded bg-gradient-to-br from-amber-200 to-amber-400 opacity-60"></div>

                        <div class="flex items-start justify-between">
                            <div>
                                <p class="text-base font-bold text-white tracking-wide">
                                    {{ $card->name }}
                                </p>
                                @if($card->last_four)
                                    <p class="text-xs text-zinc-400 mt-0.5 tracking-widest">···· ···· ···· {{ $card->last_four }}</p>
                                @endif
                            </div>
                        </div>

                        @if($hasAlert)
                            <div class="mt-3">
                                <span class="inline-flex items-center gap-1 text-xs bg-amber-500/20 text-amber-300 border border-amber-500/30 px-2 py-0.5 rounded-full font-medium">
                                    <x-heroicon-m-bell-alert class="w-3 h-3" />
                                    {{ $thisMonthPayments->count() }} cuota{{ $thisMonthPayments->count() !== 1 ? 's' : '' }} este mes
                                </span>
                            </div>
                        @endif
                    </div>

                    {{-- Card body --}}
                    <div class="p-5">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <p class="text-xs text-zinc-400 dark:text-zinc-500 mb-0.5">Deuda total</p>
                                <p class="text-lg font-bold text-rose-600 dark:text-rose-400">${{ number_format($debt, 0) }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-zinc-400 dark:text-zinc-500 mb-0.5">Cuota mensual</p>
                                <p class="text-lg font-bold text-amber-600 dark:text-amber-400">${{ number_format($monthly, 0) }}</p>
                            </div>
                        </div>

                        {{-- Barra de uso --}}
                        <div>
                            <div class="flex justify-between text-xs text-zinc-400 dark:text-zinc-500 mb-1.5">
                                <span>Uso del límite</span>
                                <span class="font-semibold {{ $usedPct > 80 ? 'text-rose-500' : ($usedPct > 50 ? 'text-amber-500' : 'text-emerald-500') }}">
                                    {{ number_format($usedPct, 1) }}%
                                </span>
                            </div>
                            <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2 overflow-hidden">
                                <div class="bg-gradient-to-r {{ $barColor }} h-2 rounded-full transition-all duration-500"
                                     style="width: {{ min(100, $usedPct) }}%"></div>
                            </div>
                            <p class="text-xs text-zinc-400 dark:text-zinc-500 mt-1.5">
                                Vence día {{ $card->due_day }} · Límite ${{ number_format($card->credit_limit, 0) }}
                            </p>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        @if($cards->isEmpty())
            <div class="text-center py-10 text-zinc-500 dark:text-zinc-400">
                <x-heroicon-o-credit-card class="w-10 h-10 mx-auto mb-3 opacity-30" />
                <p class="text-sm">No hay tarjetas de crédito registradas</p>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
