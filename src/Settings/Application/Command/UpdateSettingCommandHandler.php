<?php

namespace App\Settings\Application\Command;

use App\Settings\Domain\Exception\SettingException;
use App\Settings\Domain\Model\Setting;
use App\Settings\Infrastructure\Persistence\Doctrine\SettingsRepositoryInterface;
use App\Settings\Infrastructure\Symfony\Http\Model\Response\SettingSchema;
use App\Shared\Domain\Bus\HandlerInterface;

readonly class UpdateSettingCommandHandler implements HandlerInterface
{
    public function __construct(
        private SettingsRepositoryInterface $settingsRepository,
    ) {
    }

    public function __invoke(UpdateSettingCommand $command): array
    {
        $updated = [];
        foreach ($command->settings as $setting) {
            if (!isset($setting['setting']) || !isset($setting['value'])) {
                throw new \InvalidArgumentException('Invalid setting data');
            }

            $exist = $this->settingsRepository->findSetting($setting['setting']);

            if (null === $exist) {
                throw SettingException::notFound();
            }

            $exist->setValue($setting['value']);

            $this->settingsRepository->save($exist);

            $updated[] = $exist;
        }

        return array_map(static function (Setting $setting) {
            return new SettingSchema($setting);
        }, $updated);
    }
}
