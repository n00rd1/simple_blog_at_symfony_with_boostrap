<?php

namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "author_id", referencedColumnName: "id", onDelete: "RESTRICT", nullable: false)]
//      Error with onUpdate
//    #[ORM\JoinColumn(name: "author_id", referencedColumnName: "id", onDelete: "RESTRICT", onUpdate: "CASCADE", nullable: false)]
    private ?int $author_id = null;

    //  ---
    //
    // ЛИБО АЛЬТЕРНАТИВНЫЙ ВАРИАНТ
    //  #[ORM\ManyToOne(targetEntity: User::class)]
    //  #[ORM\JoinColumn(nullable: false)]
    //   private User $author = null;
    //  ---

    #[ORM\ManyToOne(targetEntity: Article::class)]
    #[ORM\JoinColumn(name: "article_id", referencedColumnName: "id", onDelete: "RESTRICT", nullable: false)]
//      Error with onUpdate
//    #[ORM\JoinColumn(name: "article_id", referencedColumnName: "id", onDelete: "RESTRICT", onUpdate: "CASCADE", nullable: false)]
    private ?int $article_id = null;

    //  ---
    //
    // ЛИБО АЛЬТЕРНАТИВНЫЙ ВАРИАНТ
    //  #[ORM\ManyToOne(targetEntity: Article::class)]
    //  #[ORM\JoinColumn(nullable: false)]
    //  private Article $article = null;
    //  ---

    #[ORM\Column(type: "text")]
    private ?string $comment = null;

    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?string $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?int
    {
        return $this->author;
    }

    public function setAuthor(int $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getArticle(): ?int
    {
        return $this->article_id;
    }

    public function setArticle(int $article): self {
        $this->article_id = $article;
        return $this;
    }

    public function getComment(): ?string {
        return $this->comment;
    }

    public function setComment(string $text): self
    {
        $this->comment = $text;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }


}