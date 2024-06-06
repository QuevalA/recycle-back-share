<?php

namespace App\Entity;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\ListingCategoryRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ListingCategoryRepository::class)]
#[ApiResource]

class ListingCategory

{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $category = null;

    #[ORM\Column(length: 255)]
    private ?string $categoryImage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getCategoryImage(): ?string
    {
        return $this->categoryImage;
    }

    public function setCategoryImage(string $categoryImage): self
    {
        $this->categoryImage = $categoryImage;

        return $this;
    }
}
