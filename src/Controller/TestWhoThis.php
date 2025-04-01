<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use App\Entity\Joueur;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Annotations as OA;

class TestWhoThis extends AbstractController
{
    private $serializer;


    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Test endpoint that returns information about the authenticated joueur
     * 
     * @Route("api/whoAmI", name="api_test_token", methods={"GET"})
     * 
     * @OA\Response(
     *     response=200,
     *     description="Returns user information if authenticated",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="success", type="boolean", example=true),
     *        @OA\Property(property="user", type="object",
     *           @OA\Property(property="id", type="integer", example=1),
     *           @OA\Property(property="email", type="string", example="user@example.com"),
     *           @OA\Property(property="pseudo", type="string", example="username")
     *        ),
     *        @OA\Property(property="message", type="string", example="Token is valid")
     *     )
     * )
     * @OA\Response(
     *     response=401,
     *     description="No authenticated user found",
     *     @OA\JsonContent(
     *        type="object",
     *        @OA\Property(property="success", type="boolean", example=false),
     *        @OA\Property(property="message", type="string", example="No authenticated user found")
     *     )
     * )
     * @OA\Tag(name="Hello world")
     */
    public function testToken(): JsonResponse
    {
        /** @var Joueur|null $joueur */
        $joueur = $this->getUser();

        if (!$joueur) {
            return $this->json([
                'success' => false,
                'message' => 'No authenticated user found',
            ], 401);
        }

        // Create a safe response with only the data you want to expose
        $userData = [
            'id' => $joueur->getId(),
            'email' => $joueur->getEmail(),
            'pseudo' => $joueur->getPseudo(),
        ];

        return $this->json([
            'success' => true,
            'user' => $userData,
            'message' => 'Token is valid',
        ]);
    }
}
