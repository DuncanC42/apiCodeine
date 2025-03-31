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
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/intranet/register", name="admin_create", methods={"POST"})
     */
    public function createAdmin(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'])) {
            return new JsonResponse(['error' => 'Email and password are required'], 400);
        }

        // Check if admin with this email already exists
        $existingAdmin = $entityManager->getRepository(Admin::class)->findOneBy(['email' => $data['email']]);
        if ($existingAdmin) {
            return new JsonResponse(['error' => 'An admin account with this email already exists'], 409);
        }

        $admin = new Admin();
        $admin->setEmail($data['email']);

        // Encode the password before saving
        $encodedPassword = $this->passwordEncoder->encodePassword($admin, $data['password']);
        $admin->setPassword($encodedPassword);

        // Debug: Log the hashed password
        error_log('Hashed Password: ' . $encodedPassword);

        $admin->setDerniereConnexion(new \DateTime());

        $entityManager->persist($admin);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Admin account created successfully'], 201);
    }
}
