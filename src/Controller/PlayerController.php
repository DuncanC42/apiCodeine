<?php

namespace App\Controller;

use App\Entity\Joueur;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayerController extends AbstractController
{
    /**
     * @Route("/player/{id}", name="get_player_by_id", methods={"GET"})
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
     * @Route("/player/{id}/score", name="get_player_and_score_by_id", methods={"GET"})
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
     * @Route("/players/by/rank", name="get_players_ranking", methods={"GET"})
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
     * @Route("/players/by/id", name="get_players_ranking_by_id", methods={"GET"})
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
