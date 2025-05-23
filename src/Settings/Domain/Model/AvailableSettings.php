<?php

namespace App\Settings\Domain\Model;

class AvailableSettings
{
    public const TRANSACTION_ACCOUNT_ENRICHER_FORWARDING = 'transaction_account_enricher_forwarding';
    public const TRANSACTION_COMMERCE_ENRICHER_FORWARDING = 'transaction_commerce_enricher_forwarding';

    public const SETTINGS_NAMES = [
        self::TRANSACTION_ACCOUNT_ENRICHER_FORWARDING,
        self::TRANSACTION_COMMERCE_ENRICHER_FORWARDING,
    ];

    public const SETTINGS_DATA = [
        self::TRANSACTION_ACCOUNT_ENRICHER_FORWARDING => [
            'name' => self::TRANSACTION_ACCOUNT_ENRICHER_FORWARDING,
            'description' => 'Forward failed transactions to account enricher',
            'type' => Setting::TYPE_BOOL,
            'default' => 'true',
        ],
        self::TRANSACTION_COMMERCE_ENRICHER_FORWARDING => [
            'name' => self::TRANSACTION_COMMERCE_ENRICHER_FORWARDING,
            'description' => 'Forward failed transactions to commerce enricher',
            'type' => Setting::TYPE_BOOL,
            'default' => 'true',
            ],
    ];
}
