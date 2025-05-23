<?php

namespace App\Settings\Infrastructure\Symfony\Command;

use App\Settings\Domain\Model\AvailableSettings;
use App\Settings\Domain\Model\Setting;
use App\Settings\Infrastructure\Persistence\Doctrine\SettingsRepositoryInterface;
use App\Shared\Domain\Bus\DispatcherInterface;
use App\Shared\Infrastructure\Symfony\Command\AbstractCli;
use App\Shared\Infrastructure\Symfony\String\UidGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetDefaultSettingsCommand extends AbstractCli
{
    public function __construct(
        DispatcherInterface $messageBus,
        LoggerInterface $logger,
        private UidGenerator $uuidGenerator,
        private readonly SettingsRepositoryInterface $settingsRepository
    ) {
        parent::__construct($messageBus, $logger);
    }

    protected function configure(): void
    {
        $this->setName('app:set_settings');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach (AvailableSettings::SETTINGS_DATA as $key => $data) {
            $id = $this->uuidGenerator->generate();
            $setting = Setting::cast($id, $key, $data['default']);
            $this->settingsRepository->save($setting);
        }

        echo "Settings set\n";

        return Command::SUCCESS;
    }
}
