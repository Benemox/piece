<?php

namespace App\AccessToken\Infrastructure\Symfony\Controller;

use App\AccessToken\Application\Query\ListAccessTokenQuery;
use App\AccessToken\Domain\Model\Role;
use App\AccessToken\Infrastructure\Symfony\Http\Response\AccessTokenCollection;
use App\Shared\Http\Pagination\Page;
use App\Shared\Infrastructure\Symfony\Controller\AbstractAPIController;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

class ListAccessTokenController extends AbstractAPIController
{
    /**
     * @throws \Throwable
     */
    #[OA\Get(
        description: 'List users in the middleware system',
        summary: 'List users in the middleware system',
        parameters: [
            new OA\Parameter(
                name: 'role',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['ROLE_ADMIN', 'ROLE_CONSULTANT', 'ROLE_CRM'])
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(ref: new Model(type: AccessTokenCollection::class))
            ),
            new OA\Response(response: 500, description: 'Internal Server Error'),
        ]
    )]
    #[OA\Tag(name: 'AccessToken')]
    #[Route('/api/v1/token', name: 'list_users', methods: 'GET')]
    public function __invoke(
        #[MapQueryParameter] ?string $role = null,
        #[MapQueryParameter] int $page = 1,
        #[MapQueryParameter] int $count = 10,
    ): JsonResponse {
        $this->isRoleAccessGranted(Role::roleAdmin());

        $users = $this->dispatch(
            new ListAccessTokenQuery(
                role: isset($role) ? Role::cast($role) : null,
                page: new Page($page, $count)
            )
        );

        return $this->success($users);
    }
}
