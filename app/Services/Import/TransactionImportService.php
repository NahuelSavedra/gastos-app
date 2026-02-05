<?php

namespace App\Services\Import;

use App\Models\Category;
use App\Models\ImportCategoryRule;
use App\Models\Transaction;
use App\Services\Import\CategoryMatcher\CategoryMatcherService;
use App\Services\Import\Parsers\CsvParserInterface;
use App\Services\Import\Parsers\GaliciaPdfParser;
use App\Services\Import\Parsers\MercadoPagoCsvParser;
use Illuminate\Support\Facades\DB;

class TransactionImportService
{
    private array $parsers = [];

    private array $recipientTracker = [];

    public function __construct(
        private CategoryMatcherService $categoryMatcher
    ) {
        $this->registerDefaultParsers();
    }

    private function registerDefaultParsers(): void
    {
        $this->registerParser(new MercadoPagoCsvParser);
        $this->registerParser(new GaliciaPdfParser);
    }

    public function registerParser(CsvParserInterface $parser): void
    {
        $this->parsers[$parser->getSource()] = $parser;
    }

    public function getParser(string $source): ?CsvParserInterface
    {
        return $this->parsers[$source] ?? null;
    }

    public function getAvailableParsers(): array
    {
        $result = [];
        foreach ($this->parsers as $source => $parser) {
            $result[$source] = $parser->getName();
        }

        return $result;
    }

    /**
     * Import transactions from a CSV file
     *
     * @param  string  $filePath  Path to the CSV file
     * @param  string  $parserSource  Parser identifier (e.g., 'mercadopago')
     * @param  int  $accountId  Target account ID
     * @param  int|null  $defaultCategoryId  Default category for unmatched transactions
     * @param  bool  $preview  If true, don't actually create transactions
     * @param  bool  $autoCreateCategories  If true, create categories from transaction_type
     */
    public function import(
        string $filePath,
        string $parserSource,
        int $accountId,
        ?int $defaultCategoryId = null,
        bool $preview = false,
        bool $autoCreateCategories = false
    ): ImportResult {
        $parser = $this->getParser($parserSource);

        if (! $parser) {
            $result = new ImportResult;
            $result->addFailed("Parser not found: {$parserSource}");

            return $result;
        }

        if (! file_exists($filePath)) {
            $result = new ImportResult;
            $result->addFailed("Archivo no encontrado: {$filePath}");

            return $result;
        }

        $result = new ImportResult;

        // Reset recipient tracker
        $this->recipientTracker = [];

        // Get default categories for fallback
        $defaultExpenseCategory = $defaultCategoryId
            ? Category::find($defaultCategoryId)
            : null;

        $defaultIncomeCategory = null;

        DB::beginTransaction();

        try {
            foreach ($parser->parse($filePath) as $normalizedRow) {
                $this->processRow(
                    $normalizedRow,
                    $parser->getSource(),
                    $accountId,
                    $defaultExpenseCategory,
                    $defaultIncomeCategory,
                    $result,
                    $preview,
                    $autoCreateCategories
                );
            }

            // Create rules for frequent recipients (2+ occurrences)
            if (! $preview && $autoCreateCategories) {
                $rulesCreated = $this->createRulesForFrequentRecipients($parserSource);
                $result->setRulesCreated($rulesCreated);
            }

            if ($preview) {
                DB::rollBack();
            } else {
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $result->addFailed('Import error: '.$e->getMessage());
        }

        return $result;
    }

    private function processRow(
        array $row,
        string $source,
        int $accountId,
        ?Category $defaultExpenseCategory,
        ?Category $defaultIncomeCategory,
        ImportResult $result,
        bool $preview,
        bool $autoCreateCategories = false
    ): void {
        $referenceId = $row['reference_id'] ?? null;

        // Check for duplicates
        if ($referenceId && Transaction::where('reference_id', $referenceId)->exists()) {
            $result->addSkippedDuplicate();

            return;
        }

        $isExpense = $row['is_expense'] ?? true;

        // Try to match category via rules
        $category = $this->categoryMatcher->findCategory($row, $source, $isExpense);

        // Try to auto-create category from transaction_type
        if (! $category && $autoCreateCategories) {
            $category = $this->findOrCreateCategoryFromRow($row, $isExpense);
        }

        // Fallback to default category
        if (! $category) {
            $category = $isExpense ? $defaultExpenseCategory : $defaultIncomeCategory;
        }

        if (! $category) {
            $result->addFailed('No category found and no default available', $row);

            return;
        }

        $transactionData = [
            'title' => $row['title'] ?? 'Imported',
            'description' => $row['description'] ?? null,
            'amount' => $row['amount'],
            'date' => $row['date'],
            'account_id' => $accountId,
            'category_id' => $category->id,
            'reference_id' => $referenceId,
        ];

        // Track recipient for rule creation
        $recipient = $row['recipient'] ?? null;
        if ($recipient && $autoCreateCategories) {
            $this->trackRecipient($recipient, $category->id, $isExpense);
        }

        if (! $preview) {
            try {
                Transaction::create($transactionData);
            } catch (\Exception $e) {
                $result->addFailed($e->getMessage(), $row);

                return;
            }
        }

        $result->addImported($transactionData);
    }

    private function findOrCreateCategoryFromRow(array $row, bool $isExpense): ?Category
    {
        // Use transaction_type as category name
        $categoryName = $row['transaction_type'] ?? null;

        if (empty($categoryName)) {
            return null;
        }

        $categoryName = trim($categoryName);
        $type = $isExpense ? 'expense' : 'income';

        // Find existing or create new
        return Category::firstOrCreate(
            ['name' => $categoryName, 'type' => $type],
            ['name' => $categoryName, 'type' => $type]
        );
    }

    private function trackRecipient(string $recipient, int $categoryId, bool $isExpense): void
    {
        $key = $recipient.'_'.($isExpense ? 'expense' : 'income');

        if (! isset($this->recipientTracker[$key])) {
            $this->recipientTracker[$key] = [
                'recipient' => $recipient,
                'category_id' => $categoryId,
                'is_expense' => $isExpense,
                'count' => 0,
            ];
        }

        $this->recipientTracker[$key]['count']++;
    }

    private function createRulesForFrequentRecipients(string $source, int $minOccurrences = 2): int
    {
        $rulesCreated = 0;

        foreach ($this->recipientTracker as $data) {
            if ($data['count'] < $minOccurrences) {
                continue;
            }

            // Check if rule already exists
            $exists = ImportCategoryRule::where('source', $source)
                ->where('field', 'recipient')
                ->where('operator', 'equals')
                ->where('value', $data['recipient'])
                ->exists();

            if ($exists) {
                continue;
            }

            ImportCategoryRule::create([
                'name' => $data['recipient'],
                'source' => $source,
                'field' => 'recipient',
                'operator' => 'equals',
                'value' => $data['recipient'],
                'category_id' => $data['category_id'],
                'priority' => 10,
                'is_active' => true,
            ]);

            $rulesCreated++;
        }

        return $rulesCreated;
    }
}
