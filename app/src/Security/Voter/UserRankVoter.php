<?php

namespace App\Security\Voter;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;
use App\Entity\UserRank;
use Symfony\Component\Security\Core\Security;

class UserRankVoter extends Voter
{
    public const EDIT = 'USER_RANK_ADMIN';
    public const VIEW = 'USER_RANK_VIEW';

    private $userRepository;
    private  $security;

    public function __construct(UserRepository $userRepository, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {

        return in_array($attribute, [self::EDIT, self::VIEW])
            && $subject instanceof \App\Entity\UserRank;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                return $this->canEdit($subject, $user);
                break;
            case self::VIEW:
                return $this->canView($subject, $user);
                break;
        }

        return false;
    }

    private function canEdit(UserRank $userRank, User $user): bool
    {
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        } else {
            return false;
        }
    }

    private function canView(UserRank $userRank, User $user): bool
    {
            return true;
    }
}
