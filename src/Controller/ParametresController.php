<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Parametres;

class ParametresController extends AbstractController
{
    /**
     * @Route("/api/parametres", name="get_all_parametres", methods={"GET"})
     */
    public function getAllParametres(EntityManagerInterface $entityManager,SerializerInterface $serializer): JsonResponse
    {
        // Fetch all records from the Parametres entity
        $parametres = $entityManager->getRepository(Parametres::class)->findAll();

        // Serialize the entities into JSON
        $jsonContent = $serializer->serialize($parametres, 'json');

        // Return the data as a JSON response
        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/parametres", name="create_parametres", methods={"POST"})
     */
    public function createParametres(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $parametres = new Parametres();
        $parametres->setDateCloture(new \DateTime($data['date_cloture']));
        $parametres->setDateDebut(new \DateTime($data['date_debut']));

        $entityManager->persist($parametres);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Paramètres créés!'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/parametres/{id}", name="update_parametres", methods={"PUT"})
     */
    public function updateParametres(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $parametres = $entityManager->getRepository(Parametres::class)->find($id);

        if (!$parametres) {
            return new JsonResponse(['status' => 'Paramètres non trouvés!'], Response::HTTP_NOT_FOUND);
        }

        if (isset($data['date_cloture'])) {
            $parametres->setDateCloture(new \DateTime($data['date_cloture']));
        }
        if (isset($data['date_debut'])) {
            $parametres->setDateDebut(new \DateTime($data['date_debut']));
        }

        $entityManager->flush();

        return new JsonResponse(['status' => 'Paramètres mis à jour!'], Response::HTTP_OK);
    }

    /**
     * @Route("/api/parametres/{id}", name="delete_parametres", methods={"DELETE"})
     */
    public function deleteParametres(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $parametres = $entityManager->getRepository(Parametres::class)->find($id);

        if (!$parametres) {
            return new JsonResponse(['status' => 'Paramètres non trouvés!'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($parametres);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Paramètres supprimés!'], Response::HTTP_OK);
    }
}