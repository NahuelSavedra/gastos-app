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
            📊 Comparación de Gastos por Categoría
        </x-slot>

        <x-slot name="description">
            {{ $monthLabel }} vs {{ $previousMonthLabel }}
        </x-slot>

        <div class="space-y-4">
            @if($insufficientData)
                <div class="text-center py-8">
                    <div class="text-4xl mb-4">📉</div>
                    <p class="text-gray-500 dark:text-gray-400">
                        No hay datos del mes anterior para comparar
                    </p>
                </div>
            @elseif(count($categories) === 0)
                <div class="text-center py-8">
                    <div class="text-4xl mb-4">✅</div>
                    <p class="text-gray-500 dark:text-gray-400">
                        No hay gastos registrados en este período
                    </p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead>
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Categoría
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Mes Anterior
                            </th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Actual
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider" style="min-width: 200px;">
                                Progreso
                            </th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Estado
                            </th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($categories as $category)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $category['name'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm text-gray-600 dark:text-gray-300">
                                        @if($category['previous'] > 0)
                                            ${{ number_format($category['previous'], 2) }}
                                        @else
                                            <span class="text-gray-400">$0.00</span>
                                        @endif
                                    </span>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-right">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                        ${{ number_format($category['current'], 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-gray-500 dark:text-gray-400">
                                                @if($category['percentage'] !== null)
                                                    {{ $category['percentage'] }}%
                                                @elseif($category['previous'] == 0)
                                                    Nueva categoría
                                                @else
                                                    Sin gasto
                                                @endif
                                            </span>
                                        </div>
                                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                                            @php
                                                $barWidth = $category['percentage'] !== null ? min($category['percentage'], 150) : 0;
                                                $colorClass = match($category['color']) {
                                                    'green' => 'bg-green-500',
                                                    'red' => 'bg-red-500',
                                                    'yellow' => 'bg-yellow-500',
                                                    default => 'bg-gray-500',
                                                };
                                            @endphp
                                            <div class="{{ $colorClass }} h-2.5 rounded-full transition-all duration-300"
                                                 style="width: {{ $barWidth }}%">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap text-center">
                                    @php
                                        $badgeConfig = match($category['status']) {
                                            'on_track' => ['text' => 'En control', 'class' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'],
                                            'over_budget' => ['text' => 'Excedido', 'class' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'],
                                            'neutral' => ['text' => 'Normal', 'class' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'],
                                            default => ['text' => 'N/A', 'class' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300'],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeConfig['class'] }}">
                                        {{ $badgeConfig['text'] }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                    <div class="flex items-start gap-2 text-xs text-gray-600 dark:text-gray-400">
                        <div class="flex-shrink-0 mt-0.5">ℹ️</div>
                        <div class="space-y-1">
                            <p><span class="inline-block w-3 h-3 bg-green-500 rounded-full mr-1"></span> <strong>En control:</strong> Gastando menos del 95% vs mes anterior</p>
                            <p><span class="inline-block w-3 h-3 bg-yellow-500 rounded-full mr-1"></span> <strong>Normal:</strong> Entre 95% y 110% vs mes anterior</p>
                            <p><span class="inline-block w-3 h-3 bg-red-500 rounded-full mr-1"></span> <strong>Excedido:</strong> Más del 110% vs mes anterior</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
