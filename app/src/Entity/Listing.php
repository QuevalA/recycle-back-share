<?php

namespace App\Entity;

use App\Repository\ListingRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ListingRepository::class)]

class Listing
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['listing:list', 'listing:item'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull]
    #[Groups(['listing:list', 'listing:item'])]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotNull]
    #[Groups(['listing:list', 'listing:item'])]
    private ?string $description = null;

    #[ORM\Column(length: 11)]
    #[Assert\NotNull]
    #[Groups(['listing:list', 'listing:item'])]
    private ?string $postcode = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotNull]
    #[Groups(['listing:list', 'listing:item'])]
    private ?string $city = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotNull]
    #[Groups(['listing:list', 'listing:item'])]
    private ?string $country = null;
    
    #[ORM\Column(length: 50)]
    #[Assert\NotNull]
    #[Groups(['listing:list', 'listing:item'])]
    private ?string $latitude = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotNull]
    #[Groups(['listing:list', 'listing:item'])]
    private ?string $longitude = null;

    #[ORM\ManyToOne]
    #[Assert\NotNull]
    #[ORM\JoinColumn(nullable: false)]
    private ?ListingStatus $fkListingStatus = null;

    #[ORM\ManyToOne]
    #[Assert\NotNull]
    #[ORM\JoinColumn(nullable: false)]
    private ?ListingType $fkListingType = null;

    #[ORM\ManyToOne]
    #[Assert\NotNull]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $fkUser = null;

    #[ORM\ManyToOne(inversedBy: 'listings')]
    #[Assert\NotNull]
    private ?SubCategory $subCategory = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function setPostcode(string $postcode): self
    {
        $this->postcode = $postcode;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getFkListingStatus(): ?ListingStatus
    {
        return $this->fkListingStatus;
    }

    public function setFkListingStatus(?ListingStatus $fkListingStatus): self
    {
        $this->fkListingStatus = $fkListingStatus;

        return $this;
    }

    public function getFkListingType(): ?ListingType
    {
        return $this->fkListingType;
    }

    public function setFkListingType(?ListingType $fkListingType): self
    {
        $this->fkListingType = $fkListingType;

        return $this;
    }

    public function getFkUser(): ?User
    {
        return $this->fkUser;
    }

    public function setFkUser(?User $fkUser): self
    {
        $this->fkUser = $fkUser;

        return $this;
    }

    public function getSubCategory(): ?SubCategory
    {
        return $this->subCategory;
    }

    public function setSubCategory(?SubCategory $subCategory): self
    {
        $this->subCategory = $subCategory;

        return $this;
    }
}
