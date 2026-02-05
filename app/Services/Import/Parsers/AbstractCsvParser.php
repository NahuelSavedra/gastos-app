<?php

namespace App\Services\Import\Parsers;

abstract class AbstractCsvParser implements CsvParserInterface
{
    protected array $headers = [];

    public function getDelimiter(): string
    {
        return ',';
    }

    public function getReferencePrefix(): string
    {
        return 'import_'.$this->getSource().'_';
    }

    public function parse(string $filePath): iterable
    {
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new \RuntimeException("Cannot open file: {$filePath}");
        }

        try {
            // Read header row
            $this->headers = fgetcsv($handle, 0, $this->getDelimiter());

            if ($this->headers === false) {
                throw new \RuntimeException('Cannot read CSV headers');
            }

            // Clean header names (remove BOM, trim whitespace)
            $this->headers = array_map(function ($header) {
                $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);

                return trim($header);
            }, $this->headers);

            // Read data rows
            while (($row = fgetcsv($handle, 0, $this->getDelimiter())) !== false) {
                // Skip empty rows
                if (count($row) === 1 && empty($row[0])) {
                    continue;
                }

                // Combine headers with values
                $data = array_combine($this->headers, $row);

                if ($data === false) {
                    continue; // Skip malformed rows
                }

                $normalized = $this->normalizeRow($data);

                if ($normalized !== null) {
                    yield $normalized;
                }
            }
        } finally {
            fclose($handle);
        }
    }

    /**
     * Get column mapping: CSV column name => internal field name
     */
    abstract protected function getColumnMapping(): array;

    /**
     * Parse a date string into Y-m-d format
     */
    protected function parseDate(string $dateString): ?string
    {
        $formats = [
            'Y-m-d\TH:i:sP',     // ISO 8601
            'Y-m-d H:i:s',
            'Y-m-d',
            'd/m/Y H:i:s',
            'd/m/Y',
            'd-m-Y',
        ];

        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $dateString);
            if ($date !== false) {
                return $date->format('Y-m-d');
            }
        }

        return null;
    }

    /**
     * Parse amount, handling different decimal separators
     */
    protected function parseAmount(string $amountString): float
    {
        // Remove currency symbols and whitespace
        $clean = preg_replace('/[^0-9,.\-]/', '', $amountString);

        // Handle comma as decimal separator (European format)
        if (preg_match('/,\d{2}$/', $clean)) {
            $clean = str_replace('.', '', $clean);
            $clean = str_replace(',', '.', $clean);
        } else {
            $clean = str_replace(',', '', $clean);
        }

        return (float) $clean;
    }
}
