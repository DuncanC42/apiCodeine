<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Comptes joueurs")
 */
class AuthController extends AbstractController
{
    /**
     * @Route("/api/login", name="api_login", methods={"POST"})
     * @OA\Post(
     *     path="/api/login",
     *     summary="Login to the API",
     *     description="Authenticates a user and returns a JWT token",
     *     operationId="apiLogin",
     *     @OA\RequestBody(
     *         description="User credentials",
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", example="user@example.com"),
     *             @OA\Property(property="password", type="string", example="password")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful authentication",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Authentication failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Authentication failed")
     *         )
     *     )
     * )
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
