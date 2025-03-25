<?php

namespace App\Controller;

use App\Entity\Token;
use App\Form\TokenType;
use App\Repository\TokenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("api/tokens")
 */
class TokenController extends AbstractController
{
    /**
     * @Route("/", name="app_token_index", methods={"GET"})
     */
    public function index(TokenRepository $tokenRepository): Response
    {
        return $this->json([
            'tokens' => $tokenRepository->findAll(),
        ]);
    }

    /**
     * @Route("/", name="app_token_new", methods={"POST"})
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
     */
    public function show(Token $token): Response
    {
        return $this->json($token);
    }

    /**
     * @Route("/{id}", name="app_token_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Token $token, TokenRepository $tokenRepository): Response
    {
        $tokenRepository->remove($token);
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
