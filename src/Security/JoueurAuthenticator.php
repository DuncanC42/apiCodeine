<?php

namespace App\Security;

use App\Entity\Joueur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Psr\Log\LoggerInterface;

class JoueurAuthenticator extends AbstractGuardAuthenticator
{
    private $entityManager;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->logger->info('JoueurAuthenticator: Constructed.');
    }

    public function supports(Request $request): bool
    {
        return $request->isMethod('POST') && $request->getPathInfo() === '/api/login';
    }

    public function getCredentials(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $this->logger->info('Received credentials: ' . json_encode($data));
        return [
            'email' => $data['email'] ?? null,
            'pseudo' => $data['pseudo'] ?? null,
        ];
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $email = $credentials['email'];
        $pseudo = $credentials['pseudo'];

        if (!$email || !$pseudo) {
            $this->logger->warning('Both email and pseudo are required.');
            throw new AuthenticationException('Both email and pseudo are required.');
        }

        $this->logger->info('Attempting to fetch user with email: ' . $email . ' and pseudo: ' . $pseudo);

        $user = $this->entityManager->getRepository(Joueur::class)->findOneBy([
            'email' => $email,
            'pseudo' => $pseudo,
        ]);

        if (!$user) {
            $this->logger->warning('Invalid credentials for email: ' . $email . ' and pseudo: ' . $pseudo);
            throw new AuthenticationException('Invalid credentials.');
        }

        $this->logger->info('User found: ' . $user->getEmail());

        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        return true; // No password validation needed
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        return null; // Allow the request to continue (JWT token will be generated automatically)
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['error' => $exception->getMessage()], Response::HTTP_UNAUTHORIZED);
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new JsonResponse(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe(): bool
    {
        return false; // Disable "remember me" functionality
    }
}