<?php

namespace App\AccessToken\Infrastructure\Symfony\Controller;

use App\AccessToken\Application\Command\RemoveAccessToken\RemoveAccessTokenCommand;
use App\AccessToken\Domain\Model\Role;
use App\AccessToken\Infrastructure\Symfony\Http\Request\RemoveTokenPayload;
use App\Shared\Infrastructure\Symfony\Controller\AbstractAPIController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class RemoveAccessTokenController extends AbstractAPIController
{
    /**
     * @throws \Throwable
     */
    #[OA\Delete(
        description: 'Remove a user from the middleware system',
        summary: 'Remove user',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
            ),
            new OA\Response(response: 406, description: 'Invalid Id'),
            new OA\Response(response: 500, description: 'Internal Server Error'),
        ]
    )]
    #[OA\Tag(name: 'AccessToken')]
    #[Route('/api/v1/token', name: 'remove_token', methods: 'DELETE')]
    public function __invoke(#[MapRequestPayload] RemoveTokenPayload $payload): JsonResponse
    {
        $this->isRoleAccessGranted(Role::roleAdmin());

        $this->dispatch(new RemoveAccessTokenCommand(
            token: $payload->token
        ));

        return $this->success(['AccessToken deleted successfully.']);
    }
}
