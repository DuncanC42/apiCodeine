<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Jeu;

class GameController extends AbstractController
{
    /**
     * @Route("/api/games", name="get_all_games", methods={"GET"})
     */
    public function getAllGames(EntityManagerInterface $entityManager, SerializerInterface $serializer): JsonResponse
    {
        // Fetch all records from the Jeu entity
        $jeux = $entityManager->getRepository(Jeu::class)->findAll();

        // Serialize the entities into JSON
        $jsonContent = $serializer->serialize($jeux, 'json');

        // Return the data as a JSON response
        return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
    }

    /**
     * @Route("/api/games", name="create_game", methods={"POST"})
     */
    public function createGame(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $jeu = new Jeu();
        $jeu->setNom($data['nom']);
        $jeu->setEtape($data['etape']);
        $jeu->setNbNiveau($data['nb_niveau']);
        $jeu->setDescription($data['description']);
        $jeu->setRegles($data['regles']);
        $jeu->setMessageFin($data['message_fin']);
        $jeu->setPhoto($data['photo']);
        $jeu->setTempsMax($data['temps_max']);

        $entityManager->persist($jeu);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Jeu créé!'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/api/games/{id}", name="update_game", methods={"PUT"})
     */
    public function updateGame(int $id, Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $jeu = $entityManager->getRepository(Jeu::class)->find($id);

        if (!$jeu) {
            return new JsonResponse(['status' => 'Jeu non trouvé!'], Response::HTTP_NOT_FOUND);
        }

        if (isset($data['nom'])) {
            $jeu->setNom($data['nom']);
        }
        if (isset($data['etape'])) {
            $jeu->setEtape($data['etape']);
        }
        if (isset($data['nb_niveau'])) {
            $jeu->setNbNiveau($data['nb_niveau']);
        }
        if (isset($data['description'])) {
            $jeu->setDescription($data['description']);
        }
        if (isset($data['regles'])) {
            $jeu->setRegles($data['regles']);
        }
        if (isset($data['message_fin'])) {
            $jeu->setMessageFin($data['message_fin']);
        }
        if (isset($data['photo'])) {
            $jeu->setPhoto($data['photo']);
        }
        if (isset($data['temps_max'])) {
            $jeu->setTempsMax($data['temps_max']);
        }

        $entityManager->flush();

        return new JsonResponse(['status' => 'Jeu mis à jour!'], Response::HTTP_OK);
    }

    /**
     * @Route("/api/games/{id}", name="delete_game", methods={"DELETE"})
     */
    public function deleteGame(int $id, EntityManagerInterface $entityManager): JsonResponse
    {
        $jeu = $entityManager->getRepository(Jeu::class)->find($id);

        if (!$jeu) {
            return new JsonResponse(['status' => 'Jeu non trouvé!'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($jeu);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Jeu supprimé!'], Response::HTTP_OK);
    }
}