<?php

namespace App\DataFixtures;

use App\Entity\Conversation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ConversationFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    const CONVERSATION_ = "conversation" ;

    public function getDependencies(): array
    {
        return [
            ListingFixtures::class,
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

        $conversationReferences = [];

        foreach ($listings as $listing) {
            // generate a random number of conversations (between 0 and 3) for each listing
            $numConversations = rand(0, 3);

            for ($i = 0; $i < $numConversations; $i++) {
                $conversation = new Conversation();
                $conversation->setIsActive(true);
                $conversation->setCreatedAt(new \DateTimeImmutable());
                $conversation->setFkListing($listing);

                $manager->persist($conversation);

                $conversationReferences[] = $conversation;
            }
        }

        $manager->flush();

        foreach ($conversationReferences as $i => $conversation) {
            $this->setReference(self::CONVERSATION_ . $i, $conversation);
        }
    }
}
