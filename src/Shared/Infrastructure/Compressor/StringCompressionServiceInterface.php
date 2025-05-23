<?php

namespace App\Shared\Infrastructure\Compressor;

interface StringCompressionServiceInterface
{
    public function compress(string $data): string;

    public function decompress(string $data): string;
}
