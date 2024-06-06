<?php

namespace App\DataFixtures;

use App\Entity\Listing;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ListingFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    const LISTING_ = "listing";

    public function getDependencies(): array
    {
        return [
            ListingCategoryFixtures::class,
            SubCategoryFixtures::class,
            ListingStatusFixtures::class,
            ListingTypeFixtures::class,
            UserFixtures::class
        ];
    }

    public static function getGroups(): array
    {
        return ['group1'];
    }

    private function getSubCategoryReferences(): array
    {
        $subCategoryReferences = [];

        for ($i = 0; $this->hasReference('subcategory_' . $i . '_0'); $i++) {
            $subCategoryReferences[] = 'subcategory_' . $i . '_0';
        }

        return $subCategoryReferences;
    }

    public function load(ObjectManager $manager): void
    {
        $users = [];
        for ($i = 4; $this->hasReference(UserFixtures::USER_ . $i); $i++) {
            $users[] = $this->getReference(UserFixtures::USER_ . $i);
        }

        $listingStatusReferences = array_keys(ListingStatusFixtures::LISTING_STATUSES);
        $listingTypeReferences = array_keys(ListingTypeFixtures::LISTING_TYPES);

        $subCategoryReferences = $this->getSubCategoryReferences();

        $listings = Array();

        for ($i = 0 ;  $i < 100; $i++) {
            $listings[$i] = new Listing();

            $listings[$i]->setTitle("Fake listing #".$i);
            $listings[$i]->setDescription("Fake description #".$i." Lorem ipsum dolor sit amet elit.");
            $listings[$i]->setPostcode("69007");
            $listings[$i]->setCity("Lyon");
            $listings[$i]->setCountry("France");
            $listings[$i]->setLatitude("45.750000");
            $listings[$i]->setLongitude("4.850000");
            $listings[$i]->setFkUser($users[array_rand($users)]);

            $listingStatusReference = $listingStatusReferences[array_rand($listingStatusReferences)];
            $listingStatus = $this->getReference($listingStatusReference);
            $listings[$i]->setFkListingStatus($listingStatus);

            $listingTypeReference = $listingTypeReferences[array_rand($listingTypeReferences)];
            $listingType = $this->getReference($listingTypeReference);
            $listings[$i]->setFkListingType($listingType);

            $subcategoryReference = $subCategoryReferences[array_rand($subCategoryReferences)];
            $subcategory = $this->getReference($subcategoryReference);
            $listings[$i]->setSubCategory($subcategory);

            $manager->persist($listings[$i]);
            $this->setReference(self::LISTING_ . $i, $listings[$i]);
        }

        $manager->flush();
    }
}
