<?php

namespace App\Accounts\Infrastructure\Symfony\Controller;

use App\AccessToken\Domain\Model\Role;
use App\Accounts\Application\Query\GetAccountsOrganizationsQuery;
use App\Accounts\Infrastructure\Symfony\Model\Response\OrganizationSchema;
use App\Shared\Infrastructure\Symfony\Controller\AbstractAPIController;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/accounts/organizations', name: 'accounts_organizations', methods: ['GET'], priority: 1000, format: 'json')]
class ListOrganizationsController extends AbstractAPIController
{
    #[OA\Get(
        description: 'List organizations',
        summary: 'List organizations registered in UpOne',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'total',
                            type: 'int',
                            example: 10
                        ),
                        new OA\Property(
                            property: 'results',
                            type: 'array',
                            items: new OA\Items(
                                ref: new Model(type: OrganizationSchema::class)
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(response: 500, description: 'Internal Server Error'),
        ]
    )]
    #[OA\Tag(name: 'Accounts')]
    /**
     * @throws \Throwable
     */
    public function __invoke(): JsonResponse
    {
        $this->isRoleAccessGranted(Role::roleConsultant());

        $organizations = $this->dispatch(new GetAccountsOrganizationsQuery());

        $asOrganizationsList = array_map(static function ($organization) {
            return new OrganizationSchema($organization);
        }, $organizations);

        return $this->success([
            'total' => count($asOrganizationsList),
            'results' => $asOrganizationsList,
        ]);
    }
}
