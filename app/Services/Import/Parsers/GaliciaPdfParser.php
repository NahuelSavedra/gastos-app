<?php

namespace App\Services\Import\Parsers;

use Smalot\PdfParser\Parser;

class GaliciaPdfParser implements CsvParserInterface
{
    public function getSource(): string
    {
        return 'galicia';
    }

    public function getName(): string
    {
        return 'Banco Galicia (PDF)';
    }

    public function getDelimiter(): string
    {
        return '';
    }

    public function getReferencePrefix(): string
    {
        return 'import_galicia_';
    }

    public function parse(string $filePath): iterable
    {
        $text = $this->extractText($filePath);

        if (empty($text)) {
            return;
        }

        // Split into lines
        $lines = explode("\n", $text);
        $totalLines = count($lines);

        $i = 0;
        $transactionIndex = 0; // To make reference IDs unique when same date/type/amount

        while ($i < $totalLines) {
            $line = $lines[$i];
            $trimmedLine = trim($line);

            // Check if line starts with a date (DD/MM/YY)
            if (preg_match('/^(\d{2}\/\d{2}\/\d{2})\s+(.+)$/', $trimmedLine, $matches)) {
                $date = $matches[1];
                $firstLineContent = $matches[2];

                // Extract amount from this first line - look for the pattern
                $amount = null;
                $isExpense = false;

                // Check for negative amount (débito) - format: -37.900,00
                if (preg_match('/-(\d{1,3}(?:\.\d{3})*,\d{2})/', $line, $amountMatch)) {
                    $amount = $this->parseAmount($amountMatch[0]);
                    $isExpense = true;
                }
                // Check for positive amount (crédito) - appears before débito column
                elseif (preg_match('/\s+(\d{1,3}(?:\.\d{3})*,\d{2})\s+(?:-|\d|$)/', $line, $amountMatch)) {
                    $amount = $this->parseAmount($amountMatch[1]);
                    $isExpense = false;
                }

                // Collect description lines
                $transactionLines = [$firstLineContent];
                $i++;

                // Collect continuation lines (indented lines with more details)
                while ($i < $totalLines) {
                    $nextLine = $lines[$i];
                    $trimmedNext = trim($nextLine);

                    // Stop if we hit another date line
                    if (preg_match('/^\d{2}\/\d{2}\/\d{2}\s/', $trimmedNext)) {
                        break;
                    }

                    // Stop if we hit page markers or footers
                    if ($this->isSkipLine($trimmedNext)) {
                        $i++;

                        continue;
                    }

                    // Skip empty lines
                    if (empty($trimmedNext)) {
                        $i++;

                        continue;
                    }

                    // This is a continuation line (details)
                    // Clean from any amounts or saldo values
                    $cleanLine = preg_replace('/-?[\d.]+,\d{2}/', '', $trimmedNext);
                    $cleanLine = trim($cleanLine);
                    if (! empty($cleanLine) && strlen($cleanLine) > 2) {
                        $transactionLines[] = $cleanLine;
                    }

                    $i++;
                }

                // Skip if no valid amount found
                if ($amount === null || $amount == 0) {
                    continue;
                }

                // Parse transaction details
                $parsed = $this->parseTransactionDetails($transactionLines);

                // Generate unique reference using transaction index to handle duplicates
                $transactionIndex++;
                $parsedDate = $this->parseDate($date);
                $uniqueKey = $parsedDate.'_'.$transactionIndex.'_'.$parsed['type'].'_'.$amount.'_'.($isExpense ? 'D' : 'C');
                $referenceId = $this->getReferencePrefix().md5($uniqueKey);

                yield [
                    'source_id' => md5($uniqueKey),
                    'reference_id' => $referenceId,
                    'amount' => abs($amount),
                    'is_expense' => $isExpense,
                    'date' => $parsedDate,
                    'title' => $parsed['title'],
                    'description' => $parsed['description'],
                    'transaction_type' => $parsed['type'],
                    'recipient' => $parsed['recipient'],
                ];
            } else {
                $i++;
            }
        }
    }

    private function extractText(string $filePath): string
    {
        // Try pdftotext first with -table mode (better for structured PDFs)
        $text = $this->extractWithPdftotext($filePath);

        if (! empty($text)) {
            return $text;
        }

        // Fallback to smalot/pdfparser
        return $this->extractWithPdfParser($filePath);
    }

    private function extractWithPdftotext(string $filePath): string
    {
        // On Windows, try common pdftotext locations
        $pdftotextPaths = [];

        $pdftotextExe = null;

        if (PHP_OS_FAMILY === 'Windows') {
            // Check specific Windows paths for pdftotext
            $pdftotextPaths = [
                'C:\\Program Files\\Git\\mingw64\\bin\\pdftotext.exe',
                'C:\\Program Files\\poppler\\bin\\pdftotext.exe',
                'C:\\Program Files (x86)\\poppler\\bin\\pdftotext.exe',
                'C:\\poppler\\bin\\pdftotext.exe',
                'C:\\tools\\poppler\\bin\\pdftotext.exe',
            ];

            foreach ($pdftotextPaths as $path) {
                if (file_exists($path)) {
                    $pdftotextExe = $path;
                    break;
                }
            }
        } else {
            // On Linux/Mac, just use pdftotext from PATH
            $pdftotextExe = 'pdftotext';
        }

        if ($pdftotextExe === null) {
            return '';
        }

        // Use -table mode for better column extraction
        if (PHP_OS_FAMILY === 'Windows') {
            $command = sprintf('"%s" -table "%s" -', $pdftotextExe, $filePath);
        } else {
            $command = sprintf('%s -table %s -', escapeshellarg($pdftotextExe), escapeshellarg($filePath));
        }

        $output = [];
        $returnCode = 0;
        exec($command, $output, $returnCode);

        return implode("\n", $output);
    }

    private function extractWithPdfParser(string $filePath): string
    {
        try {
            $parser = new Parser;
            $pdf = $parser->parseFile($filePath);

            return $pdf->getText();
        } catch (\Exception $e) {
            return '';
        }
    }

    private function isSkipLine(string $line): bool
    {
        $skipPatterns = [
            'Resumen de Caja',
            'Página',
            'P�gina',
            'Los depósitos',
            'Los dep',
            'Canales de atención',
            'Fecha Descripci',
            'Fecha     Descripci',
            'CUIL',
            'IVA:',
            'Disponés de',
            'Dispon',
            'El monto de IVA',
            'Datos de la cuenta',
            'Tipo de cuenta',
            'Número de cuenta',
            'N�mero de cuenta',
            'CBU',
            'Período',
            'Per�odo',
            'Saldos',
            'Cantidad de cotitulares',
            'Galicia',
            'Movimientos',
            'Origen',
            'Crédito',
            'Cr�dito',
            'Débito',
            'D�bito',
            'Total',
            'CARLOS NAHUEL',
            'Caja de Ahorro',
            '0070244',
            'Josefina',
            'Banco de Galicia',
            'bancogalicia.com',
            'Whatsapp',
            'Fonobanco',
            'bcra.gob.ar',
            'puede solicitar',
            'Usted puede',
            'Los totales',
            'Al completa',
        ];

        foreach ($skipPatterns as $pattern) {
            if (str_contains($line, $pattern)) {
                return true;
            }
        }

        // Skip lines that are just long numbers (like document IDs)
        if (preg_match('/^\d{14,}[A-Z]?$/', $line)) {
            return true;
        }

        // Skip lines that are only numbers, dots and $ (likely just saldo values)
        if (preg_match('/^[\d.,\s\$\-]+$/', $line) && strlen($line) < 20) {
            return true;
        }

        return false;
    }

    private function parseTransactionDetails(array $lines): array
    {
        $firstLine = $lines[0] ?? '';

        // Clean first line from amounts and codes
        $cleanFirstLine = preg_replace('/-?[\d.]+,\d{2}/', '', $firstLine);
        $cleanFirstLine = preg_replace('/\s+\d{4}\s*$/', '', $cleanFirstLine); // Remove origin code like "0431"
        $cleanFirstLine = trim($cleanFirstLine);

        // Extract transaction type
        $type = $this->extractTransactionType($cleanFirstLine);

        // Extract recipient/merchant from all lines
        $recipient = $this->extractRecipient($lines, $type);

        // Build title
        $title = $this->buildTitle($type, $recipient);

        // Build description from all lines
        $descriptionLines = array_map(function ($line) {
            $clean = preg_replace('/-?[\d.]+,\d{2}/', '', $line);
            $clean = preg_replace('/\s+\d{4}\s*$/', '', $clean);

            return trim($clean);
        }, $lines);
        $description = implode(' | ', array_filter($descriptionLines));

        return [
            'type' => $type,
            'recipient' => $recipient,
            'title' => $title,
            'description' => $description,
        ];
    }

    private function extractTransactionType(string $line): string
    {
        $types = [
            'COMPRA DEBITO' => 'Compra débito',
            'TRANSFERENCIA A TERCEROS' => 'Transferencia enviada',
            'TRANSFERENCIA DE TERCEROS' => 'Transferencia recibida',
            'DEBITO DEBIN RECURRENTE' => 'Débito DEBIN',
            'DEB. AUTOM. DE SERV.' => 'Débito automático',
            'PAGO TARJETA VISA' => 'Pago tarjeta Visa',
            'PAGO TARJETA' => 'Pago tarjeta',
            'SIST. NAC. DE PAGOS - HABERES' => 'Sueldo',
            'SIST. NAC. DE PAGOS' => 'Sueldo',
            'RESCATE FIMA' => 'Rescate inversión',
            'SUSCRIPCION FIMA' => 'Inversión',
            'INTERES CAPITALIZADO' => 'Intereses',
        ];

        $upperLine = strtoupper($line);
        foreach ($types as $pattern => $name) {
            if (str_contains($upperLine, $pattern)) {
                return $name;
            }
        }

        return 'Otro';
    }

    private function extractRecipient(array $lines, string $type): ?string
    {
        // For purchases, look for merchant name
        if ($type === 'Compra débito') {
            foreach ($lines as $i => $line) {
                if ($i === 0) {
                    continue;
                } // Skip first line (has transaction type)
                $line = trim($line);

                // Look for known merchant patterns
                if (preg_match('/^(PVS\*|DLO\*|MERPAGO\*)(.+)/i', $line, $matches)) {
                    $merchant = trim($matches[2]);
                    $merchant = preg_replace('/\s+/', ' ', $merchant);

                    return $merchant;
                }

                // Look for standalone merchant names (not card numbers, not technical codes)
                if (! preg_match('/^\d{10,}/', $line) &&
                    ! preg_match('/^[0-9\s]+$/', $line) &&
                    strlen($line) > 3 &&
                    strlen($line) < 40) {
                    return trim($line);
                }
            }
        }

        // For automatic debits, look for service name
        if (in_array($type, ['Débito automático', 'Débito DEBIN'])) {
            foreach ($lines as $i => $line) {
                if ($i === 0) {
                    continue;
                }
                $line = trim($line);
                if (! empty($line) &&
                    ! preg_match('/^\d{10,}/', $line) &&
                    ! preg_match('/^NRO\./', $line) &&
                    ! str_contains($line, 'VARIOS') &&
                    ! preg_match('/^[0-9\s]+$/', $line) &&
                    strlen($line) > 3 &&
                    strlen($line) < 40) {
                    return trim($line);
                }
            }
        }

        // For transfers, look for name
        if (in_array($type, ['Transferencia enviada', 'Transferencia recibida'])) {
            foreach ($lines as $i => $line) {
                if ($i === 0) {
                    continue;
                }
                $line = trim($line);

                // Skip technical lines
                if (preg_match('/^(CU|NO|FNCS|LINK|VARIOS|\d{10,}|NRO\.|MERCADO LIBRE|INDUSTRIAL)/', $line)) {
                    continue;
                }

                // Look for name patterns (Mixed case names like "Maria Noelia Garcia")
                if (preg_match('/^[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+\s+[A-ZÁÉÍÓÚÑ][a-záéíóúñ]+/', $line, $matches)) {
                    return trim($matches[0]);
                }

                // All caps name
                if (preg_match('/^[A-ZÁÉÍÓÚÑ][A-ZÁÉÍÓÚÑ\s]+$/', $line) && strlen($line) > 5 && strlen($line) < 40) {
                    return ucwords(strtolower(trim($line)));
                }
            }
        }

        // For salary
        if ($type === 'Sueldo') {
            foreach ($lines as $i => $line) {
                if ($i === 0) {
                    continue;
                }
                $line = trim($line);
                if (! empty($line) &&
                    ! preg_match('/^\d{10,}/', $line) &&
                    ! preg_match('/^INDUSTRIAL/', $line) &&
                    strlen($line) > 3) {
                    return trim($line);
                }
            }
        }

        // For investments
        if (in_array($type, ['Inversión', 'Rescate inversión'])) {
            foreach ($lines as $i => $line) {
                if ($i === 0) {
                    continue;
                }
                $line = trim($line);
                if (str_contains(strtoupper($line), 'FIMA')) {
                    return trim($line);
                }
            }
        }

        // For interests
        if ($type === 'Intereses') {
            foreach ($lines as $i => $line) {
                if ($i === 0) {
                    continue;
                }
                $line = trim($line);
                if (! empty($line) && strlen($line) > 3) {
                    return trim($line);
                }
            }
        }

        return null;
    }

    private function buildTitle(string $type, ?string $recipient): string
    {
        if ($recipient) {
            return "{$type}: {$recipient}";
        }

        return $type;
    }

    private function parseDate(string $dateString): ?string
    {
        // Format: DD/MM/YY
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{2})$/', $dateString, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $year = '20'.$matches[3];

            return "{$year}-{$month}-{$day}";
        }

        return null;
    }

    private function parseAmount(string $amountString): float
    {
        // Remove everything except digits, comma, dot, and minus
        $clean = preg_replace('/[^0-9,.\-]/', '', $amountString);

        // Argentine format: dots for thousands, comma for decimals
        $clean = str_replace('.', '', $clean);
        $clean = str_replace(',', '.', $clean);

        return abs((float) $clean);
    }

    public function normalizeRow(array $row): ?array
    {
        // Not used directly - parse() handles everything
        return null;
    }
}
