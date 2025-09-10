<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class HomecontrollerController extends AbstractController
{
    private UserRepository $userRepository;
    private MovieRepository $movieRepository;

    public function __construct(UserRepository $userRepository, MovieRepository $movieRepository)
    {
        $this->userRepository = $userRepository;
        $this->movieRepository = $movieRepository;
    }

    #[Route('/', name: 'home')]
    public function home(): Response
    {
        $users = $this->userRepository->findAll();
        $reviews = $this->movieRepository->findBy([], ['id' => 'DESC']);

        $critiquesCount = [];
        foreach ($users as $user) {
            $critiquesCount[$user->getId()] = $this->movieRepository->count(['user' => $user]);
        }

        return $this->render('index.html.twig', [
            'users' => $users,
            'reviews' => $reviews,
            'critiquesCount' => $critiquesCount,
            'currentUser' => $this->getUser(), // utilisateur connecté
        ]);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('home/contact.html.twig', [
            'currentUser' => $this->getUser(),
        ]);
    }

    #[Route('/catalogues', name: 'catalogues')]
    public function catalogues(): Response
    {
        return $this->render('home/catalogues.html.twig', [
            'currentUser' => $this->getUser(),
        ]);
    }

    #[Route('/faq', name: 'faq')]
    public function faq(): Response
    {
        return $this->render('home/faq.html.twig', [
            'currentUser' => $this->getUser(),
        ]);
    }

    #[Route('/loginregisterform', name: 'loginregisterform')]
    public function loginRegister(AuthenticationUtils $authenticationUtils): Response
    {
        $lastUsername = $authenticationUtils->getLastUsername();
        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('loginregisterform.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/profile', name: 'profile')]
    public function profile(): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('loginregisterform');
        }

        return $this->render('profile.html.twig', [
            'user' => $user,
        ]);
    }
}
