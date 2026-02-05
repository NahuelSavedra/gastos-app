<?php

namespace App\Services\Import\Parsers;

class MercadoPagoCsvParser extends AbstractCsvParser
{
    public function getSource(): string
    {
        return 'mercadopago';
    }

    public function getName(): string
    {
        return 'MercadoPago';
    }

    public function getDelimiter(): string
    {
        return ';';
    }

    protected function getColumnMapping(): array
    {
        return [
            'RELEASE_DATE' => 'date',
            'TRANSACTION_TYPE' => 'transaction_type',
            'REFERENCE_ID' => 'reference_id',
            'TRANSACTION_NET_AMOUNT' => 'amount',
            'PARTIAL_BALANCE' => 'balance',
        ];
    }

    public function parse(string $filePath): iterable
    {
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new \RuntimeException("Cannot open file: {$filePath}");
        }

        try {
            // Skip first 3 lines (balance summary + empty line)
            // Line 1: INITIAL_BALANCE;CREDITS;DEBITS;FINAL_BALANCE
            // Line 2: values
            // Line 3: empty
            fgetcsv($handle, 0, $this->getDelimiter());
            fgetcsv($handle, 0, $this->getDelimiter());
            fgetcsv($handle, 0, $this->getDelimiter());

            // Line 4: actual headers
            $this->headers = fgetcsv($handle, 0, $this->getDelimiter());

            if ($this->headers === false) {
                throw new \RuntimeException('Cannot read CSV headers');
            }

            // Clean header names
            $this->headers = array_map(function ($header) {
                $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);

                return trim($header);
            }, $this->headers);

            // Read data rows
            while (($row = fgetcsv($handle, 0, $this->getDelimiter())) !== false) {
                if (count($row) === 1 && empty($row[0])) {
                    continue;
                }

                $data = @array_combine($this->headers, $row);

                if ($data === false) {
                    continue;
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

    public function normalizeRow(array $row): ?array
    {
        $referenceId = $row['REFERENCE_ID'] ?? null;
        $amount = $row['TRANSACTION_NET_AMOUNT'] ?? null;
        $date = $row['RELEASE_DATE'] ?? null;
        $transactionType = $row['TRANSACTION_TYPE'] ?? '';

        if (empty($referenceId) || $amount === null || empty($date)) {
            return null;
        }

        $parsedAmount = $this->parseAmount($amount);

        if ($parsedAmount == 0) {
            return null;
        }

        $parsedDate = $this->parseDate($date);

        if ($parsedDate === null) {
            return null;
        }

        // Parse transaction type to extract category and description
        $parsed = $this->parseTransactionType($transactionType);

        return [
            'source_id' => $referenceId,
            'reference_id' => $this->getReferencePrefix().$referenceId,
            'amount' => abs($parsedAmount),
            'is_expense' => $parsedAmount < 0,
            'date' => $parsedDate,
            'title' => $parsed['title'],
            'description' => $transactionType,
            // Extra fields for category matching
            'transaction_type' => $parsed['type'],
            'recipient' => $parsed['recipient'],
        ];
    }

    /**
     * Parse transaction type string to extract type and recipient
     * Examples:
     * - "Transferencia enviada Ariana Montenegro" -> type: "Transferencia enviada", recipient: "Ariana Montenegro"
     * - "Pago Farmacia Farmaval" -> type: "Pago", recipient: "Farmacia Farmaval"
     * - "Rendimientos" -> type: "Rendimientos", recipient: null
     */
    private function parseTransactionType(string $transactionType): array
    {
        $transactionType = trim($transactionType);

        // Define patterns and their category mappings
        $patterns = [
            '/^(Transferencia enviada)\s+(.+)$/i' => ['type' => 'Transferencia enviada', 'title_prefix' => 'Transferencia a'],
            '/^(Transferencia recibida)\s+(.+)$/i' => ['type' => 'Transferencia recibida', 'title_prefix' => 'Transferencia de'],
            '/^(Ingreso de dinero)\s+(.+)$/i' => ['type' => 'Ingreso de dinero', 'title_prefix' => 'Ingreso desde'],
            '/^(Pago de servicios cancelado)\s+(.+)$/i' => ['type' => 'Pago de servicios cancelado', 'title_prefix' => 'Devolución'],
            '/^(Pago de servicios)\s+(.+)$/i' => ['type' => 'Pago de servicios', 'title_prefix' => ''],
            '/^(Pago con QR)\s+(.+)$/i' => ['type' => 'Pago con QR', 'title_prefix' => ''],
            '/^(Pago)\s+(.+)$/i' => ['type' => 'Pago', 'title_prefix' => ''],
            '/^(Rendimientos)\s*(.*)$/i' => ['type' => 'Rendimientos', 'title_prefix' => 'Rendimientos'],
        ];

        foreach ($patterns as $pattern => $config) {
            if (preg_match($pattern, $transactionType, $matches)) {
                $recipient = isset($matches[2]) ? trim($matches[2]) : null;
                $title = $config['title_prefix'];

                if ($recipient) {
                    $title = $title ? "{$title} {$recipient}" : $recipient;
                }

                return [
                    'type' => $config['type'],
                    'recipient' => $recipient,
                    'title' => $title ?: $transactionType,
                ];
            }
        }

        // Default: use the full string as both type and title
        return [
            'type' => $transactionType,
            'recipient' => null,
            'title' => $transactionType,
        ];
    }
}
