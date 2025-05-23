<?php

namespace App\Notification\Domain\Model;

readonly class Attachment
{
    /**
     * @param resource $contents
     */
    public function __construct(private string $name, private mixed $contents)
    {
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return resource
     */
    public function contents(): mixed
    {
        return $this->contents;
    }
}
