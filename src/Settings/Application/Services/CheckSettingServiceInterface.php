<?php

namespace App\Settings\Application\Services;

interface CheckSettingServiceInterface
{
    public function isTransactionsAccountEnrichmentEnabled(): bool;

    public function isTransactionsCommerceEnrichmentEnabled(): bool;
}
