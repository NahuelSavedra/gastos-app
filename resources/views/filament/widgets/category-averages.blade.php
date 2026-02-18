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
                Comparacion de Gastos por Categoria
            </div>
        </x-slot>

        <x-slot name="description">
            {{ $monthLabel }} vs {{ $previousMonthLabel }}
        </x-slot>

        <div class="space-y-4">
            @if($insufficientData)
                <div class="text-center py-8">
                    <x-heroicon-o-chart-bar class="w-12 h-12 text-zinc-400 mx-auto mb-4" />
                    <p class="text-zinc-500 dark:text-zinc-400">
                        No hay datos del mes anterior para comparar
                    </p>
                </div>
            @elseif(count($categories) === 0)
                <div class="text-center py-8">
                    <x-heroicon-o-check-circle class="w-12 h-12 text-emerald-500 mx-auto mb-4" />
                    <p class="text-zinc-500 dark:text-zinc-400">
                        No hay gastos registrados en este periodo
                    </p>
                </div>
            @else
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-800 overflow-hidden">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-800">
                        <thead class="bg-zinc-50 dark:bg-zinc-800/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Categoria
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Mes Anterior
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Actual
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider" style="min-width: 200px;">
                                Progreso
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                                Estado
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                        @foreach($categories as $category)
                            <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <span class="text-sm font-medium text-zinc-900 dark:text-white">
                                        {{ $category['name'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                    <span class="text-sm text-zinc-600 dark:text-zinc-300">
                                        @if($category['previous'] > 0)
                                            ${{ number_format($category['previous'], 2) }}
                                        @else
                                            <span class="text-zinc-400">$0.00</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-right">
                                    <span class="text-sm font-semibold text-zinc-900 dark:text-white">
                                        ${{ number_format($category['current'], 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5">
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-zinc-500 dark:text-zinc-400">
                                                @if($category['percentage'] !== null)
                                                    {{ $category['percentage'] }}%
                                                @elseif($category['previous'] == 0)
                                                    Nueva categoria
                                                @else
                                                    Sin gasto
                                                @endif
                                            </span>
                                        </div>
                                        <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-1.5">
                                            @php
                                                $barWidth = $category['percentage'] !== null ? min($category['percentage'], 150) : 0;
                                                $colorClass = match($category['color']) {
                                                    'green' => 'bg-emerald-500',
                                                    'red' => 'bg-rose-500',
                                                    'yellow' => 'bg-amber-500',
                                                    default => 'bg-zinc-500',
                                                };
                                            @endphp
                                            <div class="{{ $colorClass }} h-1.5 rounded-full transition-all duration-300"
                                                 style="width: {{ $barWidth }}%">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 whitespace-nowrap text-center">
                                    @php
                                        $badgeConfig = match($category['status']) {
                                            'on_track' => ['text' => 'En control', 'class' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400'],
                                            'over_budget' => ['text' => 'Excedido', 'class' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-400'],
                                            'neutral' => ['text' => 'Normal', 'class' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400'],
                                            default => ['text' => 'N/A', 'class' => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium {{ $badgeConfig['class'] }}">
                                        {{ $badgeConfig['text'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                    <div class="flex items-start gap-2 text-xs text-zinc-600 dark:text-zinc-400">
                        <x-heroicon-m-information-circle class="w-4 h-4 flex-shrink-0 mt-0.5" />
                        <div class="space-y-1">
                            <p><span class="inline-block w-2.5 h-2.5 bg-emerald-500 rounded-full mr-1"></span> <strong>En control:</strong> Gastando menos del 95% vs mes anterior</p>
                            <p><span class="inline-block w-2.5 h-2.5 bg-amber-500 rounded-full mr-1"></span> <strong>Normal:</strong> Entre 95% y 110% vs mes anterior</p>
                            <p><span class="inline-block w-2.5 h-2.5 bg-rose-500 rounded-full mr-1"></span> <strong>Excedido:</strong> Mas del 110% vs mes anterior</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
