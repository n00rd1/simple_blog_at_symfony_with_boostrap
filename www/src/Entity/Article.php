<?php

namespace App\Entity;

use App\Repository\ArticleRepository;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "bigint")]
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

    #[ORM\Column(type: "text")]
    private ?string $text = null;

    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private ?string $created_at = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;
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
