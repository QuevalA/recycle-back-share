<?php

namespace App\Security\Voter;

use App\Entity\Conversation;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class ConversationVoter extends Voter
{
    public const CREATE = 'CONVERSATION_CREATE';
    public const EDIT = 'CONVERSATION_RIGHT';

    private $userRepository;
    private $security;

    public function __construct(UserRepository $userRepository, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::EDIT]) && $subject instanceof Conversation;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case self::CREATE:
                return $this->canCreate($user);
                break;
            case self::EDIT:
                return $this->canEdit($subject, $user);
                break;
        }

        return false;
    }

    private function canCreate(User $user): bool
    {
        $userRoles = $user->getRoles();

        if (in_array("ROLE_DISABLED", $userRoles)) {
            return false;
        } else {
            return true;
        }
    }

    private function canEdit(Conversation $conversation, User $user): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $users = $this->userRepository->findBy(['fkUser' => $user->getId()]);
        $ids = [];

        for ($i = 0; $i < sizeof($users); $i++) {
            $ids[$i] = $users[$i]->getId();
        }

        if (in_array($conversation->getFkListing()->getFkUser()->getId(), $ids)) {
            return true;
        } else {
            return false;
        }
    }
}
