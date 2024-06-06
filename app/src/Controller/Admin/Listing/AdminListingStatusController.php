<?php

namespace App\Controller\Admin\Listing;

use App\Entity\ListingStatus;
use App\Form\ListingStatusType;
use App\Repository\ListingStatusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

#[Route('/admin/listingStatus')]
class AdminListingStatusController extends AbstractController
{
    #[Route('/', name: 'app_listing_status_index', methods: ['GET'])]
    public function getAll(listingStatusRepository $listingStatusRepository): JsonResponse
    {
        $listingStatuses= $listingStatusRepository->findAll();
        $data = [];

        foreach ($listingStatuses as $listingStatus) {
            $data[] = [
                'id' => $listingStatus->getId(),
                'status' => $listingStatus->getStatus(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }


    #[Route('/new', name: 'add_listing_status', methods: ['POST'])]
    public function add(Request $request, ManagerRegistry $doctrine): JsonResponse
    {


        $entityManager = $doctrine->getManager();
   
        $listingStatus = new ListingStatus();
        $data = json_decode($request->getContent(), true);
        $status = $data['status'];
        if (empty($status) ) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $listingStatus->setStatus($status);

   
        $entityManager->persist($listingStatus);
        $entityManager->flush();
   
        return $this->json('Created new status successfully with id ' . $listingStatus->getId());
    }


    #[Route('/{id}', name: 'app_listing_status_show', methods: ['GET'])]
    public function get($id, listingStatusRepository $listingStatusRepository): JsonResponse
    {
        $listingStatus = $listingStatusRepository->find($id);

        $data[] = [
            'id' => $listingStatus->getId(),
            'status' => $listingStatus->getStatus()
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'app_listing_status_edit', methods: ['GET', 'POST'])]
    public function update($id, Request $request, listingStatusRepository $listingStatusRepository, EntityManagerInterface $manager): JsonResponse
    {
        $listingStatus = $listingStatusRepository->find($id);
        $data = json_decode($request->getContent(), true);

        empty($data['status']) ? true : $listingStatus->setStatus($data['status']);

        $manager->persist($listingStatus);
        $manager->flush();


        $listingStatusArray = [
            'id' => $listingStatus->getId(),
            'status' => $listingStatus->getStatus()
        ];

        return new JsonResponse($listingStatusArray, Response::HTTP_OK);
    }


    #[Route('/{id}', name: 'app_listing_status_delete', methods: ['DELETE'])]
    public function delete($id, listingStatusRepository $listingStatusRepository, Request $request): JsonResponse
    {
        $listingStatus = $listingStatusRepository->find($id);

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $listingStatusRepository->remove($listingStatus, true);
            return new JsonResponse(['status' => 'listingStatus deleted'], Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(['status' => 'UNAUTHORIZED'], Response::HTTP_FORBIDDEN);
    }
}