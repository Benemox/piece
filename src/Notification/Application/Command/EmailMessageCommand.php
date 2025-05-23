<?php

namespace App\Notification\Application\Command;

use App\Shared\Domain\Bus\AsyncMessageInterface;
use App\Shared\Domain\Model\Email;

readonly class EmailMessageCommand implements AsyncMessageInterface
{
    public function __construct(
        public Email $emailAddress,
        public array $messages,
        public ?string $subject = null,
    ) {
    }
}
