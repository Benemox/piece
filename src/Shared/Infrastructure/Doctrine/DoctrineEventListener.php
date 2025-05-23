<?php

namespace App\Shared\Infrastructure\Doctrine;

use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\Bus\DispatcherInterface;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
#[AsDoctrineListener(event: Events::postFlush)]
class DoctrineEventListener
{
    protected array $events = [];

    public function __construct(private readonly DispatcherInterface $eventBus)
    {
    }

    public function postPersist(PostPersistEventArgs $args): void
    {
        $object = $args->getObject();

        if ($object instanceof AggregateRoot) {
            array_push($this->events, ...$object->pullEvents());
        }
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $object = $args->getObject();
        if ($object instanceof AggregateRoot) {
            array_push($this->events, ...$object->pullEvents());
        }
    }

    public function postFlush(PostFlushEventArgs $args): void
    {
        foreach ($this->events as $key => $event) {
            unset($this->events[$key]);
            $this->eventBus->dispatch($event);
        }
    }
}
