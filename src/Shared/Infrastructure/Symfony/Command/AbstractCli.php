<?php

namespace App\Shared\Infrastructure\Symfony\Command;

use App\Shared\Domain\Bus\DispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;

abstract class AbstractCli extends Command
{
    protected LoggerInterface $logger;

    public function __construct(
        private DispatcherInterface $bus,
        LoggerInterface $logger,
        ?string $name = null
    ) {
        $this->logger = $logger;
        parent::__construct($name);
    }

    protected function dispatch(object $message): mixed
    {
        return $this->bus->dispatch($message);
    }

    public function logError(string $message): void
    {
        $this->logger->error('Command error', [
            'error' => $message,
        ]);
    }

    public function logSuccess(): void
    {
        $this->logger->info('Command SUCCESS', [
            'info' => 'DONE!',
        ]);
    }
}
