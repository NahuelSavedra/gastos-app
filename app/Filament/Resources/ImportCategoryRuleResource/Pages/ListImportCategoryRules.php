<?php

namespace App\Filament\Resources\ImportCategoryRuleResource\Pages;

use App\Filament\Resources\ImportCategoryRuleResource;
use App\Models\Category;
use App\Models\ImportCategoryRule;
use App\Models\Transaction;
use App\Services\Import\CategoryMatcher\CategoryMatcherService;
use Filament\Actions;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListImportCategoryRules extends ListRecords
{
    protected static string $resource = ImportCategoryRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            $this->getApplyRulesAction(),
            $this->getSuggestRulesAction(),
            Actions\CreateAction::make(),
        ];
    }

    protected function getApplyRulesAction(): Actions\Action
    {
        return Actions\Action::make('apply_rules')
            ->label('Aplicar Reglas')
            ->icon('heroicon-o-arrow-path')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Aplicar reglas a transacciones existentes')
            ->modalDescription('Esto recategorizará las transacciones importadas según las reglas actuales. Solo se actualizarán transacciones que coincidan con alguna regla.')
            ->action(function (): void {
                $rules = ImportCategoryRule::active()
                    ->ordered()
                    ->with('category')
                    ->get();

                if ($rules->isEmpty()) {
                    Notification::make()
                        ->title('Sin reglas')
                        ->body('No hay reglas activas para aplicar.')
                        ->warning()
                        ->send();

                    return;
                }

                // Get imported transactions
                $transactions = Transaction::with('category')
                    ->where('reference_id', 'like', 'import_%')
                    ->get();

                $updated = 0;

                foreach ($transactions as $transaction) {
                    // Build data array for matching
                    $data = [
                        'title' => $transaction->title,
                        'description' => $transaction->description,
                        'recipient' => $this->extractRecipient($transaction->title),
                        'transaction_type' => $this->extractTransactionType($transaction->title),
                    ];

                    foreach ($rules as $rule) {
                        if ($rule->matches($data)) {
                            $expectedType = $transaction->category?->type;

                            // Only update if category type matches
                            if ($rule->category->type === $expectedType) {
                                if ($transaction->category_id !== $rule->category_id) {
                                    $transaction->update(['category_id' => $rule->category_id]);
                                    $updated++;
                                }
                                break;
                            }
                        }
                    }
                }

                Notification::make()
                    ->title('Reglas aplicadas')
                    ->body("Se actualizaron {$updated} transacciones.")
                    ->success()
                    ->send();
            });
    }

    protected function extractTransactionType(string $title): ?string
    {
        // Pattern: "Tipo: Destinatario" - extract the type part
        if (preg_match('/^([^:]+):/u', $title, $matches)) {
            return trim($matches[1]);
        }

        return $title;
    }

    protected function getSuggestRulesAction(): Actions\Action
    {
        return Actions\Action::make('suggest_rules')
            ->label('Sugerir Reglas')
            ->icon('heroicon-o-light-bulb')
            ->color('warning')
            ->modalHeading('Crear reglas desde transacciones importadas')
            ->modalDescription('Selecciona los destinatarios para los que quieres crear reglas de categorización automática.')
            ->modalWidth('4xl')
            ->form(function () {
                $suggestions = $this->getRecipientSuggestions();

                if (empty($suggestions)) {
                    return [
                        \Filament\Forms\Components\Placeholder::make('empty')
                            ->label('')
                            ->content('No hay destinatarios sin regla de categorización. ¡Todo está configurado!'),
                    ];
                }

                return [
                    Repeater::make('rules')
                        ->label('Reglas sugeridas')
                        ->schema([
                            TextInput::make('recipient')
                                ->label('Destinatario')
                                ->disabled()
                                ->dehydrated(),

                            TextInput::make('sample_title')
                                ->label('Ejemplo de transacción')
                                ->disabled()
                                ->dehydrated(false),

                            TextInput::make('count')
                                ->label('Transacciones')
                                ->disabled()
                                ->dehydrated(false),

                            Select::make('category_id')
                                ->label('Categoría')
                                ->options(
                                    Category::orderBy('type')
                                        ->orderBy('name')
                                        ->get()
                                        ->mapWithKeys(fn ($cat) => [
                                            $cat->id => ($cat->type === 'expense' ? '📉 ' : '📈 ') . $cat->name,
                                        ])
                                )
                                ->searchable()
                                ->placeholder('Seleccionar categoría...'),

                            Toggle::make('create_rule')
                                ->label('Crear regla')
                                ->default(false)
                                ->inline(false),
                        ])
                        ->columns(5)
                        ->default($suggestions)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false),
                ];
            })
            ->action(function (array $data): void {
                $created = 0;

                foreach ($data['rules'] ?? [] as $rule) {
                    if (! ($rule['create_rule'] ?? false)) {
                        continue;
                    }

                    if (empty($rule['category_id'])) {
                        continue;
                    }

                    $recipient = $rule['recipient'];

                    // Check if rule already exists
                    $exists = ImportCategoryRule::where('field', 'recipient')
                        ->where('operator', 'contains')
                        ->whereRaw('LOWER(value) = ?', [strtolower($recipient)])
                        ->exists();

                    if ($exists) {
                        continue;
                    }

                    ImportCategoryRule::create([
                        'name' => "Regla: {$recipient}",
                        'source' => null, // Apply to all sources
                        'field' => 'recipient',
                        'operator' => 'contains',
                        'value' => $recipient,
                        'category_id' => $rule['category_id'],
                        'priority' => 10,
                        'is_active' => true,
                    ]);

                    $created++;
                }

                if ($created > 0) {
                    Notification::make()
                        ->title('Reglas creadas')
                        ->body("Se crearon {$created} reglas de categorización.")
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                        ->title('Sin cambios')
                        ->body('No se creó ninguna regla.')
                        ->info()
                        ->send();
                }
            });
    }

    protected function getRecipientSuggestions(): array
    {
        // Get transactions from imports (last 3 months)
        $transactions = Transaction::with('category')
            ->where('reference_id', 'like', 'import_%')
            ->where('date', '>=', now()->subMonths(3))
            ->get();

        // Extract unique recipients from titles
        $recipientCounts = [];
        $recipientSamples = [];

        foreach ($transactions as $transaction) {
            $recipient = $this->extractRecipient($transaction->title);

            if (empty($recipient)) {
                continue;
            }

            $recipientKey = strtolower($recipient);

            if (! isset($recipientCounts[$recipientKey])) {
                $recipientCounts[$recipientKey] = 0;
                $recipientSamples[$recipientKey] = [
                    'recipient' => $recipient,
                    'sample' => $transaction->title,
                ];
            }

            $recipientCounts[$recipientKey]++;
        }

        // Filter out recipients that already have rules
        $existingRules = ImportCategoryRule::where('field', 'recipient')
            ->pluck('value')
            ->map(fn ($v) => strtolower($v))
            ->toArray();

        $suggestions = [];

        foreach ($recipientCounts as $key => $count) {
            // Skip if already has a rule
            $hasRule = false;
            foreach ($existingRules as $ruleValue) {
                if (str_contains($key, strtolower($ruleValue)) || str_contains(strtolower($ruleValue), $key)) {
                    $hasRule = true;
                    break;
                }
            }

            if ($hasRule) {
                continue;
            }

            // Only suggest if appears at least once
            if ($count >= 1) {
                $suggestions[] = [
                    'recipient' => $recipientSamples[$key]['recipient'],
                    'sample_title' => $recipientSamples[$key]['sample'],
                    'count' => $count,
                    'category_id' => null,
                    'create_rule' => false,
                ];
            }
        }

        // Sort by count descending
        usort($suggestions, fn ($a, $b) => $b['count'] <=> $a['count']);

        // Limit to 20 suggestions
        return array_slice($suggestions, 0, 20);
    }

    protected function extractRecipient(string $title): ?string
    {
        // Pattern: "Tipo: Destinatario"
        if (preg_match('/^[^:]+:\s*(.+)$/u', $title, $matches)) {
            $recipient = trim($matches[1]);

            // Skip if it's a generic term
            $skipTerms = ['D.A. AL VTO', 'FIMA PREMIUM', 'Diciembre', 'Enero', 'Febrero', 'Marzo'];
            foreach ($skipTerms as $term) {
                if (stripos($recipient, $term) !== false) {
                    return null;
                }
            }

            return $recipient;
        }

        return null;
    }
}
