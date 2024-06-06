<?php

namespace App\Security\Voter;

use App\Repository\UserRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\Listing;
use App\Entity\User;
use Doctrine\DBAL\Tools\Dumper;
use Symfony\Component\Security\Core\Security;

class ListingVoter extends Voter
{
    public const CREATE = 'LISTING_CREATE';
    public const VIEW = 'LISTING_VIEW';
    public const EDIT = 'LISTING_EDIT';
    public const DELETE = 'LISTING_DELETE';

    private $userRepository;
    private  $security;

    public function __construct(UserRepository $userRepository, Security $security)
    {
        $this->userRepository = $userRepository;
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CREATE, self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof \App\Entity\Listing;
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
            case self::VIEW:
                return $this->canView($subject, $user);
                break;
            case self::EDIT:
                return $this->canEdit($subject, $user);
                break;
            case self::DELETE:
                return $this->canDelete($subject, $user);
                break;
        }

        return false;
    }

    private function canCreate(User $user): bool
    {
        $userRoles = $user->getRoles();

        // If user is disabled he cannot create a listing
        if (in_array("ROLE_DISABLED", $userRoles)) {
            return false;
        } else {
            return true;
        }
    }

    private function canView(Listing $listing, User $user): bool
    {
        /*if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        $users = $this->userRepository->findBy(['fkUser' => $user->getId()]);
        $ids = [];

        for($i=0; $i<sizeof($users); $i++){
            $ids[$i] = $users[$i]->getId();
        }

        if(in_array( $listing->getFkUser()->getId(), $ids )){
            return true;
        } else {
            return false;
        }*/
        return true;
    }

    private function canEdit(Listing $listing, User $user): bool
    {
        $userRoles = $user->getRoles();

        if (in_array("ROLE_DISABLED", $userRoles)) {
            return false;
        } else {
            return true;
        }
    }

    private function canDelete(Listing $listing, User $user): bool
    {
        $users = $this->userRepository->findBy(['id' => $user->getId()]);
        $ids = [];

        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        }

        for($i=0; $i<sizeof($users); $i++){
            $ids[$i] = $users[$i]->getId();
        }

        if(in_array( $listing->getFkUser()->getId(), $ids )){
            return true;
        } else {
            return false;
        }
    }
}
