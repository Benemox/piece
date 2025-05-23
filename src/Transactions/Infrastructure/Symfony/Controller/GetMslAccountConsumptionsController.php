<?php

namespace App\Transactions\Infrastructure\Symfony\Controller;

use App\AccessToken\Domain\Model\Role;
use App\Shared\Infrastructure\Symfony\Controller\AbstractAPIController;
use App\Transactions\Application\Query\GetMslAccountsConsumptionsQuery;
use App\Transactions\Infrastructure\Symfony\Http\Request\MslConsumptionsPayload;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Post(
    description: 'List and filter MSL transactions',
    summary: 'List and filter MSL transactions',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(
                    property: 'accountIds',
                    description: 'List of account ids',
                    type: 'array',
                    items: new OA\Items(
                        properties: [
                            new OA\Property(property: 'id', type: 'string'),
                        ],
                        type: 'object'
                    )
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get Msl consumptions',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'results',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'accountId', type: 'string'),
                                new OA\Property(property: 'cardId', type: 'string'),
                                new OA\Property(property: 'recharge', type: 'float'),
                                new OA\Property(property: 'discharge', type: 'float'),
                                new OA\Property(property: 'paid', type: 'float'),
                                new OA\Property(property: 'calculatedPaid', type: 'float'),
                                new OA\Property(property: 'lastBalance', type: 'float'),
                                new OA\Property(property: 'lastBalanceCalculated', type: 'float'),
                                new OA\Property(property: 'posTransactionsCount', type: 'int'),
                                new OA\Property(property: 'ecomTransactionsCount', type: 'int'),
                                new OA\Property(property: 'apiTransactionCount', type: 'int'),
                                new OA\Property(
                                    property: 'coincidenceInLastBalance',
                                    description: 'Coincidence index related to last balance',
                                    type: 'float',
                                    deprecated: true
                                ),
                                new OA\Property(
                                    property: 'coincidenceInPayments',
                                    description: 'Coincidence index related to paid amount',
                                    type: 'float',
                                    deprecated: true
                                ),
                                new OA\Property(
                                    property: 'coincidence',
                                    description: 'Coincidence index related to payments. Cero represents full coincidence',
                                    type: 'float'
                                ),
                            ]
                        )
                    ),
                ]
            )
        ),
    ]
)]
#[OA\Tag(name: 'Transactions')]
#[Route(path: '/api/v1/transactions/msl/consumptions', name: 'list_msl_account_consumptions', methods: ['POST'])]
class GetMslAccountConsumptionsController extends AbstractAPIController
{
    /**
     * @throws \Throwable
     */
    public function __invoke(
        #[MapRequestPayload] MslConsumptionsPayload $payload
    ): JsonResponse {
        $this->validator->validate($payload);

        $this->isRoleAccessGranted(Role::roleConsultant());

        $results = $this->dispatch(
            new GetMslAccountsConsumptionsQuery(
                $payload->accountIds,
                new \DateTime($payload->from),
                new \DateTime($payload->to)
            )
        );

        return $this->success([
            'results' => $results,
        ]);
    }
}
