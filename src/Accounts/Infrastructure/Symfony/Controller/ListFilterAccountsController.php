<?php

namespace App\Accounts\Infrastructure\Symfony\Controller;

use App\AccessToken\Domain\Model\Role;
use App\Accounts\Application\Query\ListAccountsQuery;
use App\Accounts\Infrastructure\Symfony\Model\Request\ListAccountsParameters;
use App\Accounts\Infrastructure\Symfony\Model\Response\AccountSchema;
use App\Shared\Infrastructure\Symfony\Controller\AbstractAPIController;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1/accounts', name: 'list_filter_accounts', methods: ['GET'], priority: 1001, format: 'json')]
class ListFilterAccountsController extends AbstractAPIController
{
    #[OA\Get(
        description: 'List and filter accounts',
        summary: 'List and filter accounts',
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
                            property: 'page',
                            type: 'int',
                            example: 1
                        ),
                        new OA\Property(
                            property: 'count',
                            type: 'int',
                            example: 10
                        ),
                        new OA\Property(
                            property: 'results',
                            type: 'array',
                            items: new OA\Items(
                                ref: new Model(type: AccountSchema::class)
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
    public function __invoke(
        #[MapQueryString] ?ListAccountsParameters $parameters
    ): JsonResponse {
        $this->isRoleAccessGranted(Role::roleConsultant());

        if (null !== $parameters) {
            $this->validator->validate($parameters);
        } else {
            $parameters = new ListAccountsParameters();
        }

        $response = $this->dispatch(
            new ListAccountsQuery(
                $parameters->getFilters(),
                $parameters->getPage()
            )
        );

        return $this->success($response);
    }
}
