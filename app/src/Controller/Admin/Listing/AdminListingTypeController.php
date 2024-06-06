<?php

namespace App\Controller\Admin\Listing;

use App\Entity\ListingType;
use App\Form\ListingTypeType;
use App\Repository\ListingTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;


#[Route('/admin/listingType')]
class AdminListingTypeController extends AbstractController
{
    #[Route('/', name: 'app_listing_type_index', methods: ['GET'])]
    public function getAll(listingTypeRepository $listingTypeRepository): JsonResponse
    {
        $listingTypes= $listingTypeRepository->findAll();
        $data = [];

        foreach ($listingTypes as $listingType) {
            $data[] = [
                'id' => $listingType->getId(),
                'type' => $listingType->getType(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }


    #[Route('/new', name: 'add_listing_type', methods: ['POST'])]
    public function add(Request $request, ManagerRegistry $doctrine): JsonResponse
    {


        $entityManager = $doctrine->getManager();
   
        $listingType = new ListingType();
        $data = json_decode($request->getContent(), true);
        $type = $data['type'];
        if (empty($type) ) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $listingType->setType($type);

   
        $entityManager->persist($listingType);
        $entityManager->flush();
   
        return $this->json('Created new category successfully with id ' . $listingType->getId());
    }


    #[Route('/{id}', name: 'app_listing_type_show', methods: ['GET'])]
    public function get($id, listingTypeRepository $listingTypeRepository): JsonResponse
    {
        $listingType = $listingTypeRepository->find($id);

        $data[] = [
            'id' => $listingType->getId(),
            'type' => $listingType->getType()
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'app_listing_type_edit', methods: ['GET', 'POST'])]
    public function update($id, Request $request, listingTypeRepository $listingTypeRepository, EntityManagerInterface $manager): JsonResponse
    {
        $listingType = $listingTypeRepository->find($id);
        $data = json_decode($request->getContent(), true);

        empty($data['type']) ? true : $listingType->setType($data['type']);


        $manager->persist($listingType);
        $manager->flush();


        $listingTypeArray = [
            'id' => $listingType->getId(),
            'type' => $listingType->getType(),
        ];

        return new JsonResponse($listingTypeArray, Response::HTTP_OK);
    }


    #[Route('/{id}', name: 'app_listing_type_delete', methods: ['DELETE'])]
    public function delete($id, listingTypeRepository $listingTypeRepository, Request $request): JsonResponse
    {
        $listingType = $listingTypeRepository->find($id);

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $listingTypeRepository->remove($listingType, true);
            return new JsonResponse(['status' => 'listingType deleted'], Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(['status' => 'UNAUTHORIZED'], Response::HTTP_FORBIDDEN);
    }
}