<?php

namespace App\Shared\Infrastructure\File\Csv;

interface CsvFileHandlerInterface
{
    /**
     * Builds a CSV string from an array.
     *
     * @param array  $data      the array of data to be converted to CSV
     * @param array  $headers   the headers of the CSV
     * @param string $separator the separator to use in the CSV
     *
     * @return string the generated CSV string
     */
    public function buildCsv(array $data, array $headers = [], string $separator = ';'): string;

    /**
     * Saves a CSV string to a file.
     *
     * @param string $csv      the CSV string to be saved
     * @param string $filename the name of the file to save the CSV to
     */
    public function saveCsv(string $csv, string $filename): void;

    /**
     * Retrieves the content of a CSV file by its filename.
     *
     * @param string $filename the name of the file to retrieve
     *
     * @return string the content of the CSV file
     */
    public function getCsv(string $filename): string;
}
