<?php

namespace App\Controller\Admin;

use App\Entity\UserFavoriteListing;
use App\Repository\ListingRepository;
use App\Repository\UserFavoriteListingRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/admin/userFavoriteListing')]
class AdminUserFavoriteListingController extends AbstractController
{
    #[Route('/', name: 'app_sub_userFavoriteListing_index', methods: ['GET'])]
    public function getAll(UserFavoriteListingRepository $userFavoriteListingRepository): JsonResponse
    {
        $userFavoriteListings = $userFavoriteListingRepository->findAll();
        $data = [];

        foreach ($userFavoriteListings as $userFavoriteListing) {
            $data[] = [
                'id' => $userFavoriteListing->getId(),
                'fkListing' => $userFavoriteListing->getFkListing()->getId(),
                'fkUser' => $userFavoriteListing->getFkUser()->getId(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }


    #[Route('/new', name: 'add_sub_userFavoriteListing', methods: ['POST'])]
    public function add(Request $request, ManagerRegistry $doctrine, ListingRepository $listingRepository, UserRepository $userRepository): JsonResponse
    {
        $entityManager = $doctrine->getManager();
   
        $userFavoriteListing = new UserFavoriteListing();
        $data = json_decode($request->getContent(), true);
        $listing = $listingRepository->find($data['fkListing']);
        $user = $userRepository->find($data['fkUser']);

        if (empty($listing) || empty($user) ) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $userFavoriteListing->setFkUser($user);
        $userFavoriteListing->setFkListing($listing);

   
        $entityManager->persist($userFavoriteListing);
        $entityManager->flush();
   
        return $this->json('Created new userFavoriteListing successfully with id ' . $userFavoriteListing->getId());
    }


    #[Route('/{id}', name: 'app_sub_userFavoriteListing_show', methods: ['GET'])]
    public function get($id, UserFavoriteListingRepository $userFavoriteListingRepository): JsonResponse
    {
        $userFavoriteListing = $userFavoriteListingRepository->findOneBy(['id' => $id]);

        $data = [
            'id' => $userFavoriteListing->getId(),
            'fkListing' => $userFavoriteListing->getFkListing()->getId(),
            'fkUser' => $userFavoriteListing->getFkUser()->getId(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'app_sub_userFavoriteListing_edit', methods: ['GET', 'POST'])]
    public function update($id, Request $request, UserFavoriteListingRepository $userFavoriteListingRepository, EntityManagerInterface $manager, UserRepository $userRepository, ListingRepository $listingRepository): JsonResponse
    {
        $userFavoriteListing = $userFavoriteListingRepository->find($id);
        $data = json_decode($request->getContent(), true);

        empty($data['fkUser']) ? true : $userFavoriteListing->setFkUser($userRepository->find($data['fkUser']));
        empty($data['fkListing']) ? true : $userFavoriteListing->setFkListing($listingRepository->find($data['fkListing']));


        $manager->persist($userFavoriteListing);
        $manager->flush();


        $subuserFavoriteListingArray = [
            'id' => $userFavoriteListing->getId(),
            'fkUser' => $userFavoriteListing->getFkUser()->getId(),
            'fkListing' => $userFavoriteListing->getFkListing()->getId(),
        ];

        return new JsonResponse($subuserFavoriteListingArray, Response::HTTP_OK);
    }


    #[Route('/{id}', name: 'app_sub_userFavoriteListing_delete', methods: ['DELETE'])]
    public function delete($id, UserFavoriteListingRepository $userFavoriteListingRepository, Request $request): JsonResponse
    {
        $userFavoriteListing = $userFavoriteListingRepository->findOneBy(['id' => $id]);

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $userFavoriteListingRepository->remove($userFavoriteListing, true);
            return new JsonResponse(['status' => 'subuserFavoriteListing deleted'], Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(['status' => 'UNAUTHORIZED'], Response::HTTP_FORBIDDEN);
    }

}
