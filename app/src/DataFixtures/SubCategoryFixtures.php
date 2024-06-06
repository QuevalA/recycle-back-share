<?php

namespace App\DataFixtures;

use App\Entity\SubCategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class SubCategoryFixtures extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            ListingCategoryFixtures::class
        ];
    }

    public function load(ObjectManager $manager): void
    {
        // Get all previously generated listing categories
        $categories = [];
        foreach (ListingCategoryFixtures::LISTINGCATEGORIES as $key => $categoryName) {
            $listingCategory = $this->getReference('category_' . $key);
            $categories[$key] = $listingCategory;
        }

        // Define subcategories based on categories
        $subcategoriesByCategory = [
            'Maison' => ['Décoration', 'Cuisine', 'Meubles', 'Autres'],
            'Textile' => ['Vêtements', 'Tissus', 'Linge de maison', 'Chaussures', 'Autres'],
            'High-tech' => ['Informatique', 'Son', 'Image', 'Autres'],
            'Jardin' => ['Outils', 'Plantes', 'Semences', 'Autres'],
            'Sport' => ['Vêtements', 'Chaussures', 'Matériel', 'Autres'],
            'Mécanique' => ['Voiture', 'Moto', 'Maison', 'Jardin', 'Autres'],
            'Jeux & jouets' => ['Enfance', 'Jeux vidéo', 'Jeux de société', 'Autres'],
            'Culture' => ['Livres', 'Musique', 'Vidéo', 'Autres'],
        ];

        // Create subcategories for each listing category
        foreach ($categories as $key => $category) {
            $categoryName = $category->getCategory();
            $subcategories = $subcategoriesByCategory[$categoryName] ?? [];

            // Create subcategories for the current listing category
            foreach ($subcategories as $subkey => $subcategoryName) {
                $subcategory = new SubCategory();

                $subcategory->setSubcategory($subcategoryName);
                $subcategory->setFkListingCategory($category);
                $manager->persist($subcategory);
                $this->setReference('subcategory_' . $key . '_' . $subkey, $subcategory);
            }
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['group1'];
    }
}
