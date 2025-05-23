<?php

namespace App\Transactions\Infrastructure\Symfony\Controller;

use App\AccessToken\Domain\Model\Role;
use App\Shared\Infrastructure\Symfony\Controller\AbstractAPIController;
use App\Transactions\Application\Query\GetUpOneAccountsMoneyFlowQuery;
use App\Transactions\Infrastructure\Symfony\Http\Request\UpOneMoneyFlowPayload;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Post(
    description: 'Get MSL money flow by accountIds groups',
    summary: 'Get MSL money flow by accountIds groups',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'from',
                    type: 'string',
                    format: 'date',
                    example: '2022-01-01'
                ),
                new OA\Property(
                    property: 'to',
                    type: 'string',
                    format: 'date',
                    example: '2022-01-01'
                ),
                new OA\Property(
                    property: 'organizationCif',
                    type: 'string',
                ),
                new OA\Property(
                    property: '$accountName',
                    type: 'string',
                    example: '001-Comida'
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get Msl accounts money flow by accountIds groups',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'results',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'recharge', type: 'float'),
                                new OA\Property(property: 'discharge', type: 'float'),
                            ]
                        )
                    ),
                ]
            )
        ),
    ]
)]
#[OA\Tag(name: 'Transactions')]
#[Route(path: '/api/v1/transactions/upone/money-flow', name: 'get_money_flow_from_upone', methods: ['POST'])]
class GetUpOneAccountsMoneyFlowController extends AbstractAPIController
{
    /**
     * @throws \Throwable
     */
    public function __invoke(
        #[MapRequestPayload] UpOneMoneyFlowPayload $payload,
    ): JsonResponse {
        $this->validator->validate($payload);

        $this->isRoleAccessGranted(Role::roleConsultant());

        $results = $this->dispatch(
            new GetUpOneAccountsMoneyFlowQuery(
                $payload->organizationCif,
                $payload->accountName,
                new \DateTime($payload->from),
                new \DateTime($payload->to)
            )
        );

        return $this->success([
            'results' => $results,
        ]);
    }
}
