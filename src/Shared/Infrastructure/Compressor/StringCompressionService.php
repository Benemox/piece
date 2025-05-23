<?php

namespace App\Shared\Infrastructure\Compressor;

class StringCompressionService implements StringCompressionServiceInterface
{
    /**
     * Compress a string using gzip.
     */
    public function compress(string $data): string
    {
        $data = gzcompress($data, 9); // Level 9 is the highest compression

        if (false === $data) {
            throw new \RuntimeException('Failed to compress the string.');
        }

        return $data;
    }

    /**
     * Decompress a gzip-compressed string.
     */
    public function decompress(string $data): string
    {
        $decompressed = gzuncompress($data);

        if (false === $decompressed) {
            throw new \RuntimeException('Failed to decompress the string.');
        }

        return $decompressed;
    }
}
