<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use OpenApi\Annotations as OA;

class HelloWorldController extends AbstractController
{
    /**
     * @Route("/hello/world", name="app_hello_world")
     * @OA\Get(
     *     path="/hello/world",
     *     summary="Get a hello world message",
     *     description="Returns a simple hello world message",
     *     operationId="getHelloWorld",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Hello, World!")
     *         )
     *     )
     * )
     * @OA\Tag(name="Hello")
     */
    public function index(): Response
    {
        return new JsonResponse([
            'message' => 'Hello, World!',
        ]);
    }

    /**
     * @Route("/api/hello", name="token_hello", methods={"GET"})
     * @OA\Get(
     *     path="/api/hello",
     *     summary="Get a hello world message (API version)",
     *     description="Returns a simple hello world message from the API endpoint",
     *     operationId="getApiHello",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Hello, World!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *     ),
     *     security={{"Bearer": {}}}
     * )
     * @OA\Tag(name="Hello API")
     */
    public function testToken(): Response
    {
        return new JsonResponse([
            'message' => 'Hello, World!',
        ]);
    }
}
