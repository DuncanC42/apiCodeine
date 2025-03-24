<?php

namespace App\Security;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use App\Entity\User;

class ApiAuthenticator extends AbstractGuardAuthenticator
{
    private $entityManager;
    private $jwtManager;

    public function __construct(EntityManagerInterface $entityManager, JWTTokenManagerInterface $jwtmanager)
    {
        $this->entityManager = $entityManager;
        $this->jwtManager = $jwtmanager;
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has('Authorization') && str_contains($request->headers->get('Authorization'), 'Bearer ');
    }

    public function getCredentials(Request $request): array
    {
        $data = json_decode($request->getContent(), true);
        return [
            'pseudo' =>$data['pseudo'] ?? null,
            'email' => $data['email'] ?? null,
            'date_creation' => $data['date_creation'] ?? null
        ];
    }

    /*
    public function getCredentials(Request $request)
    {

         $authorizationHeader = $request->headers->get('Authorization');

         if (!$authorizationHeader || !preg_match('/Bearer (.+)/', $authorizationHeader, $matches)) {
            return null;
        }

        return $matches[1];
         
    }
    */

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $pseudo = $credentials['pseudo'];
        $email = $credentials['email'];
        $date_creation = $credentials['date_creation'];

        if (!$pseudo || !$email) {
            return null;
        }

        return $this->entityManager->getRepository(User::class)->findOneBy([
            'pseudo' => $pseudo,
            'email' => $email,
            'date_creation' => $date_creation
        ]);
    }

    /*

    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        if (!$credentials) {
            return null;
        }

        return $userProvider->loadUserByUsername($credentials);
    }

    */

    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    public function start(Request $request, AuthenticationException $authException = null): JsonResponse
    {
        $message = $authException ? $authException->getMessageKey() : 'Authentication Required';

        return new JsonResponse([
            'message' => $message
        ], Response::HTTP_UNAUTHORIZED);
    }

    /*
        public function start(Request $request, AuthenticationException $authException = null): Response
        {
            return new Response("Authentication required", Response::HTTP_UNAUTHORIZED);
        }
    */

    public function supportsRememberMe()
    {
        return false;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $jwt = $this->jwtManager->create($token->getUser());
        return new JsonResponse(['token' => $jwt]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse([
            'message' => $exception->getMessage()
        ], Response::HTTP_UNAUTHORIZED);
    }
}
