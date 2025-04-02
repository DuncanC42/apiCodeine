<?php

namespace App\Controller;

use App\Entity\Jeu;
use App\Entity\Joueur;
use App\Entity\Score;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayerController extends AbstractController
{
    /**
     * @Route("intranet/player", name="app_player")
     */
    public function index(): Response
    {
        return $this->render('player/index.html.twig', [
            'controller_name' => 'PlayerController',
        ]);
    }

    /**
     * @Route("intranet/players", name="get_all_players", methods={"GET"})
     */
    public function retrieveAllPlayers(): Response
    {
        $joueurRepository = $this->getDoctrine()->getRepository(Joueur::class);
        $joueurs = $joueurRepository->findAll();

        $data = [];
        foreach ($joueurs as $joueur) {
            $data[] = [
                'id' => $joueur->getId(),
                'pseudo' => $joueur->getPseudo(),
                'email' => $joueur->getEmail(),
                'derniere_connexion' => $joueur->getDerniereConnexion()->format('Y-m-d H:i:s'),
                'temps_joue' => $joueur->getTempsJoue(),
                'nb_partage' => $joueur->getNbPartage(),
                // Vous pouvez ajouter d'autres champs au besoin
            ];
        }

        return $this->json([
            'success' => true,
            'count' => count($data),
            'joueurs' => $data
        ]);
    }


    /**
     * @Route("intranet/players/by/rank", name="get_players_ranking", methods={"GET"})
     */
    public function retrievePlayersRanking(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        // Requête SQL native optimisée
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

        // Formater les données pour la réponse JSON
        $data = [];
        $rank = 1;
        foreach ($classementGlobal as $joueurData) {
            $data[] = [
                'id' => $joueurData['joueur_id'],
                'pseudo' => $joueurData['pseudo'],
                'email' => $joueurData['email'],
                'classement' => $joueurData['moyenne_positions'] !== null ? $rank : null,
                'moyenne_positions' => $joueurData['moyenne_positions'] !== null ? round($joueurData['moyenne_positions'], 2) : null
            ];

            if ($joueurData['moyenne_positions'] !== null) {
                $rank++;
            }
        }

        return $this->json([
            'success' => true,
            'count' => count($data),
            'joueurs' => $data
        ]);
    }



    /**
     * @Route("intranet/players/by/id", name="get_players_ranking_by_id", methods={"GET"})
     */
    public function retrievePlayersRankingById(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        // Requête SQL pour calculer le classement
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
