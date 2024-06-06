<?php

namespace App\DataFixtures;

use App\Entity\ListingCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ListingCategoryFixtures extends Fixture implements FixtureGroupInterface
{
    const LISTINGCATEGORIES = array("Maison", "Textile", "High-tech", "Jardin", "Sport", "Mécanique", "Jeux & jouets", "Culture");

    public function load(ObjectManager $manager): void
    {
        foreach (self::LISTINGCATEGORIES as $key => $categoryName) {
            $listingCategory = new ListingCategory();
            $listingCategory->setCategory($categoryName);

            switch ($categoryName) {
                case "Maison":
                    $image = "https://images.pexels.com/photos/1918291/pexels-photo-1918291.jpeg?auto=compress&cs=tinysrgb&w=600";
                    break;
                case "Textile":
                    $image = "https://images.pexels.com/photos/2928381/pexels-photo-2928381.jpeg?auto=compress&cs=tinysrgb&w=600";
                    break;
                case "High-tech":
                    $image = "https://images.pexels.com/photos/4316/technology-computer-chips-gigabyte.jpg?auto=compress&cs=tinysrgb&w=600";
                    break;
                case "Jardin":
                    $image = "https://images.pexels.com/photos/4750274/pexels-photo-4750274.jpeg?auto=compress&cs=tinysrgb&w=600";
                    break;
                case "Sport":
                    $image = "https://images.pexels.com/photos/4793223/pexels-photo-4793223.jpeg?auto=compress&cs=tinysrgb&w=600";
                    break;
                case "Mécanique":
                    $image = "https://images.pexels.com/photos/162553/keys-workshop-mechanic-tools-162553.jpeg?auto=compress&cs=tinysrgb&w=600";
                    break;
                case "Jeux & jouets":
                    $image = "https://images.pexels.com/photos/278918/pexels-photo-278918.jpeg?auto=compress&cs=tinysrgb&w=600";
                    break;
                case "Culture":
                    $image = "https://images.pexels.com/photos/14454202/pexels-photo-14454202.jpeg?auto=compress&cs=tinysrgb&w=600";
                    break;
                default:
                    $image = "https://images.pexels.com/photos/3735202/pexels-photo-3735202.jpeg?auto=compress&cs=tinysrgb&w=600";
            }

            $listingCategory->setCategoryImage($image);

            $manager->persist($listingCategory);
            $this->setReference('category_' . $key, $listingCategory);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['group1'];
    }
}
