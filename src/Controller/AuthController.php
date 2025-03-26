<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class AuthController extends AbstractController
{
    /**
     * @Route("/api/login", name="api_login", methods={"POST"})
     */
    public function login(Request $request, JWTTokenManagerInterface $jwtManager)
    {
        // The user is already authenticated by the custom authenticator
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Authentication failed'], 401);
        }

        // Generate a JWT token
        $token = $jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }
}