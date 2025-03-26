<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface as JWTManager;

class ApiLoginController extends AbstractController
{
    /**
     * @Route("/api/login", name="api_login", methods={"POST", "GET"})
     */
    public function login(Request $request)
    {
        /*
        // Récupérer les données JSON
        $data = json_decode($request->getContent(), true);

        if (!isset($data['pseudo']) || !isset($data['email'])) {
            return new JsonResponse(['error' => 'Invalid request data'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $pseudo = $data['pseudo'];
        $email = $data['email'];


        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['pseudo' => $pseudo]);

        if (!$user || $user->getEmail() !== $email) {
            return new JsonResponse(['error' => 'Authentification invalide'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'message' => 'Login successful',
            'token' => $user->getApiToken()  // Assurez-vous que l'utilisateur a bien un token API
        ]);
        */

        return new JsonResponse(['message' => 'Échec de l\'authentification'], JsonResponse::HTTP_UNAUTHORIZED);
    }
}
