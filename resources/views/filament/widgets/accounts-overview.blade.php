<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            🏦 Resumen de Cuentas
        </x-slot>

        <x-slot name="description">
            Vista general del balance de todas tus cuentas
        </x-slot>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
            @foreach($this->getViewData()['accounts'] as $account)
                <div class="relative rounded-lg border border-gray-200 dark:border-gray-700 p-5 hover:border-primary-500 dark:hover:border-primary-500 transition-all hover:shadow-lg">
                    {{-- Nombre de la cuenta --}}
                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                            {{ $account['name'] }}
                        </h3>
                    </div>

                    {{-- Balance Actual --}}
                    <div class="mb-4">
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Balance Actual</p>
                        <p class="text-2xl font-bold {{ $account['current_balance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            ${{ number_format($account['current_balance'], 2) }}
                        </p>
                    </div>

                    {{-- Balance del Mes --}}
                    <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Balance del Mes</span>
                            <span class="text-sm font-bold {{ $account['month_balance'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $account['month_balance'] >= 0 ? '+' : '' }}${{ number_format($account['month_balance'], 2) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <div class="flex items-center gap-1">
                                <span class="text-green-600">📈</span>
                                <span class="text-gray-600 dark:text-gray-400">${{ number_format($account['month_income'], 2) }}</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="text-red-600">📉</span>
                                <span class="text-gray-600 dark:text-gray-400">${{ number_format($account['month_expense'], 2) }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Stats adicionales --}}
                    <div class="mb-4 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                        <span>💳 Transacciones: {{ $account['transaction_count'] }}</span>
                        <span>🏦 Inicial: ${{ number_format($account['initial_balance'], 2) }}</span>
                    </div>

                    {{-- Botón Ver Detalles --}}
                    <a href="{{ route('filament.app.resources.accounts.view', ['record' => $account['id']]) }}"
                       class="block w-full text-center px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg transition-colors">
                        👁️ Ver Detalles
                    </a>
                </div>
            @endforeach

            @if(count($this->getViewData()['accounts']) === 0)
                <div class="col-span-full text-center py-12">
                    <p class="text-gray-500 dark:text-gray-400 mb-4">
                        No tienes cuentas creadas aún
                    </p>
                    <a href="{{ route('filament.app.resources.accounts.create') }}"
                       class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-primary-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Crear tu primera cuenta
                    </a>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
