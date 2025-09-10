<?php

namespace App\Controller;
use App\Entity\ConversationParticipant;
use App\Entity\User;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class friendsController extends AbstractController {
    #[Route('/social', name: 'social')]
    public function social(
        ConversationRepository $conversationRepository
    ): Response {
        // Récupère l'utilisateur connecté
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder à vos conversations.');
            return $this->redirectToRoute('loginregisterform');
        }

        // Récupère toutes les conversations où l'utilisateur est participant
        $conversations = $conversationRepository->findByUser($user);

        // Récupère le dernier message de chaque conversation
        $messages = [];
        foreach ($conversations as $conversation) {
            $lastMessage = $conversation->getMessages()->last(); // Doctrine Collection
            if ($lastMessage) {
                $messages[] = $lastMessage;
            }
        }

        // Envoi à Twig
        return $this->render('profile/social.html.twig', [
            'messages' => $messages,
            'conversations' => $conversations,
        ]);
    }
}