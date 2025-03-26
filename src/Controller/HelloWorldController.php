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
     * @Route("/api/hello", name="token_hello")
     */
    public function testToken(): Response
    {
        return new JsonResponse([
            'message' => 'Hello, World!',
        ]);
    }
}