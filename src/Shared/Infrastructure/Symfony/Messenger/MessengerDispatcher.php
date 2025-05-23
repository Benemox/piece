<?php

namespace App\Shared\Infrastructure\Symfony\Messenger;

use App\Shared\Domain\Bus\DispatcherInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

readonly class MessengerDispatcher implements DispatcherInterface
{
    public function __construct(private MessageBusInterface $messageBus)
    {
    }

    public function dispatch(object $object): mixed
    {
        $envelope = $this->messageBus->dispatch($object);

        /** @var HandledStamp $stamp */
        $stamp = $envelope->last(HandledStamp::class);

        return $stamp->getResult();
    }
}
