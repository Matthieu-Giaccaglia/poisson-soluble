<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class ApiKeyAuthenticator extends AbstractAuthenticator
{
    private string $validApiKey;

    public function __construct(string $apiKey = '')
    {
        $this->validApiKey = $apiKey;
    }

    public function supports(Request $request): bool
    {
        return true;
    }

    public function authenticate(Request $request): Passport
    {
        $apiKey = $request->headers->get('X-API-KEY');

        if ($apiKey !== $this->validApiKey) {
            throw new AuthenticationException('Invalid API Key');
        }

        // Anonyme class to simulate a User.
        return new SelfValidatingPassport(new UserBadge($apiKey, fn() => new class implements UserInterface {
            public function getRoles(): array
            {
                return ['ROLE_USER'];
            }
            public function eraseCredentials(): void
            {
                return;
            }
            public function getUserIdentifier(): string
            {
                return (string) '';
            }
        }));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        return new JsonResponse([
            'error' => 'Unauthorized',
            'message' => $exception->getMessage()
        ], JsonResponse::HTTP_UNAUTHORIZED);
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName
    ): ?JsonResponse {
        return null;
    }
}
