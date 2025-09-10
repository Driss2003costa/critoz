<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'profile')]
    public function profile(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('settings');
        }

        return $this->render('profile/settings.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/upload', name: 'upload_profile', methods: ['POST'])]
    public function uploadProfile(Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('loginregisterform');
        }

        $file = $request->files->get('profile_picture');
        if ($file) {
            $uploadsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles';
            $filename = uniqid() . '.' . $file->guessExtension();

            try {
                $file->move($uploadsDir, $filename);
                $user->setProfilePicture('uploads/profiles/' . $filename);

                $entityManager = $doctrine->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Photo de profil mise à jour !');
            } catch (FileException $e) {
                $this->addFlash('error', 'Erreur lors de l\'upload.');
            }
        }

        return $this->redirectToRoute('profile');
    }

    #[Route('/profile/save-color', name: 'save_color', methods: ['POST'])]
    public function saveColor(Request $request, ManagerRegistry $doctrine): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('loginregisterform');
        }

        $color = $request->request->get('color');
        if ($color) {
            $user->setBannerColor($color); // ajoute un champ bannerColor dans User
            $em = $doctrine->getManager();
            $em->persist($user);
            $em->flush();
        }

        return new Response('Couleur sauvegardée !');
    }
}
