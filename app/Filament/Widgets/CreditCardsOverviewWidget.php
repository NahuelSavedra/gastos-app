<?php

namespace App\Filament\Widgets;

use App\Models\CreditCard;
use Filament\Widgets\Widget;

class CreditCardsOverviewWidget extends Widget
{
    protected static string $view = 'filament.widgets.credit-cards-overview';

    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    protected static ?string $pollingInterval = '60s';

    public function getViewData(): array
    {
        $cards = CreditCard::active()
            ->with(['installmentPurchases' => fn ($q) => $q->active()])
            ->get();

        $totalDebt = $cards->sum(fn ($card) => $card->total_debt);
        $totalMonthly = $cards->sum(fn ($card) => $card->monthly_payment);

        return [
            'cards' => $cards,
            'totalDebt' => $totalDebt,
            'totalMonthly' => $totalMonthly,
        ];
    }

    public static function canView(): bool
    {
        return CreditCard::active()->exists();
    }
}
