<?php

namespace App\DataFixtures;

use App\Entity\ListingStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class ListingStatusFixtures extends Fixture implements FixtureGroupInterface
{
    const LISTING_STATUSES = [
        'publiee' => 'Publiée',
        'fermee' => 'Fermée',
        'attente_moderation' => 'En attente de modération',
        'attente_correction' => 'En attente de correction',
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::LISTING_STATUSES as $reference => $status) {
            $listingStatus = new ListingStatus();
            $listingStatus->setStatus($status);
            $manager->persist($listingStatus);
            $this->setReference($reference, $listingStatus);
        }

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['group1'];
    }
}
