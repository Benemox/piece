<?php

namespace App\Accounts\Domain\Model;

use App\Shared\Domain\Model\Uid;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'accounts')]
class Account
{
    public const DATE_FORMAT = 'Y-m-d H:i:s';

    public function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'string')]
        public string $id,
        #[ORM\Column(type: 'string')]
        public string $accountId,
        #[ORM\Column(type: 'string')]
        public string $accountName,
        #[ORM\Column(type: 'string')]
        public string $memberName,
        #[ORM\Column(type: 'string')]
        public string $memberSurname,
        #[ORM\Column(type: 'string')]
        public string $cif,
        #[ORM\Column(type: 'string')]
        public string $mslCustomerId,
        #[ORM\Column(type: 'string', nullable: true)]
        public ?string $updateDate,
        #[ORM\Column(type: 'string', nullable: true)]
        public ?string $organizationName = null,
        #[ORM\Column(type: 'string', nullable: true)]
        public ?string $organizationId = null,
        #[ORM\Column(type: 'string', nullable: true)]
        public ?string $clientCode = null,
        #[ORM\Column(type: 'string', nullable: true)]
        public ?string $productName = null,
        #[ORM\Column(type: 'string', nullable: true)]
        public ?string $productCode = null,
        #[ORM\Column(type: 'string', nullable: true)]
        public ?string $productId = null,
    ) {
        $this->updateDate = $this->updateDate ?? (new \DateTimeImmutable())->format(self::DATE_FORMAT);
    }

    public static function create(
        Uid $id,
        string $accountId,
        string $accountName,
        string $memberName,
        string $memberSurname,
        string $cif,
        string $mslCustomerId,
        ?string $updateDate,
        ?string $organizationName = null,
        ?string $organizationId = null,
        ?string $clientCode = null,
        ?string $productName = null,
        ?string $productCode = null,
        ?string $productId = null,
    ): self {
        return new self(
            id: $id->value(),
            accountId: $accountId,
            accountName: $accountName,
            memberName: $memberName,
            memberSurname: $memberSurname,
            cif: $cif,
            mslCustomerId: $mslCustomerId,
            updateDate: $updateDate ?? (new \DateTimeImmutable())->format(self::DATE_FORMAT),
            organizationName: $organizationName,
            organizationId: $organizationId,
            clientCode: $clientCode,
            productName: $productName,
            productCode: $productCode,
            productId: $productId
        );
    }

    public function updateWithFreshData(Account $account): void
    {
        $updatedFlag = false;

        if ($this->clientCode !== $account->clientCode) {
            $this->clientCode = $account->clientCode;
            $updatedFlag = true;
        }
        if ($this->productName !== $account->productName) {
            $this->productName = $account->productName;
            $updatedFlag = true;
        }
        if ($this->productId !== $account->productId) {
            $this->productId = $account->productId;
            $updatedFlag = true;
        }

        if ($this->mslCustomerId !== $account->mslCustomerId) {
            $this->mslCustomerId = $account->mslCustomerId;
            $updatedFlag = true;
        }

        if ($this->organizationName !== $account->organizationName) {
            $this->organizationName = $account->organizationName;
            $updatedFlag = true;
        }

        if ($this->organizationId !== $account->organizationId) {
            $this->organizationId = $account->organizationId;
            $updatedFlag = true;
        }

        if ($this->productCode !== $account->productCode) {
            $this->productCode = $account->productCode;
            $updatedFlag = true;
        }

        if ($updatedFlag) {
            $updateDate = new \DateTimeImmutable('now');
            $this->updateDate = $updateDate->format(self::DATE_FORMAT);
        }
    }

    public function getAccountId(): string
    {
        return $this->accountId;
    }

    public function getAccountName(): string
    {
        return $this->accountName;
    }

    public function getCif(): string
    {
        return $this->cif;
    }

    public function getClientCode(): ?string
    {
        if (null === $this->clientCode) {
            return null;
        }

        return (string) str_replace('.', '', $this->clientCode);
    }

    public function getMemberName(): string
    {
        return $this->memberName;
    }

    public function getMemberSurname(): string
    {
        return $this->memberSurname;
    }

    public function getFullName(): string
    {
        return $this->memberName.' '.$this->memberSurname;
    }

    public function getProductId(): ?string
    {
        return $this->productId;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function getProductCode(): ?string
    {
        return $this->productCode;
    }

    public function getOrganizationName(): ?string
    {
        return $this->organizationName;
    }

    public function getOrganizationId(): ?string
    {
        if (null === $this->organizationId) {
            return null;
        }

        return $this->organizationId;
    }

    /**
     * @throws \Exception
     */
    public function getUpdateDate(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->updateDate ?? (new \DateTimeImmutable())->format(self::DATE_FORMAT));
    }

    public function getMslCustomerId(): string
    {
        return $this->mslCustomerId;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUid(): Uid
    {
        return Uid::cast($this->id);
    }
}
