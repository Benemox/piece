<?php

namespace App\AccessToken\Infrastructure\Symfony\Controller;

use App\AccessToken\Application\Command\CreateAccessToken\CreateAccessTokenCommand;
use App\AccessToken\Domain\Model\Role;
use App\AccessToken\Infrastructure\Symfony\Http\Request\CreateAccessTokenPayload;
use App\AccessToken\Infrastructure\Symfony\Http\Response\AccessTokenSchema;
use App\Shared\Infrastructure\Symfony\Controller\AbstractAPIController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Post(
    description: 'Create a new user in the middleware system',
    summary: 'Create user',
    responses: [
        new OA\Response(
            response: 201,
            description: 'Success',
        ),
        new OA\Response(response: 406, description: 'AccessToken Exception'),
        new OA\Response(response: 500, description: 'Internal Server Error'),
    ]
)]
#[OA\Tag(name: 'AccessToken')]
#[Route('/api/v1/token', name: 'create_token', methods: 'POST')]
class CreateAccessTokenController extends AbstractAPIController
{
    /**
     * @throws \Throwable
     */
    public function __invoke(#[MapRequestPayload] CreateAccessTokenPayload $payload): JsonResponse
    {
        $this->isRoleAccessGranted(Role::roleAdmin());

        $token = $this->dispatch(new CreateAccessTokenCommand(
            role: Role::cast($payload->role)
        ));

        return $this->success(new AccessTokenSchema($token));
    }
}
