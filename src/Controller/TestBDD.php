<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class TestBDD extends AbstractController
{
    #[Route('/test-db', name: 'test_db')]
    public function testDb(ManagerRegistry $doctrine): Response
    {
        try {
            $conn = $doctrine->getConnection();
            $conn->executeQuery('SELECT 1');
            return new Response('BDD connectée ✅');
        } catch (\Exception $e) {
            return new Response('Erreur BDD ❌ : ' . $e->getMessage());
        }
    }
}
