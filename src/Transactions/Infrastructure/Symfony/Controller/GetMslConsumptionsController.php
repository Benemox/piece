<?php

namespace App\Transactions\Infrastructure\Symfony\Controller;

use App\AccessToken\Domain\Model\Role;
use App\Shared\Infrastructure\Symfony\Controller\AbstractAPIController;
use App\Transactions\Application\Query\GetMslAccountsConsumptionsQuery;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

#[OA\Get(
    description: 'List and filter MSL transactions',
    summary: 'List and filter MSL transactions',
    parameters: [
        new OA\Parameter(
            name: 'from',
            description: 'The transactions from. Default last day.',
            in: 'query',
            required: true,
            example: '2018-01-01',
        ),
        new OA\Parameter(
            name: 'to',
            description: 'The transactions to. Default last day.',
            in: 'query',
            required: true,
            example: '2018-01-01',
        ),
        new OA\Parameter(
            name: 'accountIds',
            description: 'List of MslTransaction accountIds',
            in: 'query',
            required: true,
            schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
        ),
    ],
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
                                    type: 'float'
                                ),
                                new OA\Property(
                                    property: 'coincidenceInPayments',
                                    description: 'Coincidence index related to paid amount',
                                    type: 'float'
                                ),
                            ]
                        )
                    ),
                ]
            )
        ),
    ],
    deprecated: true
)]
#[OA\Tag(name: 'Transactions')]
#[Route(path: '/api/v1/transactions/msl/consumptions', name: 'list_msl_transaction_consumptions', methods: ['GET'])]
class GetMslConsumptionsController extends AbstractAPIController
{
    /**
     * @throws \Throwable
     */
    public function __invoke(
        #[MapQueryParameter] string $from,
        #[MapQueryParameter] string $to,
        #[MapQueryParameter] array $accountIds,
    ): JsonResponse {
        $this->isRoleAccessGranted(Role::roleConsultant());
        $results = $this->dispatch(
            new GetMslAccountsConsumptionsQuery(
                $accountIds,
                new \DateTime($from),
                new \DateTime($to)
            )
        );

        return $this->success([
            'results' => $results,
        ]);
    }
}
