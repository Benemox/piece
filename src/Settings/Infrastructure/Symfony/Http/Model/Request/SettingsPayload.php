<?php

namespace App\Settings\Infrastructure\Symfony\Http\Model\Request;

use App\Settings\Domain\Model\AvailableSettings;
use App\Settings\Domain\Model\Setting;
use OpenApi\Attributes as OA;
use OpenApi\Attributes\Property;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class SettingsPayload
{
    public function __construct(
        #[Property(
            type: 'array',
            items: new OA\Items(
                properties: [
                    new Property(
                        property: 'setting',
                        type: 'string',
                        enum: AvailableSettings::SETTINGS_NAMES,
                    ),
                    new Property(
                        property: 'value',
                        type: 'string',
                        enum: Setting::TYPES,
                        example: 'true',
                    ),
                ]
            )
        )]
        #[Assert\NotBlank]
        #[Assert\Type('array')]
        public array $settings
    ) {
    }

    #[Assert\Callback]
    public function validateDates(ExecutionContextInterface $context): void
    {
        foreach ($this->settings as $setting) {
            foreach ($setting as $key => $value) {
                if ('setting' === $key) {
                    if (!in_array($value, AvailableSettings::SETTINGS_NAMES)) {
                        $context->buildViolation('The "setting" must be valid.')
                            ->atPath('setting')
                            ->addViolation();
                    }
                }

                if ('value' === $key) {
                    if (empty($value)) {
                        $context->buildViolation('The "value" must be valid for setting.')
                            ->atPath('value')
                            ->addViolation();
                    }
                }
            }
        }
    }
}
