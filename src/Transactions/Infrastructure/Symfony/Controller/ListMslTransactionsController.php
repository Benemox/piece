<?php

namespace App\Transactions\Infrastructure\Symfony\Controller;

use App\AccessToken\Domain\Model\Role;
use App\Shared\Http\Pagination\Page;
use App\Shared\Infrastructure\Symfony\Controller\AbstractAPIController;
use App\Transactions\Application\Query\ListMslTransactionsQuery;
use App\Transactions\Application\Query\ListMslTransactionsQueryFilters;
use App\Transactions\Infrastructure\Symfony\Http\Response\MslTransactionSchema;
use Nelmio\ApiDocBundle\Annotation\Model;
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
            required: false,
            schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
        ),
        new OA\Parameter(
            name: 'transactionType',
            description: 'MslTransaction type. POS: Point of Sale, ECOM: Ecommerce, API: API',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', enum: ['POS', 'ECOM', 'API']),
        ),
        new OA\Parameter(
            name: 'holdFlag',
            description: 'MslTransaction holdFlag. Y: pre-accepted, N: not accepted, C: Accepted, E: Error',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', enum: ['Y', 'N', 'C', 'E']),
        ),
        new OA\Parameter(
            name: 'financialImpact',
            description: 'MslTransaction financialImpact. CR: Credit, DR: Debit',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'string', enum: ['DR', 'CR']),
        ),
        new OA\Parameter(
            name: 'cardIds',
            description: 'List of MslTransaction cardIds',
            in: 'query',
            required: false,
            schema: new OA\Schema(type: 'array', items: new OA\Items(type: 'string')),
        ),
        new OA\Parameter(
            name: 'count',
            description: 'The number of transactions per page. Default 10.',
            in: 'query',
            required: false
        ),
        new OA\Parameter(
            name: 'page',
            description: 'Page number. Default 1.',
            in: 'query',
            required: false,
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of transactions',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'results',
                        properties: [
                            new OA\Property(
                                property: 'totalElements',
                                description: 'Total number of transactions',
                                type: 'integer',
                                example: 10
                            ),
                            new OA\Property(
                                property: 'totalPages',
                                description: 'Total number of pages',
                                type: 'integer',
                                example: 1
                            ),
                            new OA\Property(
                                property: 'currentPage',
                                description: 'Current page requested',
                                type: 'integer',
                                example: 1
                            ),
                            new OA\Property(
                                property: 'data',
                                type: 'array',
                                items: new OA\Items(
                                    ref: new Model(
                                        type: MslTransactionSchema::class
                                    )
                                )
                            ),
                        ],
                    ),
                ]
            )
        ),
    ]
)]
#[OA\Tag(name: 'Transactions')]
#[Route(path: '/api/v1/transactions/msl', name: 'list_msl_transaction', methods: ['GET'])]
class ListMslTransactionsController extends AbstractAPIController
{
    /**
     * @throws \Throwable
     */
    public function __invoke(
        #[MapQueryParameter] string $from,
        #[MapQueryParameter] string $to,
        #[MapQueryParameter] ?array $accountIds,
        #[MapQueryParameter] ?string $transactionType,
        #[MapQueryParameter] ?string $accountName,
        #[MapQueryParameter] ?string $financialImpact,
        #[MapQueryParameter] ?string $holdFlag,
        #[MapQueryParameter] ?array $cardIds,
        #[MapQueryParameter] int $count = 10,
        #[MapQueryParameter] int $page = 1,
    ): JsonResponse {
        $this->isRoleAccessGranted(Role::roleConsultant());

        $page = new Page(
            $page,
            $count
        );

        $filters = $this->buildFilters(
            $from,
            $to,
            $accountIds,
            $transactionType,
            $accountName,
            $financialImpact,
            $holdFlag,
            $cardIds
        );

        $results = $this->dispatch(
            new ListMslTransactionsQuery(
                $filters,
                $page
            )
        );

        return $this->success($results);
    }

    private function buildFilters(
        string $from,
        string $to,
        ?array $accountIds,
        ?string $transactionType,
        ?string $accountName,
        ?string $financialImpact,
        ?string $holdFlag,
        ?array $cardIds
    ): ListMslTransactionsQueryFilters {
        $filters = new ListMslTransactionsQueryFilters();

        $filters->withFrom($from);
        $filters->withTo($to);

        // <----Match filters---->
        if (null !== $transactionType) {
            $filters->withTransactionType($transactionType);
        }

        if (null !== $holdFlag) {
            $filters->withHoldFlag($holdFlag);
        }

        if (null !== $accountName) {
            $filters->withAccountName($accountName);
        }

        if (null !== $financialImpact) {
            $filters->withFinancialImpact($financialImpact);
        }
        // <----Match filters---->

        // <----Multi match match filters---->
        if (null !== $accountIds) {
            $filters->withAccountId($accountIds);
        }

        if (null !== $cardIds) {
            $filters->withCardIds($cardIds);
        }
        // <----Multi match match filters---->

        return $filters;
    }
}
