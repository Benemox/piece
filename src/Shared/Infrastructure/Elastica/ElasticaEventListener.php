<?php

namespace App\Shared\Infrastructure\Elastica;

use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\Bus\DispatcherInterface;
use App\Transactions\Domain\Contracts\RegistryInterface;

class ElasticaEventListener
{
    protected array $events = [];

    public function __construct(
        private DispatcherInterface $eventBus
    ) {
    }

    public function onDocumentAdded(RegistryInterface $object): void
    {
        if ($object instanceof AggregateRoot) {
            array_push($this->events, ...$object->pullEvents());
        }
    }

    public function onFlush(): void
    {
        foreach ($this->events as $key => $event) {
            unset($this->events[$key]);
            $this->eventBus->dispatch($event);
        }
    }
}
