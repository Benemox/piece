<?php

namespace App\Shared\Infrastructure\Logging;

use App\Shared\Infrastructure\Sanitizer\DotArray;
use App\Shared\Infrastructure\Sanitizer\ValueSanitizer;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LogResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly array $monologSanitizeKeys,
        private readonly LoggerInterface $logger
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => ['onKernelResponse', 270]];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if ('application/json' !== $event->getResponse()->headers->get('Content-Type')) {
            return;
        }
        $response = $event->getResponse();
        $content = $response->getContent() ?: '';
        try {
            $data = json_decode($content, true, JSON_THROW_ON_ERROR, JSON_THROW_ON_ERROR);
            if (is_array($data)) {
                $dot = new DotArray($data);
                foreach ($this->monologSanitizeKeys as $field) {
                    $key = str_replace('response.body.', '', $field);
                    if (!$value = $dot->get($key)) {
                        continue;
                    }
                    $dot->set($key, ValueSanitizer::sanitize(ValueSanitizer::DEFAULT, $value));
                }
            }
            $content = json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
        } catch (\Exception) {
        }

        $this->logger->debug(
            sprintf(
                "\n\n-------- Returning HTTP response -----------\n\n%s\n%s\n\n%s\n\n-------------------\n",
                $response->getProtocolVersion().' '.$response->getStatusCode().' '.Response::$statusTexts[$response->getStatusCode()],
                $event->getRequest()->headers,
                $content
            )
        );
    }
}
