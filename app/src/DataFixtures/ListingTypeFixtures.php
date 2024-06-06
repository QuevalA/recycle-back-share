<?php

namespace App\DataFixtures;

use App\Entity\ListingType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ListingTypeFixtures extends Fixture implements FixtureGroupInterface
{
    const LISTING_TYPES = [
        'offre_de_don' => 'Offre de don',
        'offre_de_service' => 'Offre de service',
        'demande_de_don' => 'Demande de don',
        'demande_de_service' => 'Demande de service',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::LISTING_TYPES as $reference => $type) {
            $listingType = new ListingType();
            $listingType->setType($type);
            $manager->persist($listingType);
            $this->setReference($reference, $listingType);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['group1'];
    }
}
