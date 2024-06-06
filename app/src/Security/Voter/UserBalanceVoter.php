<?php

namespace App\Security\Voter;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\UserBalance;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class UserBalanceVoter extends Voter
{
    public const EDIT = 'USER_BALANCE_EDIT';
    public const VIEW = 'USER_BALANCE_VIEW';
    public const DELETE = 'USER_BALANCE_DELETE';

    private $userRepository;
    private  $security;

    public function __construct(UserRepository $userRepository, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof \App\Entity\UserBalance;
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
            case self::DELETE:
                return $this->canDelete($subject, $user);
                break;
        }

        return false;
    }

    private function canEdit(UserBalance $userBalance, User $user): bool
    {


        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        } else {
            return false;
        }



    }
    private function canView(UserBalance $userBalance, User $user): bool
    {

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $users = $this->userRepository->findBy(['fkUser' => $user->getId()]);
        $ids = [];

        for($i=0; $i<sizeof($users); $i++){
            $ids[$i] = $users[$i]->getId();
        }

        if(in_array( $userBalance->getFkUser()->getId(), $ids )){
            return true;
        } else {
            return false;
        }

    }

    private function canDelete(UserBalance $userBalance, User $user): bool
    {


        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        } else {
            return false;
        }



    }
}
