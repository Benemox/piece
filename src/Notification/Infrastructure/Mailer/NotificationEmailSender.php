<?php

namespace App\Notification\Infrastructure\Mailer;

use App\Notification\Domain\Contract\NotificationEmailSenderInterface;
use App\Notification\Domain\Contract\UpSpainMailerInterface;
use App\Shared\Domain\Model\Email;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

readonly class NotificationEmailSender implements NotificationEmailSenderInterface
{
    /**
     * @var string
     */
    private const SUBJECT = 'Notificación del Middleware de Facturación';

    public function __construct(
        private UpSpainMailerInterface $mailer,
        private UpSpainTwigEmailBuilder $emailBuilder,
    ) {
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function sendNotificationEmail(Email $userEmail, array $messages, ?string $subject = null): void
    {
        $email = $this->emailBuilder
            ->setTemplate('email/notify-process.html.twig')
            ->setTemplateParams([
                'title' => self::SUBJECT,
                'messages' => $messages,
            ])
            ->setSubject($subject ?? self::SUBJECT)
            ->setRecipient($userEmail->value())
            ->getEmail();

        $this->mailer->sendEmail($email);
    }
}
