<?php

namespace App\Controller;

use App\Repository\ScoreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * @Route("/api")
 */
class StatsController extends AbstractController
{
    /**
     * @Route("/stats", name="get_stats")
     * @Method("GET")
     */
    public function getStats(ScoreRepository $scoreRepository): JsonResponse
    {
        $user = $this->getUser(); // Récupération de l'utilisateur connecté

        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $scores = $scoreRepository->findBy(['player' => $user]); // Filtrage des scores de l'utilisateur connecté
        $stats = [];

        foreach ($scores as $score) {
            $jeuId = $score->getJeu()->getId();

            // On stocke directement l'objet au lieu d'un tableau
            $stats[$jeuId] = [
                'id' => $score->getId(),
                'nb_essais' => $score->getNbEssais(),
                'temps_jeu' => $score->getTempsJeu(),
                'points' => $score->getPoints(),
            ];
        }

        return new JsonResponse($stats);
    }
}
