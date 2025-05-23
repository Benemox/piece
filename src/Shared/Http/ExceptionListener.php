<?php

namespace App\Shared\Http;

use App\Shared\Domain\Exception\ClientExceptionInterface;
use App\Shared\Domain\Exception\DomainException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;

class ExceptionListener
{
    private string $env;

    public function __construct(KernelInterface $kernel)
    {
        $this->env = $kernel->getEnvironment();
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $response = new JsonResponse();
        $statusCode = $this->getStatusCode($exception);
        $data = ['message' => $exception->getMessage()];

        // Handle specific exception types
        if ($exception instanceof FormValidationException) {
            $data['errors'] = $exception->getErrors();
        } elseif ($exception instanceof AccessNotGrantedException) {
            $statusCode = Response::HTTP_FORBIDDEN;
        } elseif ($exception instanceof ClientExceptionInterface) {
            $data = $this->handleClientException($exception);
        } elseif ($exception instanceof DomainException) {
            // No additional handling needed, just set the message
        } elseif ($exception instanceof HandlerFailedException && $exception->getPrevious()) {
            $data['message'] = $exception->getPrevious()->getMessage();
        }

        // Add trace in dev or local environments
        if (in_array($this->env, ['dev', 'local'], true)) {
            $data['trace'] = $exception->getTraceAsString();
        }

        // Set the response
        $response->setStatusCode($statusCode);
        $response->setData($data);
        $event->setResponse($response);
    }

    private function getStatusCode(\Throwable $exception): int
    {
        $code = $exception->getCode();

        return ($code > 100 && $code < 511) ? $code : Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    private function handleClientException(ClientExceptionInterface $exception): array
    {
        $message = $exception->getMessage(); // @phpstan-ignore-line
        if ($exception->getCode() > 100 && $exception->getCode() < 511) { // @phpstan-ignore-line
            return ['message' => $message];
        }

        return [
            'message' => 'Client '.$exception->getClientName().' unavailable',
        ];
    }
}
