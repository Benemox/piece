<?php

namespace App\Accounts\Infrastructure\Symfony\Controller;

use App\AccessToken\Domain\Model\Role;
use App\Accounts\Application\Query\GetAccountInCsvQuery;
use App\Shared\Infrastructure\Symfony\Controller\AbstractAPIController;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Get(
    description: 'Export accounts from a update date ahead in csv',
    summary: 'Export accounts from a update date ahead in csv',
    parameters: [
        new OA\Parameter(
            name: 'updateDate',
            description: 'Update cut date',
            in: 'path',
            required: true,
            schema: new OA\Schema(
                description: 'Update cut date',
                type: 'string',
                format: 'date',
                example: '2023-01-01'
            )
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get CSV from bulk register users and request cards',
            content: new OA\MediaType(
                mediaType: 'text/csv',
            )
        ),
    ]
)]
#[OA\Tag(name: 'Accounts')]
#[Route('/api/v1/accounts/exportUpdated/csv/{updateDate}', name: 'export_updated_accounts_csv', methods: ['GET'], format: 'csv')]
class ExportUpdatedAccountsCsvController extends AbstractAPIController
{
    /**
     * @throws \Throwable
     */
    public function __invoke(
        string $updateDate
    ): Response {
        $this->isRoleAccessGranted(Role::roleConsultant());

        $response = $this->dispatch(new GetAccountInCsvQuery(new \DateTimeImmutable($updateDate)));

        $response = new Response($response);
        $response->headers->set('Content-type', 'text/csv');
        $response->headers->set('Cache-Control', 'private');
        $response->headers->set(
            'Content-Disposition',
            'attachment; filename="Data_Accounts_'.date('d-m-Y H:m:i').'.csv";'
        );

        $response->sendHeaders();

        return $response;
    }
}
