<?php

namespace App\Controller;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use App\Entity\User;
use App\Entity\SecurityCode;
use App\Repository\SecurityCodeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class authcontroller extends AbstractController
{
    // Page login / inscription
    #[Route('/loginregister', name: 'loginregisterform')]
    public function loginRegister(AuthenticationUtils $authUtils): Response
    {
        $error = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();

        return $this->render('loginregisterform.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    // Traitement de l'inscription
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        ManagerRegistry $doctrine,
    ): Response {
        $entityManager = $doctrine->getManager();

        // Vérifier si email déjà utilisé
        $email = $request->request->get('email');
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        if (!$username || !$email || !$password) {
            $this->addFlash('error', 'Tous les champs sont requis.');
            return $this->redirectToRoute('loginregisterform');
        }

        $existingUser = $entityManager->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if ($existingUser) {
            $this->addFlash('error', 'Cet email est déjà utilisé.');
            return $this->redirectToRoute('loginregisterform');
        }

        // Créer l'utilisateur
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($passwordHasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_USER']);

        $entityManager->persist($user);
        $entityManager->flush();

        // Créer le code de validation
        $code = random_int(100000, 999999);
        $securityCode = new SecurityCode();
        $securityCode->setUser($user);
        $securityCode->setCode((string)$code);
        $securityCode->setType('email_verification');
        $securityCode->setExpiresAt(new \DateTimeImmutable('+15 minutes'));

        $entityManager->persist($securityCode);
        $entityManager->flush();

        // Envoyer le mail
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
            $mail->addAddress($user->getEmail(), $user->getUsername());

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
          <p style='margin:0; font-size:14px;'><b>De :</b> Sécurité</p>
          <p style='margin:0; font-size:14px;'><b>Sujet :</b> Bienvenue $username chez DRISS critique de film</p>
        </td>
      </tr>
    </table>
    <p style='font-size:16px; font-weight:bold; color:#3f51b5; margin:0;'>🌟 🙌 Merci d'avoir rejoint notre communauté ! 🙌 🌟</p>
    <p style='font-size:15px; margin-top:15px;'>Nous sommes ravis de vous compter parmi nous.</p>
    <p style='font-size:15px;'>Pour vous aider à démarrer, n'hésitez pas à consulter 
      <a href='#' style='color:#020061; text-decoration:none; font-weight:bold;'>FAQ</a> ! 😊
    </p>
    <p style='font-size:15px; margin-top:15px;'>Voici votre code de sécurité : 
      <span style='color:#020061; font-weight:bold;'>$code</span>
    </p>
  </div>
  <div style='background:#f2f2f2; padding:15px; text-align:right; border-bottom-left-radius:10px; border-bottom-right-radius:10px;'></div>
</div>
";
            $mail->Body = $mailBody;

            $mail->send();
        } catch (Exception $e) {
            $this->addFlash('error', "Le mail n'a pas pu être envoyé. Erreur : {$mail->ErrorInfo}");
            return $this->redirectToRoute('loginregisterform');
        }

        $this->addFlash('success', 'Inscription réussie ! Connectez-vous pour verifier votre compte');
        return $this->redirectToRoute('app_verify_code_form');
    }

    // Affichage du formulaire pour saisir le code
    #[Route('/verify_code/form', name: 'app_verify_code_form')]
    public function verifyCodeForm(): Response
    {
        return $this->render('security_form.html.twig');
    }

    // Traitement du code
    #[Route('/verify_code', name: 'app_verify_code', methods: ['POST'])]
    public function verifyCode(
        Request $request,
        SecurityCodeRepository $securityCodeRepository,
        ManagerRegistry $doctrine
    ): Response {
        $codeInput = $request->request->get('code');
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour vérifier le code.');
            return $this->redirectToRoute('loginregisterform');
        }

        $entityManager = $doctrine->getManager();
        $securityCode = $securityCodeRepository->findOneBy([
            'user' => $user,
            'code' => $codeInput,
            'type' => 'email_verification'
        ]);

        if (!$securityCode) {
            $request->getSession()->set('verification_message', 'Code invalide.');
            return $this->redirectToRoute('app_verify_code_form');
        }

        if ($securityCode->getExpiresAt() < new \DateTimeImmutable()) {
            $request->getSession()->set('verification_message', 'Code expiré.');
            return $this->redirectToRoute('app_verify_code_form');
        }

        $entityManager->remove($securityCode);
        $entityManager->flush();

        if ($securityCode && $securityCode->getExpiresAt() >= new \DateTimeImmutable()) {
            $user->setVerified(1);   // <-- Active le compte
            $entityManager->remove($securityCode);
            $entityManager->flush();

            $request->getSession()->set('verification_message', 'Votre compte a été vérifié !');
        }

        return $this->redirectToRoute('home');
    }

    // Renvoyer un code
    #[Route('/resend_code', name: 'app_resend_code')]
    public function resendCode(
        ManagerRegistry $doctrine,
        SecurityCodeRepository $securityCodeRepository,
    ): Response
    {
        $entityManager = $doctrine->getManager();
        $user = $entityManager->getRepository(User::class)->find($this->getUser()->getId());
        $username = $user->getUsername();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour renvoyer le code.');
            return $this->redirectToRoute('loginregisterform');
        }

        $entityManager = $doctrine->getManager();

        $code = random_int(100000, 999999);

        $existingCode = $securityCodeRepository->findOneBy([
            'user' => $user,
            'type' => 'email_verification'
        ]);

        if ($existingCode) {
            $existingCode->setCode((string)$code);
            $existingCode->setExpiresAt(new \DateTimeImmutable('+15 minutes'));
        } else {
            $newCode = new SecurityCode();
            $newCode->setUser($user);
            $newCode->setCode((string)$code);
            $newCode->setType('email_verification');
            $newCode->setExpiresAt(new \DateTimeImmutable('+15 minutes'));
            $entityManager->persist($newCode);
        }

        $entityManager->flush();

        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'drissmoviecritique@gmail.com';
            $mail->Password = 'yrcxwyejprzobuuk';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('drissmoviecritique@gmail.com', 'Code de sécurité');
            $mail->addAddress($user->getEmail(), $user->getUsername());
            $mail->AddEmbeddedImage('img/icon.png', 'logo');

            $mail->isHTML(true);
            $mail->Subject = 'Votre code de vérification';
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
          <p style='margin:0; font-size:14px;'><b>De :</b> Sécurité</p>
          <p style='margin:0; font-size:14px;'><b>Sujet :</b> Bienvenue $username chez DRISS critique de film</p>
        </td>
      </tr>
    </table>
    <p style='font-size:16px; font-weight:bold; color:#3f51b5; margin:0;'>🌟 🙌 Merci d'avoir rejoint notre communauté ! 🙌 🌟</p>
    <p style='font-size:15px; margin-top:15px;'>Nous sommes ravis de vous compter parmi nous.</p>
    <p style='font-size:15px;'>Pour vous aider à démarrer, n'hésitez pas à consulter 
      <a href='#' style='color:#020061; text-decoration:none; font-weight:bold;'>FAQ</a> ! 😊
    </p>
    <p style='font-size:15px; margin-top:15px;'>Voici votre code de sécurité : 
      <span style='color:#020061; font-weight:bold;'>$code</span>
    </p>
  </div>
  <div style='background:#f2f2f2; padding:15px; text-align:right; border-bottom-left-radius:10px; border-bottom-right-radius:10px;'></div>
</div>
";
            $mail->Body = $mailBody;

            $mail->send();
            $this->addFlash('success', 'Un nouveau code de vérification a été envoyé à votre email.');
        } catch (Exception $e) {
            $this->addFlash('error', "Le mail n'a pas pu être envoyé. Erreur : {$mail->ErrorInfo}");
        }

        return $this->redirectToRoute('app_verify_code_form');
        }


    #[Route('/login', name: 'login')]
    public function login()
    {
        // Symfony s’occupe de la connexion
    }
}
