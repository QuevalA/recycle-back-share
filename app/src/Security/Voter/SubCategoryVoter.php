<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\SubCategory;
use App\Entity\User;
use Symfony\Component\Security\Core\Security;

class SubCategoryVoter extends Voter
{
    public const EDIT = 'SUBCATEGORY_EDIT';

    private  $security;


    public function __construct( Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT])
            && $subject instanceof \App\Entity\SubCategory;
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
        }

        return false;
    }

    private function canEdit(SubCategory $subCategory, User $user): bool
    {


        if ($this->security->isGranted('ROLE_ADMIN')) {
            return true;
        } else {
            return false;
        }



    }

}
