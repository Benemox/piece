<?php

namespace App\Shared\Infrastructure\Symfony\Controller;

use ApiPlatform\Validator\ValidatorInterface;
use App\AccessToken\Domain\Contracts\RoleInterface;
use App\AccessToken\Domain\Model\Role;
use App\Shared\Domain\Bus\DispatcherInterface;
use App\Shared\Http\BadRequestResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class AbstractAPIController extends AbstractController
{
    public function __construct(
        protected DispatcherInterface $messageBus,
        protected ValidatorInterface $validator
    ) {
    }

    public function success(
        mixed $data = null,
        int $status = 200,
        array $headers = [],
        bool $json = false
    ): JsonResponse {
        assert($status >= 200 && $status < 300, 'invalid success status code');

        return new JsonResponse($data, $status, $headers, $json);
    }

    public function badRequest(string $message, ?array $errors = null, int $status = 400): JsonResponse
    {
        assert($status >= 400 && $status < 500, 'invalid bad request status code');

        return new JsonResponse(new BadRequestResponse('bad_request', $message, $errors), $status);
    }

    /**
     * @throws \Throwable
     */
    protected function dispatch(object $message): mixed
    {
        return $this->messageBus->dispatch($message);
    }

    protected function isRoleAccessGranted(RoleInterface $requiredRole): void
    {
        $user = $this->getUser();

        if (!$user) {
            throw new AccessDeniedHttpException('Invalid credential Access.');
        }

        $userRoles = $user->getRoles();

        if (in_array(Role::ROLE_ADMIN, $userRoles, true) || in_array($requiredRole->getValue(), $userRoles, true)) {
            return;
        }

        throw new AccessDeniedHttpException('Invalid credential Access.');
    }
}
