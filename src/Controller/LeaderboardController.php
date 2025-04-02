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
     * @Route("/leaderboard/general", name="general", methods={"GET"})
     */
    public function getGeneralLeaderboard(Request $request)
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

        $conn = $this->entityManager->getConnection();
        
        // SQL query to get the average rank of players across all games
        $sql = "
        WITH ranked_scores AS (
            SELECT 
                s.player_id, 
                s.jeu_id,
                RANK() OVER (PARTITION BY s.jeu_id ORDER BY s.points DESC) AS rank
            FROM score s
        )
        SELECT 
            j.id AS joueur_id, 
            j.pseudo AS username, 
            COALESCE(AVG(r.rank), NULL) AS average_rank
        FROM joueur j
        LEFT JOIN ranked_scores r ON j.id = r.player_id
        GROUP BY j.id, j.pseudo
        ORDER BY average_rank ASC NULLS LAST
        LIMIT :limit OFFSET :offset
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stmt->bindValue('offset', $offset, \PDO::PARAM_INT);
        $resultSet = $stmt->executeQuery();
        $leaderboard = $resultSet->fetchAllAssociative();
        
        // Format the leaderboard data
        $formattedLeaderboard = [];
        $rank = $offset + 1;
        foreach ($leaderboard as $player) {
            $formattedLeaderboard[] = [
                'id' => $player['joueur_id'],
                'username' => $player['username'],
                'average_rank' => $player['average_rank'] !== null ? round((float)$player['average_rank'], 2) : null,
                'position' => $rank++
            ];
        }

        // Count total players
        $countSql = "SELECT COUNT(DISTINCT j.id) FROM joueur j";
        $totalPlayers = (int)$conn->executeQuery($countSql)->fetchOne();
        
        // Get current player's position and average rank
        $userRankSql = "
        WITH ranked_scores AS (
            SELECT 
                s.player_id, 
                s.jeu_id,
                RANK() OVER (PARTITION BY s.jeu_id ORDER BY s.points DESC) AS rank
            FROM score s
        )
        SELECT 
            j.pseudo AS username, 
            COALESCE(AVG(r.rank), NULL) AS average_rank,
            (
                SELECT COUNT(*) + 1 FROM (
                    SELECT j2.id, COALESCE(AVG(rs.rank), NULL) AS avg_rank
                    FROM joueur j2
                    LEFT JOIN ranked_scores rs ON j2.id = rs.player_id
                    GROUP BY j2.id
                    HAVING COALESCE(AVG(rs.rank), NULL) IS NOT NULL 
                    AND COALESCE(AVG(rs.rank), NULL) < (
                        SELECT COALESCE(AVG(rs2.rank), NULL) 
                        FROM ranked_scores rs2 
                        WHERE rs2.player_id = :userId
                    )
                ) AS better_players
            ) AS user_position
        FROM joueur j
        LEFT JOIN ranked_scores r ON j.id = r.player_id
        WHERE j.id = :userId
        GROUP BY j.id, j.pseudo
        ";

        $userRankStmt = $conn->prepare($userRankSql);
        $userRankStmt->bindValue('userId', $currentUser->getId(), \PDO::PARAM_INT);
        $userStats = $userRankStmt->executeQuery()->fetchAssociative();

        $userPosition = null;
        $userAvgRank = null;
        
        if ($userStats) {
            $userPosition = $userStats['average_rank'] !== null ? (int)$userStats['user_position'] : null;
            $userAvgRank = $userStats['average_rank'] !== null ? round((float)$userStats['average_rank'], 2) : null;
        }

        // Format the response
        $response = [
            'leaderboard' => [
                'players' => $formattedLeaderboard,
                'total' => $totalPlayers,
                'page' => $page,
                'limit' => $limit,
                'totalPages' => ceil($totalPlayers / $limit)
            ],
            'currentPlayer' => [
                'username' => $currentUser->getPseudo(),
                'average_rank' => $userAvgRank,
                'position' => $userPosition
            ]
        ];

        return $this->json($response);
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
            WHERE g.etape = :gameId 
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
            WHERE g.etape = :gameId 
            AND s.points > (
                SELECT COALESCE(s2.points, 0) 
                FROM App\Entity\Score s2 
                JOIN s2.player p
                JOIN s2.jeu g2
                WHERE p.id = :userId 
                AND g2.etape = :gameId
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
            AND g.etape = :gameId'
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
