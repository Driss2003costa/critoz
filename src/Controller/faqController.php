<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;

final class faqController extends AbstractController {
    #[Route('/', name: 'faq')]
    public function index(RequestStack $requestStack): Response
    {
        $session = $requestStack->getSession();
        $username = $session->get('username');
        $isLogged = $session->has('user_id');

        $message = $username
            ? "Vous êtes connecté en tant que $username."
            : "Vous n'êtes pas connecté.";

        return $this->render('index.html.twig', [
            'title' => "Bienvenue sur notre site!",
            'message' => $message,
            'isLogged' => $isLogged,
            'username' => $username,
        ]);
    }
}
