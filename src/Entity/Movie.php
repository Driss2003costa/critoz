<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\MovieRepository;
#[ORM\Entity(repositoryClass: MovieRepository::class)]
class Movie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: "movie_title", length: 255)]
    private string $movieTitle;

    #[ORM\Column(name: "title_review", length: 255, nullable: true)]
    private ?string $titleReview = null;

    #[ORM\Column(type: "text", name: "reviews", nullable: true)]
    private ?string $reviews = null;

    #[ORM\Column(type: "integer", name: "rating", nullable: true)]
    private ?int $rating = null;

    #[ORM\Column(type: "integer", name: "movie_id")]

    private int $movieId;

    #[ORM\ManyToOne(inversedBy: 'movies')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    // --- Getters & Setters ---
    public function getId(): ?int { return $this->id; }

    public function getMovieTitle(): string { return $this->movieTitle; }
    public function setMovieTitle(string $movieTitle): self { $this->movieTitle = $movieTitle; return $this; }

    public function getMovieId(): ?int { return $this->movieId; }
    public function setMovieId(int $movieId): self { $this->movieId = $movieId; return $this; }

    public function getTitleReview(): ?string { return $this->titleReview; }
    public function setTitleReview(?string $titleReview): self { $this->titleReview = $titleReview; return $this; }

    public function getReviews(): ?string { return $this->reviews; }
    public function setReviews(?string $reviews): self { $this->reviews = $reviews; return $this; }

    public function getRating(): ?int { return $this->rating; }
    public function setRating(?int $rating): self { $this->rating = $rating; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
}
