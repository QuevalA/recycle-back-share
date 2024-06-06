<?php

namespace App\Controller\Admin\Listing;

use App\Entity\ListingTypeSubType;
use App\Repository\ListingTypeRepository;
use App\Repository\ListingTypeSubTypeRepository;
use App\Repository\SubTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;



#[Route('/admin/listingTypeSubType')]
class AdminListingTypeSubTypeController extends AbstractController
{
    #[Route('/', name: 'app_sub_listingTypeSubType_index', methods: ['GET'])]
    public function getAll(ListingTypeSubTypeRepository $sublistingTypeSubTypeRepository): JsonResponse
    {
        $sublistingTypeSubTypes = $sublistingTypeSubTypeRepository->findAll();
        $data = [];

        foreach ($sublistingTypeSubTypes as $sublistingTypeSubType) {
            $data[] = [
                'id' => $sublistingTypeSubType->getId(),
                'fkListingType' => $sublistingTypeSubType->getFkListingType()->getId(),
                'fkSubtype' => $sublistingTypeSubType->getFkSubtype()->getId(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }


    #[Route('/new', name: 'add_sub_listingTypeSubType', methods: ['POST'])]
    public function add(Request $request, ManagerRegistry $doctrine, ListingTypeRepository $listingTypeRepository, SubTypeRepository $subTypeRepository): JsonResponse
    {
        $entityManager = $doctrine->getManager();
   
        $listingTypeSubType = new ListingTypeSubType();
        $data = json_decode($request->getContent(), true);
        $listingType = $listingTypeRepository->find($data['fkListingType']);
        $subType = $subTypeRepository->find($data['fkSubType']);

        if (empty($listingType) || empty($subType) ) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $listingTypeSubType->setFkListingType($listingType);
        $listingTypeSubType->setFkSubtype($subType);

   
        $entityManager->persist($listingTypeSubType);
        $entityManager->flush();
   
        return $this->json('Created new ListingTypeSubType successfully with id ' . $listingTypeSubType->getId());
    }


    #[Route('/{id}', name: 'app_sub_listingTypeSubType_show', methods: ['GET'])]
    public function get($id, ListingTypeSubTypeRepository $listingTypeSubTypeRepository): JsonResponse
    {
        $listingTypeSubType = $listingTypeSubTypeRepository->findOneBy(['id' => $id]);

        $data = [
            'id' => $listingTypeSubType->getId(),
            'fkListingType' => $listingTypeSubType->getFkListingType()->getId(),
            'fkSubType' => $listingTypeSubType->getFkSubtype()->getId(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'app_sub_listingTypeSubType_edit', methods: ['GET', 'POST'])]
    public function update($id, Request $request, ListingTypeSubTypeRepository $listingTypeSubTypeRepository, EntityManagerInterface $manager, ListingTypeRepository $listingTypeRepository,SubTypeRepository $subTypeRepository): JsonResponse
    {
        $listingTypeSubType = $listingTypeSubTypeRepository->find($id);
        $data = json_decode($request->getContent(), true);

        empty($data['fkListingType']) ? true : $listingTypeSubType->setFkListingType($listingTypeRepository->find($data['fkListingType']));
        empty($data['fkSubType']) ? true : $listingTypeSubType->setFkSubtype($subTypeRepository->find($data['fkSubType']));


        $manager->persist($listingTypeSubType);
        $manager->flush();


        $sublistingTypeSubTypeArray = [
            'id' => $listingTypeSubType->getId(),
            'fkListingType' => $listingTypeSubType->getFkListingType()->getId(),
            'fkSubType' => $listingTypeSubType->getFkSubtype()->getId(),
        ];

        return new JsonResponse($sublistingTypeSubTypeArray, Response::HTTP_OK);
    }


    #[Route('/{id}', name: 'app_sub_listingTypeSubType_delete', methods: ['DELETE'])]
    public function delete($id, ListingTypeSubTypeRepository $listingTypeSubTypeRepository, Request $request): JsonResponse
    {
        $listingTypeSubType = $listingTypeSubTypeRepository->findOneBy(['id' => $id]);

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $listingTypeSubTypeRepository->remove($listingTypeSubType, true);
            return new JsonResponse(['status' => 'sublistingTypeSubType deleted'], Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(['status' => 'UNAUTHORIZED'], Response::HTTP_FORBIDDEN);
    }

}
