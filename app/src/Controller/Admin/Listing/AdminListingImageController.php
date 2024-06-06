<?php

namespace App\Controller\Admin\Listing;

use App\Entity\ListingImage;
use App\Repository\ListingImageRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\ListingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/listingImage')]
class AdminListingImageController extends AbstractController
{

    #[Route('/', name: 'app_listing_image_index', methods: ['GET'])]
    public function getAll(listingImageRepository $listingImageRepository): JsonResponse
    {
        $listingImages= $listingImageRepository->findAll();
        $data = [];

        foreach ($listingImages as $listingImage) {
            $data[] = [
                'id' => $listingImage->getId(),
                'image' => $listingImage->getImage(),
                'createdAt' => $listingImage->getCreatedAt(),
                'fkListingId' => $listingImage->getFkListing()->getId(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }


    #[Route('/new', name: 'add_listing_image', methods: ['POST'])]
    public function add(Request $request, ManagerRegistry $doctrine, ListingRepository $listingRepository): JsonResponse
    {

        $entityManager = $doctrine->getManager();
   
        $listingImage = new ListingImage();
        $data = json_decode($request->getContent(), true);
        $fkListing = $data['fkListing'];
        if (empty($fkListing) ) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }
        $listing = $listingRepository->find($fkListing);
        $listingImage->setCreatedAt(new \DateTimeImmutable());
        $listingImage->setFkListing($listing);
        $listingImage->setImage($data['image']);
   
        $entityManager->persist($listingImage);
        $entityManager->flush();
   
        return $this->json('Created new listingImage successfully with id ' . $listingImage->getId());
    }


    #[Route('/{id}', name: 'app_listing_image_show', methods: ['GET'])]
    public function get($id, listingImageRepository $listingImageRepository): JsonResponse
    {
        $listingImage = $listingImageRepository->findOneBy(['id' => $id]);

        $data[] = [
            'id' => $listingImage->getId(),
            'image' => $listingImage->getImage(),
            'createdAt' => $listingImage->getCreatedAt(),
            'fkListingId' => $listingImage->getFkListing()->getId()
            ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'app_listing_image_edit', methods: ['GET', 'POST'])]
    public function update($id, Request $request, listingImageRepository $listingImageRepository, EntityManagerInterface $manager, ListingRepository $listingRepository): JsonResponse
    {
        $listingImage = $listingImageRepository->find($id);
        $data = json_decode($request->getContent(), true);

        empty($data['image']) ? true : $listingImage->setImage($data['image']);
        empty($data['fkListing']) ? true : $listingImage->setFkListing($listingRepository->find($data['fkListing']));

        $manager->persist($listingImage);
        $manager->flush();


        $listingImageArray = [
            'id' => $listingImage->getId(),
            'image' => $listingImage->getImage(),
            'fkListingId' => $listingImage->getFkListing()->getId(),
        ];

        return new JsonResponse($listingImageArray, Response::HTTP_OK);
    }


    #[Route('/{id}', name: 'app_listing_image_delete', methods: ['DELETE'])]
    public function delete($id, listingImageRepository $listingImageRepository, Request $request): JsonResponse
    {
        $listingImage = $listingImageRepository->find($id);

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $listingImageRepository->remove($listingImage, true);
            return new JsonResponse(['status' => 'listingImage deleted'], Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(['status' => 'UNAUTHORIZED'], Response::HTTP_FORBIDDEN);
    }
}