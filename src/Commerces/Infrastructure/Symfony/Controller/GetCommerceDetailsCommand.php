<?php

namespace App\Commerces\Infrastructure\Symfony\Controller;

use App\AccessToken\Domain\Model\Role;
use App\Commerces\Application\Query\GetCommerceDetailsQuery;
use App\Commerces\Infrastructure\Symfony\Model\Response\CommerceDetailsSchema;
use App\Shared\Infrastructure\Symfony\Controller\AbstractAPIController;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Get(
    description: 'Get Commerce Details',
    summary: 'Get Commerce Details',
    parameters: [
        new OA\Parameter(name: 'commerceId', description: 'Commerce Id', in: 'path', required: true),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Get Commerce Details',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'result',
                        ref: new Model(type: CommerceDetailsSchema::class),
                        type: 'object'
                    ),
                ]
            )
        ),
    ]
)]
#[OA\Tag(name: 'Commerces')]
#[Route('/api/v1/commerces/{commerceId}', name: 'get_commerce_details', methods: ['GET'])]
class GetCommerceDetailsCommand extends AbstractAPIController
{
    /**
     * @throws \Throwable
     */
    public function __invoke(
        string $commerceId
    ): JsonResponse {
        $this->isRoleAccessGranted(Role::roleConsultant());

        $result = $this->dispatch(new GetCommerceDetailsQuery($commerceId));

        return $this->success([
            'result' => $result,
        ]);
    }
}
