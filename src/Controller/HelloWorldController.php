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
     * @Route("/hello/world", name="app_hello_world", methods={"GET"})
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
     * @OA\Tag(name="Hello world")
     */
    public function index(): Response
    {
        return new JsonResponse([
            'message' => 'Hello, World!',
        ]);
    }

    /**
     * @Route("/api/hello", name="token_extra_hello", methods={"GET"})
     * @OA\Get(
     *     path="/api/hello",
     *     summary="Get a hello world message from API",
     *     description="Returns a simple hello world message from the API endpoint",
     *     operationId="getExtraHello",
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
     * @OA\Tag(name="Hello world")
     */
    public function test1Token(): Response
    {
        return new JsonResponse([
            'message' => 'Hello, World!',
        ]);
    }

    /** 
     * @Route("/intranet/hello", name="token_intra_hello", methods={"GET"})
     * @OA\Get(
     *     path="/intranet/hello",
     *     summary="Get a hello world message (Intranet version)",
     *     description="Returns a simple hello world message from the intranet endpoint",
     *     operationId="getIntranetHello",
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
     * @OA\Tag(name="Hello world")
     */
    public function test2Token(): Response
    {
        return new JsonResponse([
            'message' => 'Hello, World!',
        ]);
    }
}
