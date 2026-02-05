<?php

namespace App\Services\Import\Parsers;

interface CsvParserInterface
{
    /**
     * Get the unique identifier for this parser
     */
    public function getSource(): string;

    /**
     * Get the display name for this parser
     */
    public function getName(): string;

    /**
     * Get the CSV delimiter character
     */
    public function getDelimiter(): string;

    /**
     * Parse a CSV file and return normalized rows
     *
     * @param  string  $filePath  Path to the CSV file
     * @return iterable<array> Iterator of normalized row data
     */
    public function parse(string $filePath): iterable;

    /**
     * Transform a raw CSV row into a normalized format
     *
     * @param  array  $row  Raw CSV row data
     * @return array|null Normalized data or null to skip row
     */
    public function normalizeRow(array $row): ?array;

    /**
     * Get the reference ID prefix for this source
     */
    public function getReferencePrefix(): string;
}
