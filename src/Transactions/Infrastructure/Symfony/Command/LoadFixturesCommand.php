<?php

namespace App\Transactions\Infrastructure\Symfony\Command;

use App\Shared\Domain\Bus\DispatcherInterface;
use App\Shared\Infrastructure\Elastica\ElasticaClientInterface;
use App\Shared\Infrastructure\Symfony\Command\AbstractCli;
use App\Transactions\Domain\Contracts\RegistryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class LoadFixturesCommand extends AbstractCli
{
    private string $fixturesPath = __DIR__.'/../../../../DataFixtures/Elasticsearch';

    public function __construct(
        DispatcherInterface $bus,
        LoggerInterface $logger,
        private ElasticaClientInterface $elasticaClient,
        #[Autowire(env: 'APP_ENV')]
        private readonly string $env
    ) {
        parent::__construct($bus, $logger);
    }

    protected function configure(): void
    {
        $this->setName('app:load-elasticsearch-fixtures');
        $this->setDescription('Load elasticsearch fixtures');
        $this->setHelp('This command allows you to load fixtures into your Elasticsearch indices');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ('test' === $this->env) {
            $indexPrefix = $this->env.'_';
        } else {
            $indexPrefix = '';
        }

        $fixtureFiles = glob($this->fixturesPath.'/*.json') ?: [];
        $io = new SymfonyStyle($input, $output);

        foreach ($fixtureFiles as $file) {
            $indexName = basename($file, '.json');
            $fileData = json_decode(file_get_contents($file) ?: '{}', true);

            $index = $indexPrefix.$fileData['index'];
            $objectType = $fileData['type'];
            $data = $fileData['data'];

            if (!$data || !$index || !$objectType) {
                $io->error("Invalid or empty data in file: $file");
                continue;
            }

            foreach ($data as $doc) {
                $element = new $objectType($doc);
                if (!$element instanceof RegistryInterface) {
                    continue;
                }
                $this->elasticaClient->addRegistry(
                    $index,
                    $element,
                    null,
                );
            }

            $io->success("Fixtures loaded for index: $indexName");
        }

        return Command::SUCCESS;
    }
}
