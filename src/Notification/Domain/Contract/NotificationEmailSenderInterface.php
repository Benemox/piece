<?php

namespace App\Notification\Domain\Contract;

use App\Shared\Domain\Model\Email;

interface NotificationEmailSenderInterface
{
    public function sendNotificationEmail(Email $userEmail, array $messages, ?string $subject = null): void;
}
