<?php

namespace App\Notification\Domain\Model;

readonly class Email
{
    /**
     * @param array<Attachment> $attachments
     */
    public function __construct(private string $from, private string $to, private string $subject, private string $body, private array $attachments = [])
    {
    }

    public function from(): string
    {
        return $this->from;
    }

    public function to(): string
    {
        return $this->to;
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function body(): string
    {
        return $this->body;
    }

    /**
     * @return Attachment[]
     */
    public function attachments(): array
    {
        return $this->attachments;
    }
}
