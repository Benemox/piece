<?php

namespace App\Shared\Infrastructure\Logging;

use App\Shared\Infrastructure\Sanitizer\DotArray;
use App\Shared\Infrastructure\Sanitizer\ValueSanitizer;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LogRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly array $monologSanitizeKeys,
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 270]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $content = $request->getContent();
        try {
            $data = json_decode($content, true, JSON_THROW_ON_ERROR, JSON_THROW_ON_ERROR);
            if (is_array($data)) {
                $dot = new DotArray($data);
                foreach ($this->monologSanitizeKeys as $field) {
                    $key = str_replace('request.body.', '', $field);
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
                "\n\n-------- Received HTTP request -----------\n\n%s\n%s\n\n%s\n\n-------------------\n",
                $request->getMethod().' '.$request->getUri().' '.$request->getProtocolVersion(),
                $event->getRequest()->headers,
                $content
            )
        );
    }
}
