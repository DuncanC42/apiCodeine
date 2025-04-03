<?php

namespace App\Security;

use App\Entity\Joueur;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\InvalidTokenException;

class JoueurJWTAuthenticator extends JWTTokenAuthenticator
{
    /**
     * @param array $preAuthToken
     * @param UserProviderInterface $userProvider
     * @return UserInterface
     */
    public function getUser($preAuthToken, UserProviderInterface $userProvider)
    {
        $user = parent::getUser($preAuthToken, $userProvider);
        
        // Verify that the user is a Joueur entity
        if (!$user instanceof Joueur) {
            throw new InvalidTokenException('Invalid user type');
        }
        
        return $user;
    }
    
    /**
     * Check if this authenticator supports the current request
     */
    public function supports(Request $request): bool
    {
        // Only support API routes (reject intranet routes)
        if (strpos($request->getPathInfo(), '/intranet/') === 0) {
            return false;
        }
        
        return parent::supports($request);
    }

    /**
     * Called when authentication is needed, but fails
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        return new JsonResponse(
            ['error' => 'Access denied: Player token is required for API routes'],
            Response::HTTP_FORBIDDEN
        );
    }
}
