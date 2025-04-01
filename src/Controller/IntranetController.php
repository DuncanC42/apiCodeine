<?php

namespace App\Controller;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Comptes admins")
 */
class IntranetController extends AbstractController
{
    /**
     * @Route("/intranet/login", name="intranet_login", methods={"POST"})
     * @OA\Post(
     *     path="/intranet/login",
     *     summary="Login to the intranet",
     *     description="Authenticates an admin user and returns a JWT token for intranet access",
     *     operationId="intranetLogin",
     *     @OA\RequestBody(
     *         description="Admin credentials",
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", example="admin@example.com"),
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
     *             @OA\Property(property="error", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
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
