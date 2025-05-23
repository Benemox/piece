<?php

namespace App\DataFixtures\Redis;

use App\Shared\Domain\Bus\DispatcherInterface;
use App\Shared\Infrastructure\Symfony\Command\AbstractCli;
use App\Transactions\Domain\Event\UpOneTransactionRaisedEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LoadExampleBillingEventsFixturesCommand extends AbstractCli
{
    private string $fixturesPath = __DIR__;

    public function __construct(
        DispatcherInterface $bus,
        LoggerInterface $logger,
    ) {
        parent::__construct($bus, $logger);
    }

    protected function configure(): void
    {
        $this->setName('app:load_upone_events');
        $this->setDescription('Add fake events to bus');
        $this->setHelp('This command load fake upOne transactions events to bus');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fixtureFiles = glob($this->fixturesPath . '/UpOneTransactionsEvents.json') ?: [];
        $io = new SymfonyStyle($input, $output);

        $fileData = json_decode(file_get_contents($fixtureFiles[0]) ?: '{}', true);

        if (!$fileData) {
            $io->error("Invalid or empty data in file");
            return Command::FAILURE;
        }

        foreach ($fileData['data'] as $doc) {
            $this->dispatch(new UpOneTransactionRaisedEvent($doc['data'], $doc['type']));
        }
        $io->success("Events have been included in queue");

        return Command::SUCCESS;
    }
}