<?php

namespace App\Transactions\Infrastructure\Symfony\Command;

use App\Shared\Domain\Bus\DispatcherInterface;
use App\Shared\Infrastructure\Symfony\Command\AbstractCli;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends AbstractCli
{
    public function __construct(
        DispatcherInterface $bus,
        LoggerInterface $logger,
    ) {
        parent::__construct($bus, $logger);
    }

    protected function configure(): void
    {
        $this->setName('app:test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return Command::SUCCESS;
    }
}
