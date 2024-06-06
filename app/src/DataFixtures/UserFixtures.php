<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker;

class UserFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            UserRankFixtures::class,
            AvatarFixtures::class
        ];
    }

    public static function getGroups(): array
    {
        return ['group1'];
    }

    const ALEXIS = "alexis";
    const CURTIS = "curtis";
    const NACARANO = "nacarano" ;
    const KEVIN = "kevin";
    const USER_ = "user_";

    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        $avatars = [];
        for ($i = 1; $i <= 10; $i++) {
            $avatars[] = $this->getReference('avatar_' . $i);
        }

        $userRanks = [
            $this->getReference(UserRankFixtures::RANK_1),
            $this->getReference(UserRankFixtures::RANK_2),
            $this->getReference(UserRankFixtures::RANK_3),
        ];

        $users = [];

        $userData = [
            [
                'email' => 'alexis.carrere@hotmail.fr',
                'password' => 'Y8aNV9#Hg8',
                'pseudo' => 'Alexis',
                'reference' => self::ALEXIS,
            ],
            [
                'email' => 'curtis@gmail.com',
                'password' => '4Fbo7^xWH?',
                'pseudo' => 'Curtis',
                'reference' => self::CURTIS,
            ],
            [
                'email' => 'preiranacarano.pro@gmail.com',
                'password' => 'dq8*yY(uc4',
                'pseudo' => 'Nacarano',
                'reference' => self::NACARANO,
            ],
            [
                'email' => 'kevin@gmail.com',
                'password' => '3!bKN;9H,o',
                'pseudo' => 'Kevin',
                'reference' => self::KEVIN,
            ],
        ];

        foreach ($userData as $data) {
            $user = new User();
            $user->setEmail($data['email']);
            $password = $this->hasher->hashPassword($user, $data['password']);
            $user->setPassword($password);
            $user->setRoles(["ROLE_ADMIN", "ROLE_USER"]);
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setPseudo($data['pseudo']);
            $user->setIsActive(true);
            $user->setFkUserRank($userRanks[2]);
            $user->setFkAvatar($avatars[array_rand($avatars)]);
            $manager->persist($user);
            $this->setReference($data['reference'], $user);
        }

        for ($i = 4 ; $i < 50; $i++) {
            $users[$i] = new User();
            $users[$i]->setEmail(self::USER_ . $i . '@fakeemail.com');
            $password = $this->hasher->hashPassword($users[$i], 'password');
            $users[$i]->setPassword($password);
            $users[$i]->setRoles(["ROLE_USER"]);
            $users[$i]->setPseudo($faker->firstName());
            $users[$i]->setIsActive(true);
            $users[$i]->setCreatedAt(new \DateTimeImmutable());
            $users[$i]->setFkUserRank($userRanks[array_rand($userRanks)]);
            $users[$i]->setFkAvatar($avatars[array_rand($avatars)]);

            $manager->persist($users[$i]);
            $this->setReference(self::USER_ . $i, $users[$i]);
        }

        $manager->flush();
    }
}

