<?php

namespace App\Controller;

use App\Entity\Score;
use App\Entity\Joueur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/api", name="api_leaderboard_")
 */
class LeaderboardController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var Security
     */
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * @Route("/leaderboard/taquin", name="taquin", methods={"GET"})
     */
    public function getTaquinLeaderboard(Request $request)
    {
        return $this->getGameLeaderboard(1, $request);
    }

    /**
     * @Route("/leaderboard/tirelire", name="tirelire", methods={"GET"})
     */
    public function getTirelireLeaderboard(Request $request)
    {
        return $this->getGameLeaderboard(2, $request);
    }

    /**
     * @Route("/leaderboard/dino", name="dino", methods={"GET"})
     */
    public function getDinoLeaderboard(Request $request)
    {
        return $this->getGameLeaderboard(3, $request);
    }

    /**
     * @Route("/leaderboard/fruitninja", name="fruitninja", methods={"GET"})
     */
    public function getFruitNinjaLeaderboard(Request $request)
    {
        return $this->getGameLeaderboard(4, $request);
    }

    /**
     * @Route("/leaderboard/dents", name="dents", methods={"GET"})
     */
    public function getDentsLeaderboard(Request $request)
    {
        return $this->getGameLeaderboard(5, $request);
    }

    /**
     * Get the leaderboard for a specific game
     *
     * @param int $gameId
     * @param Request $request
     * @return JsonResponse
     */
    private function getGameLeaderboard($gameId, Request $request)
    {
        // Get pagination parameters
        $limit = (int) $request->query->get('limit', 10);
        $page = (int) $request->query->get('page', 1);
        $offset = ($page - 1) * $limit;

        // Get current user
        /** @var Joueur|null $currentUser */
        $currentUser = $this->security->getUser();

        if (!$currentUser) {
            return $this->json([
                'error' => 'User not authenticated'
            ], 401);
        }

        // Get top players
        $leaderboardQuery = $this->entityManager->createQuery(
            'SELECT s.id, j.pseudo as username, s.points as score 
            FROM App\Entity\Score s 
            JOIN s.player j 
            JOIN s.jeu g
            WHERE g.id = :gameId 
            ORDER BY s.points DESC'
        )->setParameter('gameId', $gameId);

        $totalPlayers = count($leaderboardQuery->getResult());

        $leaderboard = $leaderboardQuery->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getResult();

        // Get current player's position
        $userPositionQuery = $this->entityManager->createQuery(
            'SELECT COUNT(s) + 1 
            FROM App\Entity\Score s 
            JOIN s.jeu g
            WHERE g.id = :gameId 
            AND s.points > (
                SELECT COALESCE(s2.points, 0) 
                FROM App\Entity\Score s2 
                JOIN s2.player p
                JOIN s2.jeu g2
                WHERE p.id = :userId 
                AND g2.id = :gameId
            )'
        )
            ->setParameter('gameId', $gameId)
            ->setParameter('userId', $currentUser->getId());

        $userPosition = $userPositionQuery->getSingleScalarResult();

        // Get the user's score for this game
        $userScoreQuery = $this->entityManager->createQuery(
            'SELECT COALESCE(s.points, 0) 
            FROM App\Entity\Score s 
            JOIN s.jeu g
            JOIN s.player p
            WHERE p.id = :userId 
            AND g.id = :gameId'
        )
            ->setParameter('gameId', $gameId)
            ->setParameter('userId', $currentUser->getId());

        $userScore = 0;
        try {
            $userScore = $userScoreQuery->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            // User hasn't played this game yet
        }

        // Format the response
        $response = [
            'leaderboard' => [
                'players' => $leaderboard,
                'total' => $totalPlayers,
                'page' => $page,
                'limit' => $limit,
                'totalPages' => ceil($totalPlayers / $limit)
            ],
            'currentPlayer' => [
                'username' => $currentUser->getPseudo(),
                'score' => $userScore,
                'position' => $userPosition
            ]
        ];

        return $this->json($response);
    }
}
