<?php

namespace App\Shared\Infrastructure\File\Csv;

readonly class CsvFileService implements CsvFileHandlerInterface
{
    public function __construct(
        private string $localFileStoragePath
    ) {
    }

    public function buildCsv(array $data, array $headers = [], string $separator = ';'): string
    {
        $outputBuffer = fopen('php://temp', 'rb+');

        if (false === $outputBuffer) {
            throw new \RuntimeException('Failed to open stream');
        }

        if (!empty($headers)) {
            fputcsv($outputBuffer, $headers, $separator);
        }

        foreach ($data as $row) {
            fputcsv($outputBuffer, $row, $separator);
        }
        rewind($outputBuffer);
        $csv = stream_get_contents($outputBuffer);

        if (false === $csv) {
            throw new \RuntimeException('Failed to get CSV content');
        }

        fclose($outputBuffer);

        return $csv;
    }

    public function saveCsv(string $csv, string $filename): void
    {
        // Ensure the directory exists
        if (!is_dir($this->localFileStoragePath) && !mkdir(
            $concurrentDirectory = $this->localFileStoragePath,
            0777,
            true
        ) && !is_dir($concurrentDirectory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        $filePath = $this->localFileStoragePath.'/'.$filename.'.csv';
        file_put_contents($filePath, $csv);
    }

    public function getCsv(string $filename): string
    {
        $filePath = $this->localFileStoragePath.'/'.$filename.'.csv';
        if (!file_exists($filePath)) {
            throw new \RuntimeException('Csv not found: '.$filename.'.csv');
        }
        $content = file_get_contents($filePath);
        if (false === $content) {
            throw new \RuntimeException('Csv not found: '.$filename.'.csv');
        }

        return $content;
    }
}
