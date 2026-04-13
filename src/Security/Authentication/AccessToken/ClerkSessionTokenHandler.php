<?php

namespace App\Security\Authentication\AccessToken;

use Clerk\Backend\Helpers\Jwks\AuthenticateRequest;
use Clerk\Backend\Helpers\Jwks\AuthenticateRequestOptions;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class ClerkSessionTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        #[Autowire(env: 'CLERK_SECRET_KEY')]
        private string $secretKey,
        #[Autowire(env: 'json:CLERK_AUTHORIZED_PARTIES')]
        private array $authorizedParties,
        private RequestStack $requestStack,
    ) {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        $options = new AuthenticateRequestOptions(
            secretKey: $this->secretKey,
            authorizedParties: $this->authorizedParties,
        );
        $requestState = AuthenticateRequest::authenticateRequest(
            $this->requestStack->getCurrentRequest(),
            $options,
        );
        $clerkUserId = strval($requestState->getPayload()?->sub) ?: null;

        if ($clerkUserId === null) {
            throw new BadCredentialsException('Clerk session token is invalid or expired.');
        }

        return new UserBadge($clerkUserId);
    }
}
