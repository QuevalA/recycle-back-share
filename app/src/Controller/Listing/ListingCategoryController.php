<?php

namespace App\Controller\Listing;

use App\Entity\ListingCategory;
use App\Form\ListingCategoryType;
use App\Repository\ListingCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/listingCategory')]
class ListingCategoryController extends AbstractController
{
    #[Route('/', name: 'app_listing_category_index', methods: ['GET'])]
    public function getAll(listingCategoryRepository $listingCategoryRepository): JsonResponse
    {
        $listingCategories = $listingCategoryRepository->findAll();
        $data = [];

        foreach ($listingCategories as $listingCategory) {
            $data[] = [
                'id' => $listingCategory->getId(),
                'category' => $listingCategory->getCategory(),
                'image' => $listingCategory->getCategoryImage(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/new', name: 'add_listing_category', methods: ['POST'])]
    public function add(Request $request, ManagerRegistry $doctrine): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $listingCategory = new ListingCategory();
        $data = json_decode($request->getContent(), true);
        $category = $data['category'];
        $image = $data['categoryImage'];
        if (empty($category) || empty($image)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $listingCategory->setCategory($category);
        $listingCategory->setCategoryImage($image);

        $entityManager->persist($listingCategory);
        $entityManager->flush();

        return $this->json('Created new category successfully with id ' . $listingCategory->getId());
    }

    #[Route('/{id}/edit', name: 'app_ListingCategory_edit', methods: ['GET', 'POST'])]
    public function update($id, Request $request, listingCategoryRepository $listingCategoryRepository, EntityManagerInterface $manager): JsonResponse
    {
        $listingCategory = $listingCategoryRepository->find($id);
        $data = json_decode($request->getContent(), true);

        empty($data['category']) ? true : $listingCategory->setCategory($data['category']);
        empty($data['categoryImage']) ? true : $listingCategory->setCategoryImage($data['categoryImage']);


        $manager->persist($listingCategory);
        $manager->flush();


        $listingCategoryArray = [
            'id' => $listingCategory->getId(),
            'category' => $listingCategory->getCategory(),
            'categoryImage' => $listingCategory->getCategoryImage(),
        ];

        return new JsonResponse($listingCategoryArray, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_listingCategory_delete', methods: ['DELETE'])]
    public function delete($id, listingCategoryRepository $listingCategoryRepository, Request $request): JsonResponse
    {
        $listingCategory = $listingCategoryRepository->find($id);

        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $listingCategoryRepository->remove($listingCategory, true);
            return new JsonResponse(['status' => 'listingCategory deleted'], Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(['status' => 'UNAUTHORIZED'], Response::HTTP_FORBIDDEN);
    }

    #[Route('/{id}', name: 'app_listingCategory_show', methods: ['GET'])]
    public function get($id, ListingCategoryRepository $listingCategoryRepository): JsonResponse
    {
        $listingCategory = $listingCategoryRepository->findOneBy(['id' => $id]);

        $data = [
            'id' => $listingCategory->getId(),
            'category' => $listingCategory->getCategory(),
            'image' => $listingCategory->getCategoryImage(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }
}