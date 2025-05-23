<?php

namespace App\Commerces\Domain\Model;

use App\Transactions\Domain\Contracts\RegistryInterface;

readonly class Commerce implements RegistryInterface
{
    private const TRIM_WHITE_SPACES = '/\s+/';

    /** @var array<string, mixed> */
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function getRawData(): array
    {
        return $this->data;
    }

    public function getCode(): ?string
    {
        return array_key_exists('code', $this->data) ? preg_replace(
            self::TRIM_WHITE_SPACES,
            '',
            $this->data['code']
        ) : null;
    }

    public function getName(): ?string
    {
        return array_key_exists('name', $this->data) ? preg_replace(
            self::TRIM_WHITE_SPACES,
            ' ',
            $this->data['name']
        ) : null;
    }

    public function getCsb(): ?string
    {
        return array_key_exists('csb', $this->data) ? preg_replace(
            self::TRIM_WHITE_SPACES,
            ' ',
            $this->data['csb']
        ) : null;
    }

    public function getCifNif(): ?string
    {
        return array_key_exists('cif_nif', $this->data) ? preg_replace(
            self::TRIM_WHITE_SPACES,
            ' ',
            $this->data['cif_nif']
        ) : null;
    }

    public function getArea(): ?string
    {
        return array_key_exists('area', $this->data) ? preg_replace(
            self::TRIM_WHITE_SPACES,
            ' ',
            $this->data['area']
        ) : null;
    }

    public function getProvince(): ?string
    {
        return array_key_exists('province', $this->data) ? preg_replace(
            self::TRIM_WHITE_SPACES,
            ' ',
            $this->data['province']
        ) : null;
    }

    public function getAddress(): ?string
    {
        return array_key_exists('address', $this->data) ? preg_replace(
            self::TRIM_WHITE_SPACES,
            ' ',
            $this->data['address']
        ) : null;
    }

    public function getSectorInt(): ?string
    {
        return array_key_exists('sector_int', $this->data) ? preg_replace(
            self::TRIM_WHITE_SPACES,
            ' ',
            $this->data['sector_int']
        ) : null;
    }

    public function getSectorAct(): ?string
    {
        return array_key_exists('sector_act', $this->data) ? preg_replace(
            self::TRIM_WHITE_SPACES,
            ' ',
            $this->data['sector_act']
        ) : null;
    }
}
