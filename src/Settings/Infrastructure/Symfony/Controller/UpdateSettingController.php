<?php

namespace App\Settings\Infrastructure\Symfony\Controller;

use App\AccessToken\Domain\Model\Role;
use App\Settings\Application\Command\UpdateSettingCommand;
use App\Settings\Infrastructure\Symfony\Http\Model\Request\SettingsPayload;
use App\Settings\Infrastructure\Symfony\Http\Model\Response\SettingSchema;
use App\Shared\Infrastructure\Symfony\Controller\AbstractAPIController;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Settings')]
#[Route('/api/v1/settings', name: 'update_settings', methods: 'PATCH', format: 'json')]
class UpdateSettingController extends AbstractAPIController
{
    #[OA\Patch(
        description: 'Update settings',
        summary: 'Update settings',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                ref: new Model(type: SettingsPayload::class)
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Success. Returns all updated settings.',
                content: new OA\JsonContent(ref: new Model(type: SettingSchema::class))
            ),
            new OA\Response(response: 500, description: 'Internal Server Error'),
        ]
    )]
    public function __invoke(#[MapRequestPayload] SettingsPayload $settings): JsonResponse
    {
        $this->validator->validate($settings);

        $this->isRoleAccessGranted(Role::roleAdmin());

        $settings = $this->messageBus->dispatch(new UpdateSettingCommand($settings->settings));

        return $this->success($settings);
    }
}
