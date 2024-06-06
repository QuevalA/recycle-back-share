<?php

namespace App\Entity;

use App\Repository\MessageRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MessageRepository::class)]
class Message
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 800)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $fkUserSender = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $fkUserRecipient = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Conversation $fkConversation = null;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     */
    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTimeImmutable|null $createdAt
     */
    public function setCreatedAt(?\DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return User|null
     */
    public function getFkUserSender(): ?User
    {
        return $this->fkUserSender;
    }

    /**
     * @param User|null $fkUserSender
     */
    public function setFkUserSender(?User $fkUserSender): void
    {
        $this->fkUserSender = $fkUserSender;
    }

    /**
     * @return User|null
     */
    public function getFkUserRecipient(): ?User
    {
        return $this->fkUserRecipient;
    }

    /**
     * @param User|null $fkUserRecipient
     */
    public function setFkUserRecipient(?User $fkUserRecipient): void
    {
        $this->fkUserRecipient = $fkUserRecipient;
    }

    /**
     * @return Conversation|null
     */
    public function getFkConversation(): ?Conversation
    {
        return $this->fkConversation;
    }

    /**
     * @param Conversation|null $fkConversation
     */
    public function setFkConversation(?Conversation $fkConversation): void
    {
        $this->fkConversation = $fkConversation;
    }
}
