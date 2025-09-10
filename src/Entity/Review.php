<?php

// src/Entity/Review.php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\ReviewRepository;

#[ORM\Entity(repositoryClass: ReviewRepository::class)]
class Review
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $titleReview;

    #[ORM\Column(type: 'text')]
    private string $reviews;

    #[ORM\Column(type: 'integer')]
    private int $rating;

    // Relation ManyToOne avec Movie
    #[ORM\ManyToOne(targetEntity: Movie::class, inversedBy: "reviews")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Movie $movie = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    // --- Getters / Setters ---
    public function getId(): ?int { return $this->id; }

    public function getTitleReview(): string { return $this->titleReview; }
    public function setTitleReview(string $titleReview): self { $this->titleReview = $titleReview; return $this; }

    public function getReviews(): string { return $this->reviews; }
    public function setReviews(string $reviews): self { $this->reviews = $reviews; return $this; }

    public function getRating(): int { return $this->rating; }
    public function setRating(int $rating): self { $this->rating = $rating; return $this; }

    public function getMovie(): ?Movie { return $this->movie; }
    public function setMovie(?Movie $movie): self { $this->movie = $movie; return $this; }

    public function getUser(): ?User { return $this->user; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }
}
