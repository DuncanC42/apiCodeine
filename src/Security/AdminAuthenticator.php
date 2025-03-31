<?php

namespace App\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AdminAuthenticator extends AbstractGuardAuthenticator
{
    private $passwordEncoder;
    private $jwtManager;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, JWTTokenManagerInterface $jwtManager)
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->jwtManager = $jwtManager;
    }

    public function supports(Request $request): bool
    {
        return $request->getPathInfo() === '/intranet/login' && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        return [
            'email' => $data['email'] ?? null,
            'password' => $data['password'] ?? null,
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        if (null === $credentials['email']) {
            return null;
        }

        $user = $userProvider->loadUserByUsername($credentials['email']);

        // Ensure the user is an instance of Admin
        if (!$user instanceof \App\Entity\Admin) {
            return null;
        }

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $user = $token->getUser();
        $jwtToken = $this->jwtManager->create($user);

        return new JsonResponse(['token' => $jwtToken]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        return new JsonResponse(['error' => 'Invalid credentials'], 401);
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse(['error' => 'Authentication required'], 401);
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}
