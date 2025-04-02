<?php

namespace App\Controller;

use App\Entity\Admin;
use App\Entity\Token;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(name="Comptes admins")
 */
class AdminController extends AbstractController
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Route("/intranet/register", name="admin_create", methods={"POST"})
     * @OA\Post(
     *     path="/intranet/register",
     *     summary="Register a new admin account",
     *     description="Creates a new administrator account with email, password and token",
     *     operationId="createAdmin",
     *     @OA\RequestBody(
     *         description="Admin credentials",
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", example="admin@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="token", type="string", example="admin_registration_token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Admin account created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Admin account created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Email, password and token are required")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Invalid token",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Invalid registration token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Admin account already exists",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="An admin account with this email already exists")
     *         )
     *     )
     * )
     */
    public function createAdmin(
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email'], $data['password'], $data['token'])) {
            return new JsonResponse(['error' => 'Email, password and token are required'], 400);
        }

        // Verify the token exists in the database
        $token = $entityManager->getRepository(Token::class)->findOneBy(['key' => $data['token']]);
        if (!$token) {
            return new JsonResponse(['error' => 'Invalid registration token'], 403);
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
