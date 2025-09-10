<?php

namespace App\Controller;

use App\Entity\Inbox;
use App\Entity\Review;
use App\Entity\User;
use App\Repository\MessageRepository;
use App\Repository\MovieRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RequestStack;

final class AdminController extends AbstractController
{

    private UserRepository $userRepository;
    private MovieRepository $movieRepository;


    public function __construct(UserRepository $userRepository, MovieRepository $movieRepository)
    {
        $this->userRepository = $userRepository;
        $this->movieRepository = $movieRepository;
    }

    #[Route('/administration', name: 'administration')]
    public function admin(RequestStack $requestStack, UserRepository $userRepository, ReviewRepository $reviewRepository, MovieRepository $movieRepository): Response
    {
        $bannedUsers = $userRepository->findBy(['is_banned' => 1]);
        $users = $userRepository->findAll();
        $nonVerifiedUsers = $userRepository->findBy(['verified' => 0]);
        $reviews = $this->movieRepository->findBy([], ['id' => 'DESC']);
        $roles = ['ROLE_ADMIN'];


        $admins = $userRepository->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->getQuery()
            ->getResult();

        return $this->render('admin.html.twig', [
            'bannedUsers' => $bannedUsers,
            'users' => $users,
            'nonVerifiedUsers' => $nonVerifiedUsers,
            'reviews' => $reviews,
            'admins' => $admins,
            'ROLE_ADMIN' => $roles,


        ]);
    }

    #[Route('/administration/ban/{id}', name: 'admin_ban_user')]
    public function banUser(User $user, EntityManagerInterface $em): Response
    {
        $user->setBanned(true);
        $em->flush();

        $this->addFlash('success', $user->getUsername() . ' a été banni.');
        return $this->redirectToRoute('administration');
    }

    #[Route('/administration/unban/{id}', name: 'admin_unban_user')]
    public function unbanUser(User $user, EntityManagerInterface $em): Response
    {
        $user->setBanned(false);
        $em->flush();

        $this->addFlash('success', $user->getUsername() . ' a été débanni.');
        return $this->redirectToRoute('administration');
    }

    #[Route('/administration/promote/{id}', name: 'admin_confirm_promotion')]
    public function promote(User $user, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        $user->setRoles(['ROLE_ADMIN']);
        $em->flush();

        return $this->redirectToRoute('administration', [
            'users' => $users,


        ]);
    }


    #[Route('/administration/cancel-promote/{id}', name: 'admin_cancel_promotion')]
    public function unpromote(User $user, EntityManagerInterface $em): Response
    {
        $user->setRoles(['ROLE_USER']);
        $em->flush();

        return $this->redirectToRoute('administration');
    }

    #[Route('/administration/warn/{id}', name: 'admin_warn_user', methods: ["POST"])]
    public function setwarn(User $user, EntityManagerInterface $entityManager, MessageRepository $messageRepository,int $id,UserRepository $userRepository, Request $request): Response
    {
        $user->setWarn($user->getWarn() + 1);

        $warn_reason = $request->request->get('warn_reason');;

        $warn_reasons = $user->getWarnReason();
        $sender = $userRepository->find($this->getUser()->getId());
        $reasonsArray = $warn_reasons ? explode(',', $warn_reasons) : [];
        $reasonsArray[] = $warn_reason;
        $newWarnReasons = implode(',', $reasonsArray);

        $user->setWarnReason($newWarnReasons);
        $message = $messageRepository->find($id);
        $message->setSender($sender);

        $email = $request->request->get('email');;

        // Partie message Inbox
        $receiver = $userRepository->findOneBy(['email' => $email]);
        $message = new Inbox();
        $message->setSender($sender);
        $message->setReceiver($receiver);
        $message->setSubject("Avertissement");
        $message->setBody($warn_reason);
        $message->setCreatedAt(new \DateTimeImmutable());



        $mail = new PHPMailer(true);

        try {
            // Paramètres SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'drissmoviecritique@gmail.com';
            $mail->Password = 'yrcxwyejprzobuuk'; // mot de passe application Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('drissmoviecritique@gmail.com', 'Code de sécurité');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Votre code de vérification';
            $mail->AddEmbeddedImage('img/icon.png', 'logo');
            $mailBody = "
<div style='max-width:700px; margin:20px 0;'>
  <div style='background:#020061; padding:35px; border-top-left-radius:10px; border-top-right-radius:10px;'></div>
  <div style='background:#f2f2f2; padding:15px; text-align:center;'>
    <div style='height:20px; background:#fff; border-radius:20px; width:60%; margin:auto;'></div>
  </div>
  <div style='padding:20px;'>
    <table style='width:100%; margin-bottom:20px; border-collapse:collapse;'>
      <tr>
        <td style='width:50px; vertical-align:top;'>
          <div style='width:75px; height:75px; background:#020061; border-radius:50%;'>
            <img src='cid:logo' style='width:100%; height:100%; border-radius:50%;'>
          </div>
        </td>
        <td style='vertical-align:middle; padding-left:10px;'>
          <p style='margin:0; font-size:14px;'><b>De :</b> Support</p>
          <p style='margin:0; font-size:14px;'><b>Sujet :</b> Avertissement sur votre compte</p>
        </td>
      </tr>
    </table>
    <p style='font-size:16px; font-weight:bold; color:#3f51b5; margin:0;'>Vous avez recu un avertissement sur votre compte pour :</p>
    <p style='font-size:15px; margin-top:15px;'>$warn_reason</p>
    <p style='font-size:15px;'>3 avertissement sur notre site et votre compte sera cloturé. 
    </p>
    </p>
  </div>
  <div style='background:#f2f2f2; padding:15px; text-align:right; border-bottom-left-radius:10px; border-bottom-right-radius:10px;'></div>
</div>
";
            $mail->Body = $mailBody;

            $mail->send();
        } catch (\Exception $e) {
            dump($e);
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('administration', [
                'body' => $message->getBody(),
            ]);
        }
        $entityManager->flush();
        dump($entityManager);

        $this->addFlash('success', 'Inscription réussie ! Connectez-vous pour verifier votre compte');
        return $this->redirectToRoute('administration');

    }

    #[Route('/administration/delete-review/{reviewId}', name: 'admin_delete_review', methods: ['GET'])]
    public function deleteReview(
        EntityManagerInterface $em,
        int $reviewId
    ): Response {
        $conn = $em->getConnection();

        // Supprime la review correspondant à l'ID
        $sql = 'DELETE FROM movie WHERE id = :reviewId';
        $stmt = $conn->prepare($sql);
        $stmt->executeStatement(['reviewId' => $reviewId]);

        return new Response('Review deleted', 200);
    }


    #[Route('/admin/promote-user/{id}', name: 'admin_promote_user', methods: ['GET'])]
    public function showPromoteConfirmation(
        UserRepository $userRepository,
        int $id
    ): Response {
        $userToPromote = $userRepository->find($id);

        if (!$userToPromote) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        return $this->render('admin.html.twig', [
            'userToPromote' => $userToPromote,
            'userIdToPromote' => $userToPromote->getId(),
        ]);
    }
}