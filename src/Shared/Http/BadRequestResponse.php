<?php

namespace App\Shared\Http;

class BadRequestResponse implements \JsonSerializable
{
    public function __construct(
        public string $code,
        public string $message,
        public mixed $data
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return [
            'message' => $this->message,
            'data' => $this->data,
        ];
    }
}
