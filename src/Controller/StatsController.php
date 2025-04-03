<?php

namespace App\Controller;

use App\Repository\ScoreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use OpenApi\Annotations as OA;

/**
 * @Route("/api")
 * @OA\Tag(name="Statistiques")
 */
class StatsController extends AbstractController
{
    /**
     * @Route("/stats", name="get_stats", methods={"GET"})
     * @Method("GET")
     * @OA\Get(
     *     path="/api/stats",
     *     summary="Obtenir les statistiques du joueur",
     *     description="Retourne les statistiques de jeu pour le joueur connecté",
     *     operationId="getPlayerStats",
     *     @OA\Response(
     *         response=200,
     *         description="Statistiques récupérées avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\AdditionalProperties(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", example=123),
     *                 @OA\Property(property="nb_essais", type="integer", example=5),
     *                 @OA\Property(property="temps_jeu", type="integer", example=300),
     *                 @OA\Property(property="points", type="integer", example=8500)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non autorisé",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Unauthorized")
     *         )
     *     ),
     *     security={{"Bearer": {}}}
     * )
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
