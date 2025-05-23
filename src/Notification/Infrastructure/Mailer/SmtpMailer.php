<?php

namespace App\Notification\Infrastructure\Mailer;

use App\Notification\Domain\Contract\UpSpainMailerInterface;
use App\Notification\Domain\Exception\MailDeliveringException;
use App\Notification\Domain\Model\Email;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email as EmailMessage;

readonly class SmtpMailer implements UpSpainMailerInterface
{
    public function __construct(private MailerInterface $symfonyMailer, private LoggerInterface $logger)
    {
    }

    /**
     * @throws MailDeliveringException
     */
    public function sendEmail(Email $email): void
    {
        $emailMessage = (new EmailMessage())
            ->from($email->from())
            ->to($email->to())
            ->subject($email->subject())
            ->html($email->body());

        foreach ($email->attachments() as $attachment) {
            $emailMessage->attach($attachment->contents(), $attachment->name());
        }

        try {
            $this->symfonyMailer->send($emailMessage);
        } catch (TransportExceptionInterface $transportException) {
            $this->logger->error('SmtpMailer: '.$transportException->getMessage(), $transportException->getTrace());

            throw MailDeliveringException::create('mailer.sent', 400);
        }
    }
}
