<?php

namespace App\Controller;

use App\Entity\Jeu;
use App\Entity\Score;
use App\Entity\Joueur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class StatusJeuxController extends AbstractController
{
    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * @Route("/api/status/jeux", name="api_status_jeux", methods={"GET"})
     */
    public function getGameStatus(): JsonResponse
    {
        // Get the current user from token
        $joueur = $this->security->getUser();
        if (!$joueur instanceof Joueur) {
            return $this->json([
                'error' => 'Utilisateur non authentifié',
            ], 401);
        }

        // Get all games
        $jeuxRepository = $this->entityManager->getRepository(Jeu::class);
        $jeux = $jeuxRepository->findAll();

        // Get user's scores
        $scoresRepository = $this->entityManager->getRepository(Score::class);
        $userScores = $scoresRepository->findBy(['player' => $joueur->getId()]);
        
        // Create a map of game IDs to scores for quick lookup
        $scoresByGame = [];
        foreach ($userScores as $score) {
            $scoresByGame[$score->getJeu()->getId()] = $score->getPoints();
        }

        // Prepare result array with game names as keys
        $result = [];
        foreach ($jeux as $jeu) {
            $nomJeu = $jeu->getNom();
            $result[$nomJeu] = isset($scoresByGame[$jeu->getId()]) && $scoresByGame[$jeu->getId()] > 0;
        }

        return $this->json($result);
    }
}
