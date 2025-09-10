<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;

final class cataloguesController extends AbstractController {
    #[Route('/catalogues', name: 'catalogues')]
    public function catalogues(RequestStack $requestStack): Response
    {
        return $this->render('catalogues.html.twig', [
        ]);
    }
}
