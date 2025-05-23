<?php

namespace App\Shared\Domain;

use JMS\Serializer\Annotation as Serializer;

abstract class AggregateRoot
{
    /**
     * @Serializer\Exclude()
     */
    protected array $events = [];

    public function pullEvents(): array
    {
        $events = $this->events;
        $this->events = [];

        return $events;
    }
}
