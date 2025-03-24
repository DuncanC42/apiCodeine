<?php

namespace App\Controller;

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
        return $this->render('hello_world/index.html.twig', [
            'controller_name' => 'HelloWorldController',
        ]);
    }

    /**
     * @Route("/api/hello", name="api_hello_world", methods={"GET"})
     */
    public function apiHello(): JsonResponse
    {
        return $this->json(['message' => 'Hello World']);
    }


    /**
     * @Route("/api/hello", name="api_hello_world", methods={"GET"})
     */
    public function apiTest(): JsonResponse
    {
        return $this->json(['bonjour' => 'Hello World']);
    }
}
