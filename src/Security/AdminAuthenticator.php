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
        $isSupported = $request->headers->has('Authorization') && str_starts_with($request->headers->get('Authorization'), 'Bearer ');
        error_log('AdminAuthenticator supports: ' . ($isSupported ? 'Yes' : 'No'));
        return $isSupported;
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
            error_log('AdminAuthenticator getUser: No email provided');
            return null;
        }

        $user = $userProvider->loadUserByUsername($credentials['email']);

        // Ensure the user is an instance of Admin
        if (!$user instanceof \App\Entity\Admin) {
            error_log('AdminAuthenticator getUser: User is not an Admin');
            return null;
        }

        error_log('AdminAuthenticator getUser: ' . ($user ? 'Admin found' : 'Admin not found'));
        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        $isValid = $this->passwordEncoder->isPasswordValid($user, $credentials['password']);
        error_log('Password Verification: ' . ($isValid ? 'Success' : 'Failure'));
        return $isValid;
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
