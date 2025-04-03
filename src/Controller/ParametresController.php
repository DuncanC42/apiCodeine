<?php

namespace App\Controller;

use App\Entity\Parametres;
use App\Repository\ParametresRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Paramètres")
 */
class ParametresController extends AbstractController
{
    private $entityManager;
    private $parametresRepository;
    private $serializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        ParametresRepository $parametresRepository,
        SerializerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->parametresRepository = $parametresRepository;
        $this->serializer = $serializer;
    }

    /**
     * @Route("api/parametres", methods={"GET"})
     * @OA\Get(
     *     path="/api/parametres",
     *     summary="Récupérer les paramètres",
     *     description="Retourne les paramètres du système (dates de début et de clôture)",
     *     operationId="getParametres",
     *     @OA\Response(
     *         response=200,
     *         description="Paramètres récupérés avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="date_debut", type="string", format="date-time", example="2023-01-01T00:00:00+00:00"),
     *             @OA\Property(property="date_cloture", type="string", format="date-time", example="2023-12-31T23:59:59+00:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Aucun paramètre trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="No parameters found")
     *         )
     *     ),
     *     security={{"Bearer": {}}}
     * )
     */
    public function getParametres(): JsonResponse
    {
        // Get the first record since there should only be one
        $parametres = $this->parametresRepository->findOneBy([], ['id' => 'ASC']);

        if (!$parametres) {
            return new JsonResponse(['message' => 'No parameters found'], Response::HTTP_NOT_FOUND);
        }

        // Serialize the parameters object to JSON
        $data = $this->serializer->serialize($parametres, 'json', [
            'circular_reference_handler' => function ($object) {
                return $object->getId();
            }
        ]);

        return new JsonResponse($data, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("intranet/parametres", methods={"POST"})
     * @OA\Post(
     *     path="/intranet/parametres",
     *     summary="Créer ou mettre à jour les paramètres",
     *     description="Crée un nouvel ensemble de paramètres ou met à jour ceux existants (une seule instance est conservée)",
     *     operationId="upsertParametres",
     *     @OA\RequestBody(
     *         description="Données des paramètres à créer/mettre à jour",
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="date_debut", type="string", format="date-time", example="2023-01-01T00:00:00+00:00", nullable=true),
     *             @OA\Property(property="date_cloture", type="string", format="date-time", example="2023-12-31T23:59:59+00:00", nullable=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Paramètres créés ou mis à jour avec succès",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="date_debut", type="string", format="date-time", example="2023-01-01T00:00:00+00:00"),
     *             @OA\Property(property="date_cloture", type="string", format="date-time", example="2023-12-31T23:59:59+00:00")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Données invalides",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="At least one parameter (date_debut or date_cloture) is required")
     *         )
     *     ),
     *     security={{"Bearer": {}}}
     * )
     */
    public function upsertParametres(Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);

        // Check if at least one of the dates is provided
        if (!isset($content['date_debut']) && !isset($content['date_cloture'])) {
            return new JsonResponse(['error' => 'At least one parameter (date_debut or date_cloture) is required'], Response::HTTP_BAD_REQUEST);
        }

        // Look for existing parameters
        $parametres = $this->parametresRepository->findOneBy([], ['id' => 'ASC']);
        
        if (!$parametres) {
            // Create new parameters if none exist
            $parametres = new Parametres();
        }

        try {
            // Only update date_debut if it's provided
            if (isset($content['date_debut'])) {
                $dateDebut = new \DateTime($content['date_debut']);
                $parametres->setDateDebut($dateDebut);
            }
            
            // Only update date_cloture if it's provided
            if (isset($content['date_cloture'])) {
                $dateCloture = new \DateTime($content['date_cloture']);
                $parametres->setDateCloture($dateCloture);
            }
            
            $this->entityManager->persist($parametres);
            $this->entityManager->flush();
            
            $data = $this->serializer->serialize($parametres, 'json', [
                'circular_reference_handler' => function ($object) {
                    return $object->getId();
                }
            ]);
            
            return new JsonResponse($data, Response::HTTP_CREATED, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
