<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class logoutController extends AbstractController
{
    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        // Symfony gère le logout automatiquement
        throw new \LogicException('Cette méthode peut rester vide.');
    }
}