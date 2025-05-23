<?php

namespace App\Notification\Domain\Contract;

use App\Notification\Domain\Exception\MailDeliveringException;
use App\Notification\Domain\Model\Email;

interface UpSpainMailerInterface
{
    /**
     * @throws MailDeliveringException
     */
    public function sendEmail(Email $email): void;
}
