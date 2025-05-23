<?php

namespace App\Settings\Infrastructure\Persistence\Doctrine;

use App\Settings\Domain\Model\Setting;

interface SettingsRepositoryInterface
{
    public function save(Setting $provider): void;

    public function findSetting(string $settingName): ?Setting;

    public function findAllSettings(): array;
}
