<?php

namespace App\Controller;

use App\Entity\Inbox;
use App\Entity\Message;
use App\Entity\User;
use App\Repository\InboxRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class InboxController extends AbstractController
{
    #[Route('/inbox', name: 'app_inbox')]
    public function inbox(
        InboxRepository $messageRepository,
        SessionInterface $session
    ): Response {
        /** @var User $user */
        $user = $this->getUser(); // Récupère l’utilisateur connecté via le système de sécurité

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à la boîte de réception.');
        }

        // Récupération des messages reçus
        $messages = $messageRepository->findBy(
            ['receiver' => $user],
            ['createdAt' => 'DESC']
        );

        return $this->render('profile/inbox.html.twig', [
            'user' => $user,
            'messages' => $messages,
        ]);
    }

    #[Route('/inbox/send', name: 'app_send_message', methods: ['POST'])]
    public function sendMessage(
        Request $request,
        UserRepository $userRepository,
        InboxRepository $messageRepository,
        SessionInterface $session
    ): Response {
        /** @var User $sender */
        $sender = $this->getUser();

        if (!$sender) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour envoyer un message.');
        }

        $receiverUsername = $request->request->get('receiver');
        $subject = $request->request->get('subject');
        $body = $request->request->get('body');

        $receiver = $userRepository->findOneBy(['username' => $receiverUsername]);

        if (!$receiver) {
            $session->set('inbox_message', "Destinataire introuvable.");
            return $this->redirectToRoute('app_inbox');
        }
        $message = new Inbox();
        $message->setSender($sender);
        $message->setReceiver($receiver);
        $message->setSubject($subject);
        $message->setBody($body);
        $message->setCreatedAt(new \DateTimeImmutable());

        $messageRepository->save($message, true);

        $session->set('inbox_message', "Message envoyé avec succès !");
        return $this->redirectToRoute('app_inbox');
    }


    #[Route('/message/{id}', name: 'app_message_get', methods: ['GET'])]
    public function getMessage(int $id, InboxRepository $messageRepository): JsonResponse
    {
        $message = $messageRepository->find($id);

        if (!$message) {
            return $this->json(['error' => 'Message introuvable'], 404);
        }

        // Vérifier que l'utilisateur connecté est bien le destinataire
        $user = $this->getUser();
        if ($message->getReceiver() !== $user) {
            return $this->json(['error' => 'Accès refusé'], 403);
        }

        return $this->json([
            'subject' => $message->getSubject(),
            'body' => $message->getBody(),
            'sender' => $message->getSender()->getUsername(),
            'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i')
        ]);
    }
}
