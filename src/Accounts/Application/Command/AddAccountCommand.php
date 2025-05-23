<?php

namespace App\Accounts\Application\Command;

use App\Shared\Domain\Bus\AsyncMessageInterface;

class AddAccountCommand implements AsyncMessageInterface
{
    public function __construct(
        public string $accountId,
        public string $accountName,
        public string $memberName,
        public string $memberSurname,
        public string $cif,
        public string $organizationName,
        public string $organizationId,
        public string $productName,
        public string $productCode,
        public string $productId,
        public string $customerId,
        public ?string $clientCode,
    ) {
    }
}
