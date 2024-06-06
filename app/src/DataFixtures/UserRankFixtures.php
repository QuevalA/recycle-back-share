<?php

namespace App\DataFixtures;

use App\Entity\UserRank;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class UserRankFixtures extends Fixture implements FixtureGroupInterface
{
    const RANK_1 = "rank_1";
    const RANK_2 = "rank_2";
    const RANK_3 = "rank_3";

    public function load(ObjectManager $manager): void
    {
        $ranks = [];

        // Create first rank
        $rank1 = new UserRank();
        $rank1->setLevel("Nouveau");
        $manager->persist($rank1);
        $this->setReference(self::RANK_1, $rank1);

        // Create second rank
        $rank2 = new UserRank();
        $rank2->setLevel("Intermédiaire");
        $manager->persist($rank2);
        $this->setReference(self::RANK_2, $rank2);

        // Create third rank
        $rank3 = new UserRank();
        $rank3->setLevel("Experimenté");
        $manager->persist($rank3);
        $this->setReference(self::RANK_3, $rank3);

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 1; // the order in which fixtures will be loaded
    }

    public static function getGroups(): array
    {
        return ['group1'];
    }
}
