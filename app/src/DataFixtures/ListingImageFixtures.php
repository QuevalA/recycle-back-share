<?php

namespace App\DataFixtures;

use App\Entity\ListingImage;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ListingImageFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    const IMAGE = "image";

    public function getDependencies(): array
    {
        return [
            ListingFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['group1'];
    }

    public function load(ObjectManager $manager): void
    {
        $imagesUrl = array(
            "https://images.pexels.com/photos/942772/pexels-photo-942772.jpeg",
            "https://images.pexels.com/photos/13948359/pexels-photo-13948359.jpeg",
            "https://images.pexels.com/photos/296158/pexels-photo-296158.jpeg",
            "https://images.pexels.com/photos/11324519/pexels-photo-11324519.jpeg",
            "https://images.pexels.com/photos/4233114/pexels-photo-4233114.jpeg",
            "https://images.pexels.com/photos/1598508/pexels-photo-1598508.jpeg",
            "https://images.pexels.com/photos/1697220/pexels-photo-1697220.jpeg",
            "https://images.pexels.com/photos/9982284/pexels-photo-9982284.jpeg",
            "https://images.pexels.com/photos/194101/pexels-photo-194101.jpeg",
            "https://images.pexels.com/photos/3747562/pexels-photo-3747562.jpeg",
            "https://images.pexels.com/photos/623018/pexels-photo-623018.jpeg",
            "https://images.pexels.com/photos/4226876/pexels-photo-4226876.jpeg",
            "https://images.pexels.com/photos/6045028/pexels-photo-6045028.jpeg",
            "https://images.pexels.com/photos/6044926/pexels-photo-6044926.jpeg",
            "https://images.pexels.com/photos/3836671/pexels-photo-3836671.jpeg",
            "https://images.pexels.com/photos/6621472/pexels-photo-6621472.jpeg",
            "https://images.pexels.com/photos/4397833/pexels-photo-4397833.jpeg",
            "https://images.pexels.com/photos/2267633/pexels-photo-2267633.jpeg",
            "https://images.pexels.com/photos/4203070/pexels-photo-4203070.jpeg"
        );

        $listings = array();
        for ($i = 0; $this->hasReference(ListingFixtures::LISTING_ . $i); $i++) {
            $listings[] = $this->getReference(ListingFixtures::LISTING_ . $i);
        }

        foreach ($listings as $listing) {
            $numImages = rand(1, 3);
            for ($i = 0; $i < $numImages; $i++) {
                $listingImage = new ListingImage();
                $listingImage->setCreatedAt(new \DateTimeImmutable());
                $listingImage->setFkListing($listing);
                $listingImage->setImage($imagesUrl[array_rand($imagesUrl)]);
                $manager->persist($listingImage);
            }
        }

        $manager->flush();
    }
}
