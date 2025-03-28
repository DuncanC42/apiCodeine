<?php

namespace App\Controller;

use App\Entity\Admin;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{
    /**
     * @Route("/intranet/create", name="admin_create", methods={"POST"})
     */
    public function createAdmin(
        Request $request,
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Email and password are required'], 400);
        }

        $admin = new Admin();
        $admin->setEmail($data['email']);
        $hashedPassword = $passwordEncoder->encodePassword($admin, $data['password']);
        $admin->setPassword($hashedPassword);

        // Debug: Log the hashed password
        error_log('Hashed Password: ' . $hashedPassword);

        $admin->setDerniereConnexion(new \DateTime());

        $entityManager->persist($admin);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Admin account created successfully'], 201);
    }
}
