<?php

namespace App\Filament\Resources\CreditCardResource\Pages;

use App\Filament\Resources\CreditCardResource;
use App\Models\InstallmentPurchase;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewCreditCard extends ViewRecord
{
    protected static string $resource = CreditCardResource::class;

    protected static string $view = 'filament.resources.credit-card-resource.pages.view-credit-card';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Editar Tarjeta'),
            Actions\Action::make('new_purchase')
                ->label('Nueva Compra en Cuotas')
                ->icon('heroicon-o-plus-circle')
                ->color('success')
                ->url(fn () => route('filament.app.resources.installment-purchases.create', [
                    'credit_card_id' => $this->record->id,
                ])),
        ];
    }

    public function getViewData(): array
    {
        $card = $this->record;

        $activePurchases = $card->installmentPurchases()
            ->with('category')
            ->active()
            ->orderBy('first_payment_date')
            ->get();

        $completedPurchases = $card->installmentPurchases()
            ->with('category')
            ->whereColumn('paid_installments', '>=', 'installments_count')
            ->orderByDesc('updated_at')
            ->get();

        // Cuotas que vencen este mes
        $thisMonthPurchases = $card->installmentPurchases()
            ->with('category')
            ->active()
            ->get()
            ->filter(function ($purchase) {
                $nextDate = $purchase->next_payment_date;
                return $nextDate && $nextDate->month === now()->month && $nextDate->year === now()->year;
            });

        // Compras que terminan pronto (último mes de cuotas)
        $completingSoon = $card->installmentPurchases()
            ->with('category')
            ->active()
            ->get()
            ->filter(function ($purchase) {
                return $purchase->remaining_installments === 1;
            });

        return [
            'card' => $card,
            'activePurchases' => $activePurchases,
            'completedPurchases' => $completedPurchases,
            'thisMonthPurchases' => $thisMonthPurchases,
            'completingSoon' => $completingSoon,
            'totalDebt' => $card->total_debt,
            'monthlyPayment' => $card->monthly_payment,
            'availableCredit' => $card->available_credit,
        ];
    }
}
