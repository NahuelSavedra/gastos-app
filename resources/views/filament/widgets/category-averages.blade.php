<x-filament-widgets::widget>
    @php
        $data = $this->getViewData();
        $categories = $data['categories'];
        $monthLabel = $data['monthLabel'];
        $previousMonthLabel = $data['previousMonthLabel'];
        $insufficientData = $data['insufficientData'];
    @endphp

    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-heroicon-o-chart-bar-square class="w-5 h-5 text-primary-500" />
                Comparación de Gastos por Categoría
            </div>
        </x-slot>

        <x-slot name="description">
            {{ $monthLabel }} vs {{ $previousMonthLabel }}
        </x-slot>

        @if($insufficientData)
            <div class="text-center py-10">
                <div class="w-14 h-14 rounded-2xl bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-3">
                    <x-heroicon-o-chart-bar class="w-7 h-7 text-zinc-400" />
                </div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">No hay datos del mes anterior para comparar</p>
            </div>
        @elseif(count($categories) === 0)
            <div class="text-center py-10">
                <div class="w-14 h-14 rounded-full bg-emerald-50 dark:bg-emerald-950/30 flex items-center justify-center mx-auto mb-3">
                    <x-heroicon-o-check-circle class="w-7 h-7 text-emerald-500" />
                </div>
                <p class="text-sm text-zinc-500 dark:text-zinc-400">No hay gastos registrados en este periodo</p>
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-800">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                    <thead>
                        <tr class="bg-zinc-50 dark:bg-zinc-800/70">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Categoría
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider hidden sm:table-cell">
                                Mes Anterior
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Actual
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider" style="min-width: 160px;">
                                Progreso
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Estado
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 bg-white dark:bg-zinc-900">
                        @foreach($categories as $index => $category)
                            @php
                                $isOverBudget = $category['status'] === 'over_budget';
                                $isOnTrack = $category['status'] === 'on_track';
                                $barGradient = $isOverBudget ? 'from-rose-400 to-rose-600' : ($isOnTrack ? 'from-emerald-400 to-emerald-500' : 'from-amber-400 to-amber-500');
                                $rowBg = $isOverBudget ? 'bg-rose-50/30 dark:bg-rose-950/10' : '';
                            @endphp
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors {{ $rowBg }}">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-zinc-900 dark:text-white">{{ $category['name'] }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-right hidden sm:table-cell">
                                    <span class="text-sm text-zinc-400 dark:text-zinc-500">
                                        @if($category['previous'] > 0)
                                            ${{ number_format($category['previous'], 0) }}
                                        @else
                                            —
                                        @endif
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                    <span class="text-sm font-bold text-zinc-900 dark:text-white">
                                        ${{ number_format($category['current'], 0) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    @php
                                        $barWidth = $category['percentage'] !== null ? min($category['percentage'], 150) : 0;
                                    @endphp
                                    <div class="w-full bg-zinc-100 dark:bg-zinc-800 rounded-full h-2 overflow-hidden">
                                        <div class="bg-gradient-to-r {{ $barGradient }} h-2 rounded-full transition-all duration-500"
                                             style="width: {{ $barWidth }}%"></div>
                                    </div>
                                    @if($category['percentage'] !== null)
                                        <p class="text-xs text-zinc-400 mt-1">{{ $category['percentage'] }}%</p>
                                    @elseif($category['previous'] == 0)
                                        <p class="text-xs text-zinc-400 mt-1">Nueva</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                    @php
                                        $badge = match($category['status']) {
                                            'on_track' => ['text' => 'En control', 'class' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400'],
                                            'over_budget' => ['text' => 'Excedido', 'class' => 'bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400'],
                                            'neutral' => ['text' => 'Normal', 'class' => 'bg-amber-100 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400'],
                                            default => ['text' => 'N/A', 'class' => 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge['class'] }}">
                                        {{ $badge['text'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex flex-wrap gap-4 text-xs text-zinc-500 dark:text-zinc-400 p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-1.5 rounded-full bg-gradient-to-r from-emerald-400 to-emerald-500 inline-block"></span>
                    <strong class="text-zinc-700 dark:text-zinc-300">En control:</strong> &lt;95% vs mes anterior
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-1.5 rounded-full bg-gradient-to-r from-amber-400 to-amber-500 inline-block"></span>
                    <strong class="text-zinc-700 dark:text-zinc-300">Normal:</strong> 95–110%
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-1.5 rounded-full bg-gradient-to-r from-rose-400 to-rose-600 inline-block"></span>
                    <strong class="text-zinc-700 dark:text-zinc-300">Excedido:</strong> &gt;110%
                </span>
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
