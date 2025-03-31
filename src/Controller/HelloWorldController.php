<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloWorldController extends AbstractController
{
    /**
     * @Route("/hello/world", name="app_hello_world")
     */
    public function index(): Response
    {
        return new JsonResponse([
            'message' => 'Hello, World!',
        ]);
    }

    /**
     * @Route("/api/hello", name="token_extra_hello")
     */
    public function test1Token(): Response
    {
        return new JsonResponse([
            'message' => 'Hello, World!',
        ]);
    }

    /** 
     * @Route("/intranet/hello", name="token_intra_hello")
     */
    public function test2Token(): Response
    {
        return new JsonResponse([
            'message' => 'Hello, World!',
        ]);
    }
}
