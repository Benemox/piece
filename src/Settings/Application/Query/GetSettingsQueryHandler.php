<?php

namespace App\Settings\Application\Query;

use App\Settings\Domain\Model\Setting;
use App\Settings\Infrastructure\Persistence\Doctrine\SettingsRepositoryInterface;
use App\Settings\Infrastructure\Symfony\Http\Model\Response\SettingSchema;
use App\Shared\Domain\Bus\HandlerInterface;

readonly class GetSettingsQueryHandler implements HandlerInterface
{
    public function __construct(
        private SettingsRepositoryInterface $settingsRepository
    ) {
    }

    public function __invoke(GetSettingsQuery $query): array
    {
        $settings = $this->settingsRepository->findAllSettings();

        return array_map(static function (Setting $setting) {
            return new SettingSchema(
                $setting
            );
        }, $settings);
    }
}
