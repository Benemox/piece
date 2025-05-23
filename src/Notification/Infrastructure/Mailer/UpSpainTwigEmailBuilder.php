<?php

namespace App\Notification\Infrastructure\Mailer;

use App\Notification\Domain\Model\Attachment;
use App\Notification\Domain\Model\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class UpSpainTwigEmailBuilder
{
    /**
     * @var string
     */
    final public const FROM_EMAIL_ADDRESS = 'no.reply@up-spain.com';

    private string $to;

    private string $templateName;

    private string $subject;

    private array $templateParams = [];

    /**
     * @var Attachment[]
     */
    private array $attachments = [];

    public function __construct(private Environment $twig)
    {
    }

    public function setRecipient(string $to): self
    {
        $this->to = $to;

        return $this;
    }

    public function setTemplate(string $templateName): self
    {
        $this->templateName = $templateName;

        return $this;
    }

    public function setTemplateParam(string $name, mixed $value): self
    {
        $this->templateParams[$name] = $value;

        return $this;
    }

    public function setTemplateParams(array $params): self
    {
        $this->templateParams = array_merge($this->templateParams, $params);

        return $this;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function addAttachment(array $attachment = []): self
    {
        $this->attachments = array_merge($this->attachments, $attachment);

        return $this;
    }

    /**
     * @throws SyntaxError
     * @throws RuntimeError
     * @throws LoaderError
     */
    public function getEmail(): Email
    {
        $body = $this->twig->render($this->templateName, $this->templateParams);

        return new Email(
            self::FROM_EMAIL_ADDRESS,
            $this->to,
            $this->subject,
            $body,
            $this->attachments,
        );
    }
}
