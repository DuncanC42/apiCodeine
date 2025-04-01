<?php

namespace App\Controller;

use App\Entity\Token;
use App\Form\TokenType;
use App\Repository\TokenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * @Route("api/tokens")
 * @OA\Tag(name="Tokens")
 */
class TokenController extends AbstractController
{
    /**
     * @Route("/", name="app_token_index", methods={"GET"})
     * @OA\Get(
     *     path="/api/tokens/",
     *     summary="Get all tokens",
     *     description="Returns a list of all tokens",
     *     operationId="getAllTokens",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="tokens", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     security={{"Bearer": {}}}
     * )
     */
    public function index(TokenRepository $tokenRepository): Response
    {
        return $this->json([
            'tokens' => $tokenRepository->findAll(),
        ]);
    }

    /**
     * @Route("/", name="app_token_new", methods={"POST"})
     * @OA\Post(
     *     path="/api/tokens/",
     *     summary="Create a new token",
     *     description="Creates a new token with the provided data",
     *     operationId="createToken",
     *     @OA\RequestBody(
     *         description="Token data",
     *         required=true,
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Token created successfully",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid input",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="string")
     *         )
     *     ),
     *     security={{"Bearer": {}}}
     * )
     */
    public function new(Request $request, TokenRepository $tokenRepository): Response
    {
        $data = json_decode($request->getContent(), true); // Decode JSON data
        $token = new Token();
        $form = $this->createForm(TokenType::class, $token, ['csrf_protection' => false]);
        $form->submit($data); // Submit the decoded data to the form

        if ($form->isSubmitted() && $form->isValid()) {
            $tokenRepository->add($token);
            return $this->json($token, Response::HTTP_CREATED);
        }

        return $this->json([
            'errors' => (string) $form->getErrors(true, false),
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/{id}", name="app_token_show", methods={"GET"})
     * @OA\Get(
     *     path="/api/tokens/{id}",
     *     summary="Get token by ID",
     *     description="Returns a single token by ID",
     *     operationId="getTokenById",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the token to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Token not found"
     *     ),
     *     security={{"Bearer": {}}}
     * )
     */
    public function show(Token $token): Response
    {
        return $this->json($token);
    }

    /**
     * @Route("/{id}", name="app_token_delete", methods={"DELETE"})
     * @OA\Delete(
     *     path="/api/tokens/{id}",
     *     summary="Delete a token",
     *     description="Deletes a token by ID",
     *     operationId="deleteToken",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the token to delete",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Token deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Token not found"
     *     ),
     *     security={{"Bearer": {}}}
     * )
     */
    public function delete(Request $request, Token $token, TokenRepository $tokenRepository): Response
    {
        $tokenRepository->remove($token);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
