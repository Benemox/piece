<?php

namespace App\Shared\Infrastructure\Symfony\Subscriber;

use App\Shared\Http\BadRequestResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Validator\Exception\ValidationFailedException;

readonly class ValidatorExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        if (($previous = $exception->getPrevious()) && $previous instanceof ValidationFailedException) {
            $errors = [];
            foreach ($previous->getViolations() as $violation) {
                if ($violation->getPropertyPath()) {
                    $errors[$violation->getPropertyPath()][] = $violation->getMessage();
                }
            }

            $event->setResponse(
                new JsonResponse(new BadRequestResponse(
                    '400',
                    'Validation failed',
                    $errors
                ))
            );
        }
    }
}
