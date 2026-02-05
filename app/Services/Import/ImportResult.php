<?php

namespace App\Services\Import;

class ImportResult
{
    private int $imported = 0;

    private int $skippedDuplicates = 0;

    private int $failed = 0;

    private int $rulesCreated = 0;

    private array $errors = [];

    private array $importedTransactions = [];

    public function addImported(array $transaction): void
    {
        $this->imported++;
        $this->importedTransactions[] = $transaction;
    }

    public function addSkippedDuplicate(): void
    {
        $this->skippedDuplicates++;
    }

    public function addFailed(string $error, ?array $rowData = null): void
    {
        $this->failed++;
        $this->errors[] = [
            'message' => $error,
            'data' => $rowData,
        ];
    }

    public function getImportedCount(): int
    {
        return $this->imported;
    }

    public function getSkippedDuplicatesCount(): int
    {
        return $this->skippedDuplicates;
    }

    public function getFailedCount(): int
    {
        return $this->failed;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getImportedTransactions(): array
    {
        return $this->importedTransactions;
    }

    public function setRulesCreated(int $count): void
    {
        $this->rulesCreated = $count;
    }

    public function getRulesCreated(): int
    {
        return $this->rulesCreated;
    }

    public function getTotalProcessed(): int
    {
        return $this->imported + $this->skippedDuplicates + $this->failed;
    }

    public function getSummary(): array
    {
        return [
            'imported' => $this->imported,
            'skipped_duplicates' => $this->skippedDuplicates,
            'failed' => $this->failed,
            'total_processed' => $this->getTotalProcessed(),
            'errors' => $this->errors,
        ];
    }

    public function getSummaryMessage(): string
    {
        $parts = [];

        if ($this->imported > 0) {
            $parts[] = "{$this->imported} importadas";
        }

        if ($this->skippedDuplicates > 0) {
            $parts[] = "{$this->skippedDuplicates} duplicadas (omitidas)";
        }

        if ($this->failed > 0) {
            $parts[] = "{$this->failed} fallidas";
        }

        if ($this->rulesCreated > 0) {
            $parts[] = "{$this->rulesCreated} reglas creadas";
        }

        return implode(', ', $parts) ?: 'No se procesaron transacciones';
    }
}
