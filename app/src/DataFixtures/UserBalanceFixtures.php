<?php

namespace App\DataFixtures;

use App\Entity\UserBalance;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UserBalanceFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['group1'];
    }

    const BALANCE_ = "balance";

    /**
     * @throws \Exception
     */
    public function load(ObjectManager $manager): void
    {
        $adminUsers = [
            $this->getReference(UserFixtures::ALEXIS),
            $this->getReference(UserFixtures::CURTIS),
            $this->getReference(UserFixtures::NACARANO),
            $this->getReference(UserFixtures::KEVIN),
        ];
        
        $users = [];
        for ($i = 4; $this->hasReference(UserFixtures::USER_ . $i); $i++) {
            $users[] = $this->getReference(UserFixtures::USER_ . $i);
        }

        $balances = Array();
        
        foreach ($adminUsers as $user) {
            $balance = new UserBalance();
            $balance->setBalance(250);
            $balance->setCreatedAt(new \DateTimeImmutable());
            $balance->setUpdatedAt(new \DateTimeImmutable());
            $balance->setFkUser($user);

            $manager->persist($balance);
            $this->setReference(self::BALANCE_ . $user->getId(), $balance);
        }
        
        foreach ($users as $user) {
            $balance = new UserBalance();
            $balance->setBalance(mt_rand(0, 25));
            $balance->setCreatedAt(new \DateTimeImmutable());
            $balance->setUpdatedAt(new \DateTimeImmutable());
            $balance->setFkUser($user);

            $manager->persist($balance);
            $balances[] = $balance;
        }

        $manager->flush();

        // set references after flush
        foreach ($balances as $i => $balance) {
            $this->setReference(self::BALANCE_ . $i, $balance);
        }
    }
}
