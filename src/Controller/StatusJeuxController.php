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
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Completion")
 */
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
     * @OA\Get(
     *     path="/api/status/jeux",
     *     summary="Récupérer le statut des jeux",
     *     description="Retourne l'état de complétion des jeux pour le joueur authentifié",
     *     operationId="getGameStatus",
     *     @OA\Response(
     *         response=200,
     *         description="Statut des jeux récupéré avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             example={
     *                 "jeux1": true,
     *                 "jeux2": false,
     *                 "jeux3": true,
     *                 "jeux4": false
     *             },
     *             @OA\AdditionalProperties(
     *                 type="boolean",
     *                 description="Indique si le jeu a été complété (true) ou non (false)"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Utilisateur non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Utilisateur non authentifié")
     *         )
     *     ),
     *     security={{"Bearer": {}}}
     * )
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

        // Prepare result array with "jeux<etape>" as keys
        $result = [];
        foreach ($jeux as $jeu) {
            $key = 'jeux' . $jeu->getEtape();
            $result[$key] = isset($scoresByGame[$jeu->getId()]) && $scoresByGame[$jeu->getId()] > 0;
        }

        return $this->json($result);
    }
}
