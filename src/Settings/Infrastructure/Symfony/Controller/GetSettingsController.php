<?php

namespace App\Settings\Infrastructure\Symfony\Controller;

use App\AccessToken\Domain\Model\Role;
use App\Settings\Application\Query\GetSettingsQuery;
use App\Settings\Infrastructure\Symfony\Http\Model\Response\SettingSchema;
use App\Shared\Infrastructure\Symfony\Controller\AbstractAPIController;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Settings')]
#[Route('/api/v1/settings', name: 'list_settings', methods: 'GET', format: 'json')]
class GetSettingsController extends AbstractAPIController
{
    #[OA\Get(
        description: 'Get system current settings',
        summary: 'Get system current settings',
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success',
                content: new OA\JsonContent(ref: new Model(type: SettingSchema::class))
            ),
            new OA\Response(response: 500, description: 'Internal Server Error'),
        ]
    )]
    public function __invoke(): JsonResponse
    {
        $this->isRoleAccessGranted(Role::roleAdmin());

        $settings = $this->messageBus->dispatch(new GetSettingsQuery());

        return $this->success($settings);
    }
}
