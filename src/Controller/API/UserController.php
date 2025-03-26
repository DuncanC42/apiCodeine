<?php 

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted; 
use App\Entity\User;

class UserController extends AbstractController{

    /**
     * @Route("/api/me", name="api_me", methods={"POST"})
     * 
     */
    
    public function login(User $user): JsonResponse{

        /*$user = $this->getUser();
    
        if (!$user) {
            return $this->json(['message' => 'Unauthorized'], 401);
        }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'pseudo' => $user->getPseudo(),
            'derniere_connexion' => $user->getDerniereConnexion()
        ]);
        */

        return $this->json(['message' => 'Login successful']);
    }
}