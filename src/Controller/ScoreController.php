<?php

namespace App\Controller;

use App\Entity\Score;
use App\Entity\Joueur;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Util\Json;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ScoreController extends AbstractController
{
    /**
     * @Route("/score", name="save_score" , methods={"POST"})
     */
    public function saveScore(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $score = new Score();
        $score->setValue($data['value']);
        $score->setUser($this->getUser()); // Assure-toi d'avoir un système d'authentification
        
        $em->persist($score);
        $em->flush();

        return $this->json(['status' => 'score saved']);
    }

    public function getScores(): JsonResponse
    {
        $scores = $this->getDoctrine()
            ->getRepository(Score::class)
            ->findBy([], ['value' => 'DESC'], 10); // Top 10 scores
        
        return $this->json($scores);
    }
}