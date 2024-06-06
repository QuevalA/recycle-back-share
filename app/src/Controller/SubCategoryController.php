<?php

namespace App\Controller;

use App\Entity\SubCategory;
use App\Form\SubCategoryType;
use App\Repository\ListingCategoryRepository;
use App\Repository\SubCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/subCategory')]
class SubCategoryController extends AbstractController
{
    #[Route('/', name: 'app_sub_category_index', methods: ['GET'])]
    public function getAll(subCategoryRepository $subCategoryRepository): JsonResponse
    {
        $subCategorys = $subCategoryRepository->findAll();
        $data = [];

        foreach ($subCategorys as $subCategory) {
            $this->denyAccessUnlessGranted('SUBCATEGORY_EDIT', $subCategory);

            $data[] = [
                'id' => $subCategory->getId(),
                'subCategory' => $subCategory->getSubcategory(),
                'fkListingCategory' => $subCategory->getFkListingCategory()->getId(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/category/{id}', name: 'app_sub_category_list', methods: ['GET'])]
    public function getAllSub(SubCategoryRepository $subCategoryRepository, $id): JsonResponse
    {
        $subCategories = $subCategoryRepository->findBy(['fkListingCategory' => $id]);
        $data = [];

        foreach ($subCategories as $subCategory) {
            $this->denyAccessUnlessGranted('SUBCATEGORY_EDIT', $subCategory);

            $data[] = [
                'subCategoryId' => $subCategory->getId(),
                'subCategoryName' => $subCategory->getSubcategory(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/new', name: 'add_sub_category', methods: ['POST'])]
    public function add(Request $request, ManagerRegistry $doctrine, ListingCategoryRepository $listingCategoryRepository): JsonResponse
    {
        $entityManager = $doctrine->getManager();
   
        $subCategory = new SubCategory();
        $this->denyAccessUnlessGranted('SUBCATEGORY_EDIT', $subCategory);

        $data = json_decode($request->getContent(), true);
        $category = $data['subCategory'];
        $listingCategory = $listingCategoryRepository->find($data['fkListingCategory']);

        if (empty($category) ) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $subCategory->setSubcategory($category);
        $subCategory->setFkListingCategory($listingCategory);

        $entityManager->persist($subCategory);
        $entityManager->flush();
   
        return $this->json('Created new subCategory successfully with id ' . $subCategory->getId());
    }

    #[Route('/{id}', name: 'app_sub_category_show', methods: ['GET'])]
    public function get($id, subCategoryRepository $subCategoryRepository): JsonResponse
    {
        $subCategory = $subCategoryRepository->findOneBy(['id' => $id]);
        $this->denyAccessUnlessGranted('SUBCATEGORY_EDIT', $subCategory);

        $data = [
            'id' => $subCategory->getId(),
            'subCategory' => $subCategory->getsubCategory(),
            'fkListingCategory' => $subCategory->getFkListingCategory()->getId(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'app_sub_category_edit', methods: ['GET', 'POST'])]
    public function update($id, Request $request, subCategoryRepository $subCategoryRepository, EntityManagerInterface $manager, ListingCategoryRepository $listingCategoryRepository): JsonResponse
    {
        $subCategory = $subCategoryRepository->find($id);
        $data = json_decode($request->getContent(), true);
        $this->denyAccessUnlessGranted('SUBCATEGORY_EDIT', $subCategory);

        empty($data['subCategory']) ? true : $subCategory->setsubCategory($data['subCategory']);
        empty($data['fkListingCategory']) ? true : $subCategory->setFkListingCategory($listingCategoryRepository->find($data['fkListingCategory']));

        $manager->persist($subCategory);
        $manager->flush();

        $subCategoryArray = [
            'id' => $subCategory->getId(),
            'subCategory' => $subCategory->getsubCategory(),
            'fkListingCategory' => $subCategory->getFkListingCategory()->getId(),
        ];

        return new JsonResponse($subCategoryArray, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_sub_category_delete', methods: ['DELETE'])]
    public function delete($id, subCategoryRepository $subCategoryRepository, Request $request): JsonResponse
    {
        $subCategory = $subCategoryRepository->findOneBy(['id' => $id]);
        $this->denyAccessUnlessGranted('SUBCATEGORY_EDIT', $subCategory);

        $subCategoryRepository->remove($subCategory, true);

        return new JsonResponse(['status' => 'subCategory deleted'], Response::HTTP_NO_CONTENT);
    }
}
