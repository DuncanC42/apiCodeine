<?php
namespace App\Controller;

use App\Entity\Score;
use App\Entity\Jeu;
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
    private $entityManager;

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



    public function score(Request $request, EntityManagerInterface $em)
    {
        $logData = [
            'time' => date('Y-m-d H:i:s'),
            'user' => $this->getUser() ? $this->getUser()->getId() : null,
            'request' => $request->getContent(),
            'decoded' => json_decode($request->getContent(), true)
        ];
    
        file_put_contents('score_debug.log', print_r($logData, true)."\n\n", FILE_APPEND);
        try {
            // 1. Authentification
            $user = $this->getUser();
            if (!$user) {
                return new JsonResponse(['error' => 'Authentification requise'], 401);
            }
    
            // 2. Récupération des données
            $data = json_decode($request->getContent(), true);
            if (!$data || !isset($data['jeu_id']) || !isset($data['points'])) {
                return new JsonResponse(['error' => 'Données invalides - jeu_id et points requis'], 400);
            }
    
            // 3. Vérification que le jeu existe
            $jeu = $em->getRepository(Jeu::class)->findOneBy(['etape' => $data['jeu_id']]);
            if (!$jeu) {
                return new JsonResponse(['error' => 'Jeu introuvable'], 404);
            }
    
            // 4. Recherche d'un score existant
            $existingScore = $em->getRepository(Score::class)->findOneBy([
                'player' => $user,
                'jeu' => $jeu
            ]);
    
            // 5. Logique de création/mise à jour
            if ($existingScore) {
                // Compare les scores (on garde le plus élevé)
                if ($data['points'] > $existingScore->getPoints()) {
                    $existingScore->setPoints($data['points']);
                    $message = "Score mis à jour";
                } else {
                    return new JsonResponse([
                        'message' => 'Score existant déjà meilleur',
                        'score_id' => $existingScore->getId(),
                        'points' => $existingScore->getPoints()
                    ], 200);
                }
            } else {
                // Crée un nouveau score
                $existingScore = new Score();
                $existingScore->setPoints($data['points']);
                $existingScore->setJeu($jeu);
                $existingScore->setPlayer($user);
                $existingScore->setTempsJeu(0); // Valeur par défaut
                $existingScore->setNbEssais(1); // Valeur par défaut
                $message = "Score enregistré";
            }
    
            // 6. Sauvegarde
            $em->persist($existingScore);
            $em->flush();
    
            return new JsonResponse([
                'message' => $message,
                'score_id' => $existingScore->getId(),
                'points' => $existingScore->getPoints(),
                'jeu' => $jeu->getNom()
            ], 201);
    
        } catch (\Exception $e) {
            $logData['error'] = $e->getMessage();
            $logData['trace'] = $e->getTraceAsString();
            file_put_contents('score_errors.log', print_r($logData, true)."\n\n", FILE_APPEND);
            
            return new JsonResponse([
                'error' => 'Erreur serveur',
                'details' => 'Voir les logs serveur' // En production, retirez les détails
            ], 500);
        }
    }
}