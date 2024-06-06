<?php

namespace App\DataFixtures;

use App\Entity\Message;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class MessageFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    const MESSAGE_ = "message";

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            ConversationFixtures::class,
        ];
    }

    public static function getGroups(): array
    {
        return ['group1'];
    }

    public function load(ObjectManager $manager): void
    {
        $users = [];
        for ($userIndex = 4; $this->hasReference(UserFixtures::USER_ . $userIndex);
             $userIndex++) {
            $users[] = $this->getReference(UserFixtures::USER_ . $userIndex);
        }
        
        $conversations = [];
        $conversationIndex = 0;
        while ($this->hasReference(ConversationFixtures::CONVERSATION_ .
            $conversationIndex)) {
            $conversations[] = $this->getReference(ConversationFixtures::CONVERSATION_ .
                $conversationIndex);
            $conversationIndex++;
        }

        $currentDateTime = new DateTimeImmutable();

        foreach ($conversations as $conversation) {
            $numberOfMessages = mt_rand(1, 8);

            //To make sure it is the involved listing's creator that gets contacted first.
            $listingAuthor = $conversation->getFkListing()->getFkUser();

            //To make sure $listingAuthor isn't chatting with himself.
            do {
                $conversationStarter = $users[mt_rand(0, count($users) - 1)];
            } while ($conversationStarter === $listingAuthor);

            $message = new Message();
            $message->setContent("Lorem ipsum dolor sit amet, consectetur
            adipiscing elit. #" . 0);
            $message->setCreatedAt($currentDateTime);
            $message->setFkConversation($conversation);
            $message->setFkUserSender($conversationStarter);
            $message->setFkUserRecipient($listingAuthor);
            $manager->persist($message);
            $this->setReference(self::MESSAGE_ . 0, $message);

            for ($messageIndex = 1; $messageIndex < $numberOfMessages; $messageIndex++) {
                $message = new Message();
                $message->setContent("Lorem ipsum dolor sit amet, consectetur adipiscing
                elit. #" . $messageIndex);

                //Make sure each new message is timed later than the precedent.
                $message->setCreatedAt($currentDateTime->modify("+{$messageIndex} hours"));

                $message->setFkConversation($conversation);

                //To setup back and forth messages between the 2 involved users.
                if ($messageIndex % 2 != 0) {
                    $message->setFkUserSender($listingAuthor);
                    $message->setFkUserRecipient($conversationStarter);
                } else if ($messageIndex % 2 == 0) {
                    $message->setFkUserSender($conversationStarter);
                    $message->setFkUserRecipient($listingAuthor);
                }

                $manager->persist($message);
                $this->setReference(self::MESSAGE_ . $messageIndex, $message);
            }
        }

        $manager->flush();
    }
}
