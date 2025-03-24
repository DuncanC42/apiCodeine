<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    /**
     * @Route("/api/games", name="games", methods={"GET"})
     */
    public function apiHello(): JsonResponse
    {
        return $this->json(['taquin' => 'Taquin']);
    }
}
