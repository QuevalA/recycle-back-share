<?php

namespace App\Controller\Admin;

use App\Entity\SubType;
use App\Form\SubTypeType;
use App\Repository\ListingTypeRepository;
use App\Repository\SubTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/subType')]
class AdminSubTypeController extends AbstractController
{
    #[Route('/', name: 'app_sub_type_index', methods: ['GET'])]
    public function getAll(subTypeRepository $subTypeRepository): JsonResponse
    {
        $subTypes = $subTypeRepository->findAll();
        $data = [];

        foreach ($subTypes as $subType) {
            $data[] = [
                'id' => $subType->getId(),
                'subType' => $subType->getsubType(),
                'fkListingType' => $subType->getFkListingType()->getId(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }


    #[Route('/new', name: 'add_sub_type', methods: ['POST'])]
    public function add(Request $request, ManagerRegistry $doctrine, ListingTypeRepository $listingTypeRepository): JsonResponse
    {
        $entityManager = $doctrine->getManager();
   
        $subType = new SubType();
        $data = json_decode($request->getContent(), true);
        $type = $data['subType'];
        $listingType = $listingTypeRepository->find($data['fkListingType']);

        if (empty($type) ) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $subType->setSubtype($type);
        $subType->setFkListingType($listingType);

   
        $entityManager->persist($subType);
        $entityManager->flush();
   
        return $this->json('Created new subType successfully with id ' . $subType->getId());
    }


    #[Route('/{id}', name: 'app_sub_type_show', methods: ['GET'])]
    public function get($id, subTypeRepository $subTypeRepository): JsonResponse
    {
        $subType = $subTypeRepository->findOneBy(['id' => $id]);

        $data = [
            'id' => $subType->getId(),
            'subType' => $subType->getSubType(),
            'fkListingType' => $subType->getFkListingType()->getId(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'app_sub_type_edit', methods: ['GET', 'POST'])]
    public function update($id, Request $request, subTypeRepository $subTypeRepository, EntityManagerInterface $manager, ListingTypeRepository $listingTypeRepository): JsonResponse
    {
        $subType = $subTypeRepository->find($id);
        $data = json_decode($request->getContent(), true);

        empty($data['subType']) ? true : $subType->setSubType($data['subType']);
        empty($data['fkListingType']) ? true : $subType->setFkListingType($listingTypeRepository->find($data['fkListingType']));


        $manager->persist($subType);
        $manager->flush();


        $subTypeArray = [
            'id' => $subType->getId(),
            'subType' => $subType->getSubType(),
            'fkListingType' => $subType->getFkListingType()->getId(),
        ];

        return new JsonResponse($subTypeArray, Response::HTTP_OK);
    }


    #[Route('/{id}', name: 'app_sub_Type_delete', methods: ['DELETE'])]
    public function delete($id, subTypeRepository $subTypeRepository, Request $request): JsonResponse
    {
        $subType = $subTypeRepository->findOneBy(['id' => $id]);

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $subTypeRepository->remove($subType, true);
            return new JsonResponse(['status' => 'subType deleted'], Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(['status' => 'UNAUTHORIZED'], Response::HTTP_FORBIDDEN);
    }

}