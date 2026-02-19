<?php

namespace App\Observers;

use App\Models\InstallmentPurchase;

class InstallmentPurchaseObserver
{
    public function created(InstallmentPurchase $purchase): void
    {
        $this->clearCaches($purchase);
    }

    public function updated(InstallmentPurchase $purchase): void
    {
        $this->clearCaches($purchase);

        // Si cambió de tarjeta, limpiar la tarjeta anterior también
        if ($purchase->isDirty('credit_card_id')) {
            $oldCardId = $purchase->getOriginal('credit_card_id');
            if ($oldCardId) {
                \Illuminate\Support\Facades\Cache::forget("credit_card_debt_{$oldCardId}");
                \Illuminate\Support\Facades\Cache::forget("credit_card_monthly_{$oldCardId}");
            }
        }
    }

    public function deleted(InstallmentPurchase $purchase): void
    {
        $this->clearCaches($purchase);
    }

    protected function clearCaches(InstallmentPurchase $purchase): void
    {
        if ($purchase->credit_card_id) {
            \Illuminate\Support\Facades\Cache::forget("credit_card_debt_{$purchase->credit_card_id}");
            \Illuminate\Support\Facades\Cache::forget("credit_card_monthly_{$purchase->credit_card_id}");
        }
    }
}
