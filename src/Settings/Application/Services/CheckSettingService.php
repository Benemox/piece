<?php

namespace App\Settings\Application\Services;

use App\Settings\Domain\Model\AvailableSettings;
use App\Settings\Infrastructure\Persistence\Doctrine\SettingsRepositoryInterface;

readonly class CheckSettingService implements CheckSettingServiceInterface
{
    public function __construct(
        private SettingsRepositoryInterface $settingsRepository
    ) {
    }

    public function isTransactionsAccountEnrichmentEnabled(): bool
    {
        $setting = $this->settingsRepository->findSetting(AvailableSettings::TRANSACTION_ACCOUNT_ENRICHER_FORWARDING);

        if (null === $setting) {
            return false;
        }
        $state = $setting->active();
        assert(null !== $state, 'Setting status cant be null at this point');

        return $state;
    }

    public function isTransactionsCommerceEnrichmentEnabled(): bool
    {
        $setting = $this->settingsRepository->findSetting(AvailableSettings::TRANSACTION_COMMERCE_ENRICHER_FORWARDING);
        if (null === $setting) {
            return false;
        }
        $state = $setting->active();
        assert(null !== $state, 'Setting status cant be null at this point');

        return $state;
    }
}
