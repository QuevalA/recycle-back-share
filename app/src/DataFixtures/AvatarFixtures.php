<?php

namespace App\DataFixtures;

use App\Entity\Avatar;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class AvatarFixtures extends Fixture implements FixtureGroupInterface
{
    const AVATAR = "avatar";

    public function load(ObjectManager $manager): void
    {
        $avatarUrls = array(
            "https://images.pexels.com/photos/4333876/pexels-photo-4333876.jpeg",
            "https://images.pexels.com/photos/13948359/pexels-photo-13948359.jpeg",
            "https://images.pexels.com/photos/995022/pexels-photo-995022.jpeg",
            "https://images.pexels.com/photos/15663275/pexels-photo-15663275.jpeg",
            "https://images.pexels.com/photos/13859631/pexels-photo-13859631.jpeg",
            "https://images.pexels.com/photos/53504/grass-rush-juicy-green-53504.jpeg",
            "https://images.pexels.com/photos/1939485/pexels-photo-1939485.jpeg",
            "https://images.pexels.com/photos/2832382/pexels-photo-2832382.jpeg",
            "https://images.pexels.com/photos/1509534/pexels-photo-1509534.jpeg",
            "https://images.pexels.com/photos/4498792/pexels-photo-4498792.jpeg"
        );

        for ($i = 0; $i < count($avatarUrls); $i++) {
            $avatar = new Avatar();
            $avatar->setImage($avatarUrls[$i]);
            $manager->persist($avatar);
            $this->setReference('avatar_' . ($i + 1), $avatar);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return 2; // the order in which fixtures will be loaded
    }

    public static function getGroups(): array
     {
         return ['group1'];
     }
}
