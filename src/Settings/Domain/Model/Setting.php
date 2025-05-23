<?php

namespace App\Settings\Domain\Model;

use App\Settings\Domain\Exception\SettingException;
use App\Shared\Domain\AggregateRoot;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'setting')]
class Setting extends AggregateRoot
{
    public const TYPE_BOOL = 'boolean';
    public const TYPE_INT = 'int';

    public const TYPES = [
        self::TYPE_INT,
        self::TYPE_BOOL,
    ];

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string', length: 128, unique: true)]
        public string $id,
        #[ORM\Id]
        #[ORM\Column(type: 'string', length: 255, unique: true)]
        public string $settingName,
        #[ORM\Column(length: 255)]
        private string $value
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getSettingName(): string
    {
        return $this->settingName;
    }

    public function getDescription(): string
    {
        return AvailableSettings::SETTINGS_DATA[$this->settingName]['description'];
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getType(): string
    {
        return AvailableSettings::SETTINGS_DATA[$this->settingName]['type'];
    }

    public function active(): ?bool
    {
        $value = strtolower(trim((string) $this->value));

        return match ($value) {
            'true' => true,
            'false' => false,
            default => null,
        };
    }

    /**
     * Returns amount only if int type.
     */
    public function amount(): ?int
    {
        if (self::TYPE_INT !== $this->getType()) {
            return null;
        }

        return (int) $this->value;
    }

    public function setValue(mixed $value): void
    {
        $booleans = [
            'true',
            'false',
        ];

        if (self::TYPE_BOOL === $this->getType() && !in_array($value, $booleans, true)) {
            throw SettingException::invalidType();
        }

        if (self::TYPE_INT === $this->getType() && !is_int($value)) {
            throw SettingException::invalidType();
        }

        $this->value = $value;
    }

    public function getDefaultValue(): mixed
    {
        return AvailableSettings::SETTINGS_DATA[$this->settingName]['default'];
    }

    public static function cast(string $id, string $settingName, mixed $value): self
    {
        if (!in_array($settingName, AvailableSettings::SETTINGS_NAMES, false)) {
            throw SettingException::notFound();
        }

        $booleans = [
            'true',
            'false',
        ];

        $type = (string) AvailableSettings::SETTINGS_DATA[$settingName]['type'];

        if (self::TYPE_BOOL === $type && !in_array(
            $value,
            $booleans,
            true
        )) {
            throw SettingException::invalidType();
        }

        return new self(
            id: $id,
            settingName: $settingName,
            value: $value ?? AvailableSettings::SETTINGS_DATA[$settingName]['default'],
        );
    }
}
