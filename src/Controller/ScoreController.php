<?php
namespace App\Controller;

use App\Entity\Score;
use App\Entity\Joueur;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use OpenApi\Annotations as OA;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Security;

/**
 * @OA\Tag(name="Score joueurs")
 */ 
class ScoreController extends Controller
{
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    /**
     * @Route("/api/score", name="score", methods={"POST"})
     * @OA\Post(
     *     path="/api/score",
     *     summary="Enregistre ou met à jour un score",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="jeu_id", type="integer", example=1),
     *             @OA\Property(property="points", type="integer", example=100)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Score enregistré/mis à jour",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="score", type="object")
     *         )
     *     )
     * )
     */



    public function score(Request $request, EntityManagerInterface $manager)
    {
        // Réponse préliminaire pour les requêtes OPTIONS (pré-vol)
        if ($request->getMethod() === 'OPTIONS') {
            return new JsonResponse([], 200, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'POST, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization',
            ]);
        }

        // 1. Récupérer l'utilisateur connecté (Symfony 4.4)
        $user = $this->security->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Authentification requise.'], 401);
        }
        dump(get_class_methods($user));

        // 2. Décoder les données JSON
        $data = json_decode($request->getContent(), true);
        if (!$data || !isset($data['jeu_id']) || !isset($data['points'])) {
            return new JsonResponse(['error' => 'Données invalides.'], 400);
        }

        // 3. Chercher le score existant
        $scoreRepo = $manager->getRepository(Score::class);
        $existingScore = $scoreRepo->findOneBy([
            'player_id' => $user->getId(),
            'jeu_id' => $data['jeu_id']
        ]);

        // 4. Logique de mise à jour
        if ($existingScore) {
            if ($data['points'] > $existingScore->getPoints()) {
                $existingScore->setPoints($data['points']);
                $message = "Score mis à jour.";
            } else {
                return new JsonResponse([
                    'message' => 'Le score actuel est déjà meilleur.',
                    'score' => $existingScore
                ], 200);
            }
        } else {
            $existingScore = new Score();
            $existingScore->setPlayerId($user->getId());
            $existingScore->setJeuId($data['jeu_id']);
            $existingScore->setPoints($data['points']);
            $manager->persist($existingScore);
            $message = "Score enregistré.";
        }

        // 5. Sauvegarder
        $manager->flush();

        // 6. Retourner la réponse
        return new JsonResponse([
            'message' => $message,
            'score' => [
                'player_id' => $existingScore->getPlayerId(),
                'jeu_id' => $existingScore->getJeuId(),
                'points' => $existingScore->getPoints()
            ]
        ], 201);
    }
}