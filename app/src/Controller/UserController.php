<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\AvatarRepository;
use App\Repository\ConversationRepository;
use App\Repository\ListingRepository;
use App\Repository\ListingStatusRepository;
use App\Repository\UserRankRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Firebase\JWT\Key;
use Firebase\JWT\JWT;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function getAll(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'role' => $user->getRoles(),
                'createdAt' => $user->getCreatedAt(),
                'updatedAt' => $user->getUpdatedAt(),
                'city' => $user->getCity(),
                'country' => $user->getCountry(),
                'pseudo' => $user->getPseudo(),
                'fkAvatar' => $user->getFkAvatar()->getId(),
                'fkUserRank' => $user->getFkUserRank()->getId(),
                'gpsCoordinates' => $user->getGpsCoordinates(),
                'postCode' => $user->getPostcode(),
                'streetName' => $user->getStreetName(),
                'streetNumber' => $user->getStreetNumber(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function get($id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);

        $data[] = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt(),
            'updatedAt' => $user->getUpdatedAt(),
            'city' => $user->getCity(),
            'country' => $user->getCountry(),
            'pseudo' => $user->getPseudo(),
            'fkAvatar' => $user->getFkAvatar()->getId(),
            'fkUserRank' => $user->getFkUserRank()->getId(),
            'gpsCoordinates' => $user->getGpsCoordinates(),
            'postCode' => $user->getPostcode(),
            'streetName' => $user->getStreetName(),
            'streetNumber' => $user->getStreetNumber(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function update($id, Request $request, UserRepository $userRepository, EntityManagerInterface $manager, UserRankRepository $userRankRepository, AvatarRepository $avatarRepository): JsonResponse
    {
        $user = $userRepository->find($id);
        $data = json_decode($request->getContent(), true);

        empty($data['email']) ? true : $user->setEmail($data['email']);
        empty($data['password']) ? true : $user->setPassword($data['password']);
        empty($data['role']) ? true : $user->setRoles($data['role']);
        empty($data['city']) ? true : $user->setCity($data['city']);
        empty($data['country']) ? true : $user->setCountry($data['country']);
        empty($data['pseudo']) ? true : $user->setPseudo($data['pseudo']);
        empty($data['fkAvatar']) ? true : $user->setFkAvatar($avatarRepository->find($data['fkAvatar']));
        empty($data['fkUserRank']) ? true : $user->setFkUserRank($userRankRepository->find($data['fkUserRank']));
        empty($data['gpsCoordinates']) ? true : $user->setGpsCoordinates($data['gpsCoordinates']);
        empty($data['postCode']) ? true : $user->setPostcode($data['postCode']);
        empty($data['streetName']) ? true : $user->setStreetName($data['streetName']);
        empty($data['streetNumber']) ? true : $user->setStreetNumber($data['streetNumber']);

        $user->setUpdatedAt(new \DateTimeImmutable());

        $manager->persist($user);
        $manager->flush();

        $userArray= [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'createdAt' => $user->getCreatedAt(),
            'updatedAt' => $user->getUpdatedAt(),
            'city' => $user->getCity(),
            'country' => $user->getCountry(),
            'pseudo' => $user->getPseudo(),
            'fkAvatar' => $user->getFkAvatar()->getId(),
            'fkUserRankId' => $user->getFkUserRank()->getId(),
            'gpsCoordinates' => $user->getGpsCoordinates(),
            'postCode' => $user->getPostcode(),
            'streetName' => $user->getStreetName(),
            'streetNumber' => $user->getStreetNumber(),
        ];

        return new JsonResponse($userArray, Response::HTTP_OK);
    }

    #[Route('/disable/{id}', name: 'app_user_disable', methods: ['GET', 'POST'])]
    public function disableUser($id, UserRepository $userRepository, listingRepository $listingRepository, listingStatusRepository $listingStatusRepository, ConversationRepository $conversationRepository, EntityManagerInterface $manager): JsonResponse
    {
        $data = [];
        $user = $userRepository->find($id);
        $disabledUserListings = $listingRepository->findBy(['fkUser' => $id]);

        $listingsArray = [];
        $conversationsArray = [];

        $user->setRoles(['ROLE_DISABLED']);
        $user->setUpdatedAt(new \DateTimeImmutable());
        $manager->persist($user);
        $manager->flush();

        foreach ($disabledUserListings as $listing) {
            $listingConversations = $conversationRepository->findBy(['fkListing' => $listing->getId()]);

            $listingsArray[] = [
                'listingId' => $listing->getId(),
                'listingStatus' => $listing->getFkListingStatus()->getStatus(),
            ];

            foreach ($listingConversations as $conversation) {
                $conversation->setIsActive(false);

                $conversationsArray[] = [
                    'conversationId' => $conversation->getId(),
                    'conversationIsActive' => $conversation->getIsActive(),
                    'conversationFkListing' => $conversation->getFkListing()->getId(),
                ];
            }
        }

        $listingIds = array_column($listingsArray, 'listingId');
        $updatedListingStatus = $listingRepository->batchUpdateListingStatus($listingIds, 2);

        $data[] = [
            'User id' => $user->getId(),
            'User roles' => $user->getRoles(),
            'Related listings' => $updatedListingStatus,
            'Related conversations' => $conversationsArray,
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function delete($id, userRepository $userRepository, Request $request): JsonResponse
    {
        $user = $userRepository->find($id);

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $userRepository->remove($user, true);
            return new JsonResponse(['status' => 'user deleted'], Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(['status' => 'UNAUTHORIZED'], Response::HTTP_FORBIDDEN);
    }
}