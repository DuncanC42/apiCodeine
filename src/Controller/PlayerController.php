<?php

namespace App\Controller;

use App\Entity\Joueur;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Joueur")
 */
class PlayerController extends AbstractController
{
    /**
     * @Route("/intranet/player/{id}", name="get_player_by_id", methods={"GET"})
     * @OA\Get(
     *     path="/intranet/player/{id}",
     *     summary="Récupérer un joueur par ID",
     *     description="Retourne les informations d'un joueur spécifique",
     *     operationId="getPlayerById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du joueur",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Joueur trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="joueur", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="pseudo", type="string", example="Joueur1"),
     *                 @OA\Property(property="email", type="string", example="joueur1@example.com"),
     *                 @OA\Property(property="derniere_connexion", type="string", example="2023-01-01 12:00:00"),
     *                 @OA\Property(property="temps_joue", type="integer", example=3600),
     *                 @OA\Property(property="nb_partage", type="integer", example=5)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Joueur non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Joueur non trouvé")
     *         )
     *     ),
     *     security={{"Bearer": {}}}
     * )
     */
    public function getPlayerById(ManagerRegistry $doctrine, int $id): Response
    {
        $joueur = $doctrine->getRepository(Joueur::class)->find($id);

        if (!$joueur) {
            return $this->json(['success' => false, 'message' => 'Joueur non trouvé'], 404);
        }

        return $this->json([
            'success' => true,
            'joueur' => [
                'id' => $joueur->getId(),
                'pseudo' => $joueur->getPseudo(),
                'email' => $joueur->getEmail(),
                'derniere_connexion' => $joueur->getDerniereConnexion()->format('Y-m-d H:i:s'),
                'temps_joue' => $joueur->getTempsJoue(),
                'nb_partage' => $joueur->getNbPartage()
            ]
        ], 200);
    }

    /**
     * @Route("/intranet/player/{id}/score", name="get_player_and_score_by_id", methods={"GET"})
     * @OA\Get(
     *     path="/intranet/player/{id}/score",
     *     summary="Récupérer un joueur et ses scores par ID",
     *     description="Retourne les informations d'un joueur spécifique avec ses scores et classements pour chaque jeu",
     *     operationId="getPlayerAndScoreById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du joueur",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Joueur et scores trouvés",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="joueur", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="pseudo", type="string", example="Joueur1"),
     *                 @OA\Property(property="email", type="string", example="joueur1@example.com"),
     *                 @OA\Property(property="derniere_connexion", type="string", example="2023-01-01 12:00:00"),
     *                 @OA\Property(property="temps_joue", type="integer", example=3600),
     *                 @OA\Property(property="nb_partage", type="integer", example=5),
     *                 @OA\Property(property="classement_general", type="number", example=3.5)
     *             ),
     *             @OA\Property(property="scores", type="array", 
     *                 @OA\Items(
     *                     @OA\Property(property="jeu_id", type="integer", example=1),
     *                     @OA\Property(property="jeu_nom", type="string", example="Jeu Test"),
     *                     @OA\Property(property="score_id", type="integer", example=42),
     *                     @OA\Property(property="points", type="integer", example=9500),
     *                     @OA\Property(property="classement", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucun score trouvé pour ce joueur",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Aucun score trouvé pour ce joueur")
     *         )
     *     ),
     *     security={{"Bearer": {}}}
     * )
     */
    public function getPlayerAndScoreById(ManagerRegistry $doctrine, int $id): Response
    {
        $entityManager = $doctrine->getManager();
        $conn = $entityManager->getConnection();

        $sql = "
    WITH Classement AS (
        SELECT 
            s.player_id,
            s.jeu_id,
            s.points,
            RANK() OVER (PARTITION BY s.jeu_id ORDER BY s.points DESC) AS classement
        FROM score s
    )
    SELECT 
        j.id AS joueur_id,
        j.pseudo,
        j.email,
        j.derniere_connexion,
        j.temps_joue,
        j.nb_partage,
        jeu.id AS jeu_id,
        jeu.nom AS jeu_nom,
        s.id AS score_id,
        s.points,
        c.classement
    FROM joueur j
    LEFT JOIN score s ON s.player_id = j.id
    LEFT JOIN jeu ON s.jeu_id = jeu.id
    LEFT JOIN Classement c ON c.player_id = j.id AND c.jeu_id = jeu.id
    WHERE j.id = :player_id
    ORDER BY jeu.id, c.classement;
    ";

        // Exécuter la requête principale
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['player_id' => $id]);
        $rankingData = $resultSet->fetchAllAssociative();

        // Vérifier si le joueur a des scores
        if (empty($rankingData)) {
            return $this->json(['success' => false, 'message' => 'Aucun score trouvé pour ce joueur'], 404);
        }

        // Requête pour récupérer le classement général
        $rankingSql = "
    WITH ranked_scores AS (
        SELECT 
            s.player_id, 
            s.jeu_id,
            RANK() OVER (PARTITION BY s.jeu_id ORDER BY s.points DESC) AS rank
        FROM score s
    )
    SELECT 
        j.id AS joueur_id, 
        j.pseudo, 
        j.email, 
        ROUND(AVG(r.rank)) AS moyenne_positions
    FROM joueur j
    LEFT JOIN ranked_scores r ON j.id = r.player_id
    WHERE j.id = :player_id
    GROUP BY j.id
    ";

        $stmt = $conn->prepare($rankingSql);
        $resultSet = $stmt->executeQuery(['player_id' => $id]);
        $globalRankingData = $resultSet->fetchAssociative();

        // Récupérer le classement général (ou null si aucune donnée)
        $globalRanking = $globalRankingData !== false ? $globalRankingData['moyenne_positions'] : null;

        return $this->json([
            'success' => true,
            'joueur' => [
                'id' => $rankingData[0]['joueur_id'],
                'pseudo' => $rankingData[0]['pseudo'],
                'email' => $rankingData[0]['email'],
                'derniere_connexion' => $rankingData[0]['derniere_connexion'],
                'temps_joue' => $rankingData[0]['temps_joue'],
                'nb_partage' => $rankingData[0]['nb_partage'],
                'classement_general' => $globalRanking
            ],
            'scores' => array_map(function ($row) {
                return [
                    'jeu_id' => $row['jeu_id'],
                    'jeu_nom' => $row['jeu_nom'],
                    'score_id' => $row['score_id'],
                    'points' => $row['points'],
                    'classement' => $row['classement']
                ];
            }, $rankingData)
        ]);
    }


    /**
     * @Route("intranet/players/by/rank", name="get_players_ranking", methods={"GET"})
     * @OA\Get(
     *     path="/intranet/players/by/rank",
     *     summary="Récupérer le classement des joueurs",
     *     description="Retourne la liste des joueurs classés par leur moyenne de positions dans tous les jeux",
     *     operationId="getPlayersRanking",
     *     @OA\Response(
     *         response=200,
     *         description="Classement des joueurs récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="count", type="integer", example=10),
     *             @OA\Property(property="joueurs", type="array", 
     *                 @OA\Items(
     *                     @OA\Property(property="joueur_id", type="integer", example=1),
     *                     @OA\Property(property="pseudo", type="string", example="Joueur1"),
     *                     @OA\Property(property="email", type="string", example="joueur1@example.com"),
     *                     @OA\Property(property="moyenne_positions", type="number", example=2.5)
     *                 )
     *             )
     *         )
     *     ),
     *     security={{"Bearer": {}}}
     * )
     */
    public function retrievePlayersRanking(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

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
            j.pseudo, 
            j.email, 
            COALESCE(AVG(r.rank), NULL) AS moyenne_positions
        FROM joueur j
        LEFT JOIN ranked_scores r ON j.id = r.player_id
        GROUP BY j.id
        ORDER BY moyenne_positions ASC NULLS LAST
        ";

        $conn = $entityManager->getConnection();
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $classementGlobal = $resultSet->fetchAllAssociative();

        return $this->json([
            'success' => true,
            'count' => count($classementGlobal),
            'joueurs' => $classementGlobal
        ]);
    }

    /**
     * @Route("intranet/players/by/id", name="get_players_ranking_by_id", methods={"GET"})
     * @OA\Get(
     *     path="/intranet/players/by/id",
     *     summary="Récupérer le classement des joueurs par ID",
     *     description="Retourne la liste des joueurs avec leur classement, triés par ID",
     *     operationId="getPlayersRankingById",
     *     @OA\Response(
     *         response=200,
     *         description="Classement des joueurs récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="count", type="integer", example=10),
     *             @OA\Property(property="joueurs", type="array", 
     *                 @OA\Items(
     *                     @OA\Property(property="joueur_id", type="integer", example=1),
     *                     @OA\Property(property="pseudo", type="string", example="Joueur1"),
     *                     @OA\Property(property="email", type="string", example="joueur1@example.com"),
     *                     @OA\Property(property="moyenne_positions", type="number", example=2.5),
     *                     @OA\Property(property="classement", type="integer", example=3)
     *                 )
     *             )
     *         )
     *     ),
     *     security={{"Bearer": {}}}
     * )
     */
    public function retrievePlayersRankingById(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $sql = "
        WITH ranked_scores AS (
            SELECT 
                s.player_id, 
                s.jeu_id,
                RANK() OVER (PARTITION BY s.jeu_id ORDER BY s.points DESC) AS rank
            FROM score s
        ),
        player_ranks AS (
            SELECT 
                j.id AS joueur_id, 
                j.pseudo, 
                j.email, 
                COALESCE(AVG(r.rank), NULL) AS moyenne_positions
            FROM joueur j
            LEFT JOIN ranked_scores r ON j.id = r.player_id
            GROUP BY j.id
        )
        SELECT joueur_id, pseudo, email, moyenne_positions,
               RANK() OVER (ORDER BY moyenne_positions ASC NULLS LAST) AS classement
        FROM player_ranks
        ORDER BY joueur_id ASC
        ";

        $conn = $entityManager->getConnection();
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery();
        $classementGlobal = $resultSet->fetchAllAssociative();

        return $this->json([
            'success' => true,
            'count' => count($classementGlobal),
            'joueurs' => $classementGlobal
        ]);
    }
}
