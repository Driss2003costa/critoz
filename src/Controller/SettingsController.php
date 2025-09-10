<?php

namespace App\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
final class SettingsController extends AbstractController {
    #[Route('/settings', name: 'settings')]
    public function settings(RequestStack $requestStack): Response
    {
        return $this->render('profile/settings.html.twig', [
        ]);
    }
}
