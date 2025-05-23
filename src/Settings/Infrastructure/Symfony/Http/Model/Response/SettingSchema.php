<?php

namespace App\Settings\Infrastructure\Symfony\Http\Model\Response;

use App\Settings\Domain\Model\Setting;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'SettingSchema',
    properties: [
        new OA\Property(
            property: 'setting',
            description: 'Setting name',
            type: 'string',
            example: 'send_data',
        ),

        new OA\Property(
            property: 'type',
            description: 'Setting type',
            type: 'string',
            enum: [Setting::TYPE_BOOL, Setting::TYPE_INT],
            example: Setting::TYPE_BOOL,
        ),
        new OA\Property(
            property: 'value',
            description: 'Setting value',
            type: 'string',
            example: 'true',
        ),
    ],
    type: 'object'
)]
readonly class SettingSchema implements \JsonSerializable
{
    public function __construct(
        private Setting $setting
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'setting' => $this->setting->getSettingName(),
            'description' => $this->setting->getDescription(),
            'type' => $this->setting->getType(),
            'value' => $this->setting->getValue(),
        ];
    }
}
