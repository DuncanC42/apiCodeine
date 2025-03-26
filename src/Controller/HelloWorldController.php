<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloWorldController extends AbstractController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("/hello/world", name="app_hello_world")
     */
    public function index(): Response
    {
        $this->logger->info('HelloWorldController: index method called.');

        return new JsonResponse([
            'message' => 'Hello, World!',
        ]);
    }

    /**
     * @Route("/api/hello", name="token_hello")
     */
    public function testToken(): Response
    {
        $this->logger->info('HelloWorldController: index method called.');

        return new JsonResponse([
            'message' => 'Hello, World!',
        ]);
    }
}