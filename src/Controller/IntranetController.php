<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class IntranetController extends AbstractController
{
    /**
     * @Route("/intranet/login", name="intranet_login", methods={"POST"})
     */
    public function login(Request $request, JWTTokenManagerInterface $jwtManager): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            // Log the issue for debugging
            error_log('IntranetController login: User is null');
            return new JsonResponse(['error' => 'Invalid credentials'], 401);
        }

        // Generate a JWT token for the authenticated user
        $token = $jwtManager->create($user);

        return new JsonResponse(['token' => $token]);
    }
}
