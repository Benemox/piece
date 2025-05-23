<?php

namespace App\Accounts\Infrastructure\Symfony\Model\Request;

use App\Accounts\Application\Query\ListAccountsQueryFilters;
use App\Shared\Http\Pagination\Page;
use OpenApi\Attributes as OA;
use Symfony\Component\Validator\Constraints as Assert;

class ListAccountsParameters
{
    public function __construct(
        #[Assert\Type('string')]
        #[OA\Property(description: 'organization id', type: 'string', example: '4b26e6c1-0b87-4e64-aa5a-cf9ef685e35f')]
        public ?string $organizationId = null,
        #[Assert\Type('string')]
        #[OA\Property(description: 'organization cif', type: 'string', example: '06987575Z')]
        public ?string $cif = null,
        #[Assert\Type('int')]
        #[OA\Property(description: 'page', type: 'integer', example: '1')]
        public int $page = 1,
        #[Assert\Type('int')]
        #[OA\Property(description: 'count', type: 'integer', example: '10')]
        public int $count = 10
    ) {
    }

    public function getFilters(): ListAccountsQueryFilters
    {
        $filters = new ListAccountsQueryFilters();

        if (null !== $this->organizationId) {
            $filters->withOrganizationId($this->organizationId);
        }

        if (null !== $this->cif) {
            $filters->withCif($this->cif);
        }

        return $filters;
    }

    public function getPage(): Page
    {
        return new Page(
            $this->page,
            $this->count
        );
    }
}
