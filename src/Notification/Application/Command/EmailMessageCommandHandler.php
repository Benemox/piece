<?php

namespace App\Notification\Application\Command;

use App\Notification\Domain\Contract\NotificationEmailSenderInterface;
use App\Shared\Domain\Bus\HandlerInterface;

readonly class EmailMessageCommandHandler implements HandlerInterface
{
    public function __construct(
        private NotificationEmailSenderInterface $notificationEmailSender
    ) {
    }

    public function __invoke(EmailMessageCommand $command): void
    {
        $this->notificationEmailSender->sendNotificationEmail(
            userEmail: $command->emailAddress,
            messages: $command->messages,
        );
    }
}
