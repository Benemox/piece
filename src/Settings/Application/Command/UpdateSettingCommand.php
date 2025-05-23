<?php

namespace App\Settings\Application\Command;

use App\Shared\Domain\Bus\CommandMessageInterface;

readonly class UpdateSettingCommand implements CommandMessageInterface
{
    public function __construct(
        public array $settings
    ) {
    }
}
