<?php

namespace App\Controller;

use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Security;

class SocialController
{

    #[Route('/message/{id}', name: 'app_message_show', methods: ['GET'])]
    public function showMessage(
        int               $id,
        MessageRepository $messageRepository,
        Security          $security
    ): Response
    {
        $user = $security->getUser(); // Utilisateur connect

        $message = $messageRepository->find($id);

        if (!$message) {
            return $this->json(['error' => 'Message introuvable.'], 404);
        }

        // Vérifie que l'utilisateur fait partie de la conversation
        $conversation = $message->getConversation();
        $participants = $conversation->getParticipants()->map(fn($p) => $p->getUser()->getId())->toArray();

        if (!in_array($user->getId(), $participants)) {
            return $this->json(['error' => 'Accès refusé.'], 403);
        }

        return $this->json([
            'id' => $message->getId(),
            'subject' => $message->getSubject(),
            'body' => $message->getBody(),
            'sender' => $message->getSender()->getUsername(),
            'createdAt' => $message->getCreatedAt()->format('c')
        ]);
    }
}