<?php

namespace App\Settings\Application\Query;

use App\Shared\Domain\Bus\QueryMessageInterface;

readonly class GetSettingsQuery implements QueryMessageInterface
{
    public function __construct()
    {
    }
}
