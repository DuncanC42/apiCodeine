<?php 

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Security\Htpp\Attribute\IsGranted;
use App\Entity\User;

class UserController extends AbstractController{

    /**
     * @Route("/api/me", name="api_bonjour", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_FULLY")
     */
    
    public function me(User $user): JsonResponse{
        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'pseudo' => $user->getPseudo(),
            'derniere_connexion' => $user->getDerniereConnexion()
        ]);
    }
}