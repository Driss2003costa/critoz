<?php
// src/Controller/MovieController.php
namespace App\Controller;

use App\Entity\Movie;
use App\Entity\Review;
use App\Repository\MovieRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MovieController extends AbstractController
{
    private HttpClientInterface $client;
    private string $tmdbApiKey = '21a926ccdfd5aeacf7922ea1912786a2';

    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Affiche les détails d’un film et ses reviews
     */
    #[Route('/movie/{movieId}', name: 'movie_details')]
    public function details(
        int              $movieId,
        ReviewRepository $reviewRepository,
        MovieRepository $movieRepository,

    ): Response
    {
        // Récupère les infos du film depuis TMDB
        $response = $this->client->request(
            'GET',
            "https://api.themoviedb.org/3/movie/$movieId?api_key={$this->tmdbApiKey}&language=fr-FR"
        );
        $movieData = $response->toArray();
        $movie = $movieRepository->find($movieId);


        $reviews = $reviewRepository->findBy(['movieId' => $movieId], ['id' => 'DESC']);

        return $this->render('detail.html.twig', [
            'movieData' => $movieData,
            'reviews' => $reviews,
            'movieId' => $movieId,
        ]);
    }

    /**
     * Création d’une nouvelle review pour un film
     */
    #[Route('/reviews/{movieId}/new', name: 'reviews_submit', methods: ['POST'])]
    public function submitReview(
        Request                $request,
        int                    $movieId,
        EntityManagerInterface $em,
        HttpClientInterface    $client
    ): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour poster une review.');
            return $this->redirectToRoute('login');
        }

        // Récupère les infos du film via TMDB
        $response = $client->request(
            'GET',
            "https://api.themoviedb.org/3/movie/$movieId?api_key={$this->tmdbApiKey}&language=fr-FR"
        );
        $movieData = $response->toArray();

        // Crée un nouvel enregistrement (review)
        $movie = new Movie();
        $movie->setMovieId($movieId);
        $movie->setMovieTitle($movieData['title']);
        $movie->setUser($user);
        $movie->setTitleReview($request->request->get('titlereview') ?: 'Titre non renseigné');
        $movie->setReviews($request->request->get('reviews') ?: 'Contenu non renseigné');
        $movie->setRating((int)$request->request->get('rating') ?: 0);

        $em->persist($movie);
        $em->flush();

        $this->addFlash('success', 'Votre review a bien été enregistrée !');
        return $this->redirectToRoute('movie_details', ['movieId' => $movieId]);
    }

    /**
     * Liste toutes les reviews pour un film
     */
    #[Route('/reviews/{movieId}/form', name: 'reviews_form', methods: ['GET'])]
    public function reviewsForm(int $movieId): Response
    {
        return $this->render('homecontroller/reviewsform.html.twig', [
            'movieId' => $movieId,
        ]);
    }

    #[Route('/allreviews/user/{userID}', name: 'app_movie_allreviewuser', methods: ['GET'])]
    public function allreviewUser(int $userID, UserRepository $userRepository, ReviewRepository $reviewRepository, MovieRepository $movieRepository): Response
    {
        $user = $userRepository->find($userID);
        $movies = $movieRepository->findBy(['user' => $user]);
        $reviews = $movieRepository->findBy(['user' => $user], ['id' => 'DESC']);
        $bannerColor = $user->getBannerColor() ?? '#333'; // ou une valeur par défaut

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }


        return $this->render('allreview.html.twig', [
            'mode' => 'user',
            'user' => $user,
            'reviews' => $reviews,
            'movie' => $movies,
            'bannerColor' => $bannerColor

        ]);
    }
    #[Route('/allreviews/{movieId}', name: 'app_movie_allreviewbymovie', methods: ['GET'])]
    public function allreviewByMovie(
        int $movieId,
        MovieRepository $movieRepository
    ): Response {
        // Récupérer toutes les "reviews" pour ce film
        $reviews = $movieRepository->findBy(['movieId' => $movieId], ['id' => 'DESC']);

        // Extraire les utilisateurs uniques
        $users = [];
        foreach ($reviews as $review) {
            $user = $review->getUser();
            if ($user && !in_array($user, $users, true)) {
                $users[] = $user;
            }
        }

        // Couleur de banner par défaut
        $bannerColor = '#333';

        return $this->render('allreview.html.twig', [
            'mode' => 'movie',
            'reviews' => $reviews,
            'users' => $users,
            'movieId' => $movieId,
            'bannerColor' => $bannerColor,
        ]);
    }



    #[Route('/allreviews/{movieId}/{userID}', name: 'app_movie_allreviewuserbymovie', methods: ['GET'])]
    public function allreviewUserbymovie(
        int $movieId,
        int $userID,
        UserRepository $userRepository,
        MovieRepository $movieRepository
    ): Response
    {
        $user = $userRepository->find($userID);
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }

        // Récupère toutes les reviews de cet utilisateur pour ce film seulement
        $reviews = $movieRepository->findBy([
            'movieId' => $movieId,
            'user' => $user
        ], ['id' => 'DESC']);

        $bannerColor = $user->getBannerColor() ?? '#333';

        // On récupère le film pour avoir l'ID TMDB exact
        $movie = $movieRepository->findOneBy([
            'movieId' => $movieId,
            'user' => $user
        ]);

        $tmdbId = $movie ? $movie->getMovieId() : null; // <-- ID TMDB exact

        return $this->render('allreview.html.twig', [
            'mode' => 'movie',
            'user' => $user,
            'reviews' => $reviews,
            'bannerColor' => $bannerColor,
            'movieId' => $tmdbId
        ]);

    }
}