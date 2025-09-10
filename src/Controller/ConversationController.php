<?php
namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ConversationController extends AbstractController
{
    #[Route('/conversation/create', name: 'conversation_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $receiverUsername = $request->request->get('receiver');
        $sender = $this->getUser();
        $content = $request->request->get('body');

        if (!$sender) {
            return new JsonResponse(['error' => 'Utilisateur non connecté'], 401);
        }
        if (!$receiverUsername) {
            return new JsonResponse(['error' => 'Destinataire requis'], 400);
        }
        if (!$content) {
            return new JsonResponse(['error' => 'Message vide'], 400);
        }

        $receiver = $em->getRepository(User::class)->findOneBy(['username' => $receiverUsername]);
        if (!$receiver) {
            return new JsonResponse(['error' => 'Utilisateur introuvable'], 404);
        }

        // Toujours recharger le sender comme entité Doctrine
        $sender = $em->getRepository(User::class)->find($sender->getId());

        // Vérifier si conversation existante
        $existingConversation = $em->getRepository(Conversation::class)
            ->createQueryBuilder('c')
            ->join('c.participants', 'p')
            ->where('p.id IN (:userIds)')
            ->setParameter('userIds', [$sender->getId(), $receiver->getId()])
            ->groupBy('c.id')
            ->having('COUNT(c.id) = 2')
            ->getQuery()
            ->getOneOrNullResult();

        $conversation = $existingConversation ?? new Conversation();

        if ($existingConversation) {
            // Ajouter directement le message dans la conv existante
            $message = new Message();
            $message->setConversation($existingConversation);
            $message->setSender($sender);
            $message->setBody($content);

            $em->persist($message);
            $em->flush();

            if (!$existingConversation) {
                $conversation->addParticipant($sender);
                $conversation->addParticipant($receiver);
                $em->persist($conversation);
                $em->flush();
            }

            // Créer le message
            $message = new Message();
            $message->setConversation($conversation);
            $message->setSender($sender);
            $message->setBody($content);

            $em->persist($message);
            $em->flush();

            return new JsonResponse([
                'conversation_id' => $conversation->getId(),
                'message_id' => $message->getId(),
                'body' => $message->getBody(),
                'sender' => $message->getSender()->getUsername(),
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
            ]);
        }
    }


    #[Route('/conversation/{id}', name: 'load_conversation', methods: ['GET'])]
    public function load(int $id, EntityManagerInterface $em): JsonResponse
    {
        $conversation = $em->getRepository(Conversation::class)->find($id);

        if (!$conversation) {
            return new JsonResponse(['error' => 'Conversation introuvable'], 404);
        }

        // Vérification que l'utilisateur connecté est bien participant
        $currentUser = $this->getUser();
        if (!$conversation->getParticipants()->contains($currentUser)) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        // Récupérer les messages
        $messages = [];
        foreach ($conversation->getMessages() as $message) {
            $messages[] = [
                'id'        => $message->getId(),
                'body'      => $message->getBody(),
                'sender'    => [
                    'id'       => $message->getSender()->getId(),
                    'username' => $message->getSender()->getUsername(),
                    'profilePicture' => $message->getSender()->getProfilePicture() ?? '/img/profile-user.png'

                ],
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }

        return new JsonResponse([
            'conversation_id' => $conversation->getId(),
            'participants'    => array_map(fn($p) => $p->getUsername(), $conversation->getParticipants()->toArray()),
            'messages'        => $messages,
        ]);
    }



}
