<?php

namespace App\Services;

use App\Models\InstallmentPurchase;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InstallmentPurchaseService
{
    public function createLinkedTransaction(InstallmentPurchase $purchase): void
    {
        $purchase->load('creditCard');

        if (! $purchase->creditCard || ! $purchase->creditCard->account_id) {
            Log::warning("InstallmentPurchase #{$purchase->id}: no linked account_id on credit card, skipping transaction creation.");

            return;
        }

        DB::transaction(function () use ($purchase) {
            $title = $purchase->title;
            if ($purchase->store) {
                $title .= " - {$purchase->store}";
            }

            $tx = Transaction::create([
                'title' => $title,
                'amount' => $purchase->total_amount,
                'date' => $purchase->first_payment_date,
                'account_id' => $purchase->creditCard->account_id,
                'category_id' => $purchase->category_id,
                'description' => "Generado desde compra en cuotas #{$purchase->id}",
            ]);

            $purchase->updateQuietly(['transaction_id' => $tx->id]);
        });
    }

    public function syncLinkedTransaction(InstallmentPurchase $purchase): void
    {
        if (! $purchase->transaction_id) {
            return;
        }

        if (! $purchase->isDirty(['title', 'store', 'total_amount', 'first_payment_date', 'category_id'])) {
            return;
        }

        $tx = Transaction::find($purchase->transaction_id);
        if (! $tx) {
            return;
        }

        $title = $purchase->title;
        if ($purchase->store) {
            $title .= " - {$purchase->store}";
        }

        $tx->update([
            'title' => $title,
            'amount' => $purchase->total_amount,
            'date' => $purchase->first_payment_date,
            'category_id' => $purchase->category_id,
        ]);
    }

    public function deleteLinkedTransaction(InstallmentPurchase $purchase): void
    {
        if (! $purchase->transaction_id) {
            return;
        }

        Transaction::find($purchase->transaction_id)?->delete();
    }
}
