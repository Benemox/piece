<?php

namespace App\AccessToken\Infrastructure\Symfony\Controller;

use App\AccessToken\Application\Command\UpdateAccessToken\UpdateAccessTokenRoleCommand;
use App\AccessToken\Domain\Model\Role;
use App\AccessToken\Infrastructure\Symfony\Http\Request\UpdateRolePayload;
use App\Shared\Infrastructure\Symfony\Controller\AbstractAPIController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class UpdateRoleController extends AbstractAPIController
{
    /**
     * @throws \Throwable
     */
    #[OA\Patch(
        description: 'Change the token role',
        summary: 'Change token role',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
            ),
            new OA\Response(response: 406, description: 'Invalid role'),
            new OA\Response(response: 500, description: 'Internal Server Error'),
        ]
    )]
    #[OA\Tag(name: 'AccessToken')]
    #[Route('/api/v1/token', name: 'update_token_role', methods: 'PATCH')]
    public function __invoke(
        #[MapRequestPayload] UpdateRolePayload $payload
    ): JsonResponse {
        $this->isRoleAccessGranted(Role::roleAdmin());

        $this->dispatch(new UpdateAccessTokenRoleCommand(
            token: $payload->token,
            role: Role::cast($payload->role)
        ));

        return $this->success(['token updated successfully']);
    }
}
