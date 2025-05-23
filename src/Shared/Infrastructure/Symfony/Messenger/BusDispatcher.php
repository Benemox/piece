<?php

namespace App\Shared\Infrastructure\Symfony\Messenger;

use App\Shared\Domain\Bus\AsyncMessageInterface;
use App\Shared\Domain\Bus\CommandMessageInterface;
use App\Shared\Domain\Bus\DispatcherInterface;
use App\Shared\Domain\Bus\EventMessageInterface;
use App\Shared\Domain\Bus\QueryMessageInterface;
use App\Shared\Domain\Bus\UpOneEventMessageInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final readonly class BusDispatcher implements DispatcherInterface
{
    public function __construct(
        private MessageBusInterface $eventBus,
        private MessageBusInterface $queryBus,
        private MessageBusInterface $commandBus
    ) {
    }

    public function dispatch(object $object): mixed
    {
        if ($object instanceof QueryMessageInterface) {
            $envelope = $this->queryBus->dispatch($object);
            $handledStamp = $this->assertSingleHandler($envelope);

            return $handledStamp->getResult();
        }

        if ($object instanceof CommandMessageInterface) {
            $envelope = $this->commandBus->dispatch($object);
            $handledStamp = $this->assertSingleHandler($envelope);

            return $handledStamp->getResult();
        }

        if ($object instanceof EventMessageInterface
            || $object instanceof AsyncMessageInterface
            || $object instanceof UpOneEventMessageInterface
        ) {
            $this->eventBus->dispatch($object);
        }

        return null;
    }

    private function assertSingleHandler(Envelope $envelope): HandledStamp
    {
        /** @var HandledStamp[] $handledStamps */
        $handledStamps = $envelope->all(HandledStamp::class);

        if (!$handledStamps) {
            $className = get_class($envelope->getMessage());
            throw new \LogicException("Message {$className} was not handled. Did you forget to add a handler for it?");
        }

        if (count($handledStamps) > 1) {
            $className = get_class($envelope->getMessage());
            $handlers = implode(
                ', ',
                array_map(fn(HandledStamp $stamp): string => sprintf('"%s"', $stamp->getHandlerName()), $handledStamps)
            );

            throw new \LogicException(
                "Message {$className} was handled by multiple handlers: {$handlers}. Only one handler per message is supported."
            );
        }

        return $handledStamps[0];
    }
}
