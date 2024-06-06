<?php

namespace App\DataFixtures;

use App\Entity\UserFavoriteListing;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserFavoriteListingFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            ListingFixtures::class,
            UserFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['group1'];
    }

    public function load(ObjectManager $manager): void
    {
        $listings = [];
        for ($i = 0; $this->hasReference(ListingFixtures::LISTING_ . $i); $i++) {
            $listings[] = $this->getReference(ListingFixtures::LISTING_ . $i);
        }

        $users = [];
        for ($userIndex = 4; $this->hasReference(UserFixtures::USER_ . $userIndex); $userIndex++) {
            $users[] = $this->getReference(UserFixtures::USER_ . $userIndex);
        }

        foreach ($listings as $listing) {
            $numFavorites = rand(0, 3);
            $shuffledUsers = $users;
            shuffle($shuffledUsers);
            $selectedUsers = array_slice($shuffledUsers, 0, $numFavorites);

            foreach ($selectedUsers as $selectedUser) {
                $favorite = new UserFavoriteListing();
                $favorite->setFkListing($listing);
                $favorite->setFkUser($selectedUser);

                $manager->persist($favorite);
            }
        }

        $manager->flush();
    }
}
