<?php

namespace App\Accounts\Infrastructure\Symfony\Controller;

use App\AccessToken\Domain\Model\Role;
use App\Accounts\Application\Query\GetAccountDetailsQuery;
use App\Accounts\Infrastructure\Symfony\Model\Response\AccountSchema;
use App\Shared\Infrastructure\Symfony\Controller\AbstractAPIController;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Get(
    description: 'Get account data details',
    summary: 'Get account details',
    parameters: [
        new OA\Parameter(
            name: 'accountId',
            description: 'Account ID',
            in: 'path',
            required: true
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Success',
            content: new OA\JsonContent(ref: new Model(type: AccountSchema::class))
        ),
        new OA\Response(response: 404, description: 'Account not found'),
        new OA\Response(response: 500, description: 'Internal Server Error'),
    ]
)]
#[OA\Tag(name: 'Accounts')]
#[Route('/api/v1/accounts/{accountId}', name: 'get_account_details', methods: ['GET'], priority: 999, format: 'json')]
class GetAccountDetailsController extends AbstractAPIController
{
    /**
     * @throws \Throwable
     */
    public function __invoke(
        string $accountId
    ): JsonResponse {
        $this->isRoleAccessGranted(Role::roleConsultant());

        $response = $this->dispatch(new GetAccountDetailsQuery($accountId));

        return $this->success([
            'result' => $response,
        ]);
    }
}
