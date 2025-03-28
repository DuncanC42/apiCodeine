<?php

namespace App\Controller;

use App\Entity\Joueur;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    /**
     * @Route("/register", name="app_register", methods={"POST"})
     */
    public function register(EntityManagerInterface $manager, Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!$data || !isset($data['pseudo']) || !isset($data['email'])) {
            return new JsonResponse(['error' => 'Pseudo et email requis !'], Response::HTTP_BAD_REQUEST);
        }

        $pseudo = trim($data['pseudo']);
        $email = trim($data['email']);

        if (empty($pseudo) || empty($email)) {
            return new JsonResponse(['error' => 'Pseudo et email ne peuvent pas être vides.'], Response::HTTP_BAD_REQUEST);
        }

        $existingJoueur = $manager->getRepository(Joueur::class)->findOneBy([
            'email' => $email
        ]);

        if ($existingJoueur) {
            return new JsonResponse(['error' => 'Un joueur avec cet email existe déjà.'], Response::HTTP_CONFLICT);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse(['error' => "L'email fourni n'est pas valide."], Response::HTTP_BAD_REQUEST);
        }

        $joueur = new Joueur();
        $joueur->setPseudo($pseudo);
        $joueur->setEmail($email);
        $joueur->setDerniereConnexion(new DateTime());
        $joueur->setNbPartage(0);
        $joueur->setTempsJoue(0);

        // Sauvegarde du joueur en base
        $manager->persist($joueur);
        $manager->flush();

        return new JsonResponse([
            'message' => 'Joueur inscrit avec succès.',
            'joueur' => [
                'id' => $joueur->getId(),
                'pseudo' => $joueur->getPseudo(),
                'email' => $joueur->getEmail(),
            ]
        ], Response::HTTP_CREATED);
    }
}
