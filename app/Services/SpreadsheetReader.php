<?php

namespace App\Services;

use ZipArchive;

/**
 * Minimal, dependency-free .xlsx reader.
 *
 * An .xlsx file is just a ZIP archive containing XML parts. This reads the
 * first worksheet plus the shared-strings table directly, with no Composer
 * package required (useful in environments where `composer require` isn't
 * possible). It intentionally only supports the modern .xlsx format — the
 * legacy binary .xls format is a completely different structure and is not
 * supported here.
 */
class SpreadsheetReader
{
    /**
     * @return array<int, array<int, string|null>> Rows of cell values (0-indexed columns).
     */
    public static function readXlsx(string $path): array
    {
        $zip = new ZipArchive();

        if ($zip->open($path) !== true) {
            throw new \RuntimeException('Could not open the .xlsx file — it may be corrupted.');
        }

        // 1. Shared strings table (most text cells reference an index into this).
        $sharedStrings = [];
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedStringsXml !== false) {
            $sst = simplexml_load_string($sharedStringsXml);
            foreach ($sst->si as $si) {
                // A shared string can be a single <t> or multiple <r><t> "runs" — concatenate all text.
                $text = '';
                if (isset($si->t)) {
                    $text = (string) $si->t;
                } elseif (isset($si->r)) {
                    foreach ($si->r as $run) {
                        $text .= (string) $run->t;
                    }
                }
                $sharedStrings[] = $text;
            }
        }

        // 2. Find the first worksheet (usually xl/worksheets/sheet1.xml).
        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        if ($sheetXml === false) {
            $zip->close();
            throw new \RuntimeException('Could not find worksheet data in this .xlsx file.');
        }
        $zip->close();

        $sheet = simplexml_load_string($sheetXml);
        $rows = [];

        foreach ($sheet->sheetData->row as $row) {
            $rowData = [];

            foreach ($row->c as $cell) {
                $ref = (string) $cell['r']; // e.g. "C4"
                $colIndex = self::columnLetterToIndex(preg_replace('/[0-9]/', '', $ref));
                $type = (string) $cell['t'];

                $value = isset($cell->v) ? (string) $cell->v : null;

                if ($type === 's' && $value !== null) {
                    // Shared string — resolve the index.
                    $value = $sharedStrings[(int) $value] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = isset($cell->is->t) ? (string) $cell->is->t : '';
                }

                $rowData[$colIndex] = $value !== null ? trim($value) : null;
            }

            if (! empty($rowData)) {
                // Fill any gaps (empty cells) so every row lines up by column index.
                $maxCol = max(array_keys($rowData));
                $normalized = [];
                for ($i = 0; $i <= $maxCol; $i++) {
                    $normalized[$i] = $rowData[$i] ?? null;
                }
                $rows[] = $normalized;
            }
        }

        return $rows;
    }

    /**
     * Convert a spreadsheet column letter ("A", "B", ... "AA") to a 0-indexed column number.
     */
    private static function columnLetterToIndex(string $letters): int
    {
        $letters = strtoupper($letters);
        $index = 0;
        for ($i = 0; $i < strlen($letters); $i++) {
            $index = $index * 26 + (ord($letters[$i]) - ord('A') + 1);
        }
        return $index - 1;
    }
}
