<?php

namespace App\Controller\Admin;

use App\Entity\UserRank;
use App\Form\UserRankType;
use App\Repository\UserRankRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/admin/userRank')]
class AdminUserRankController extends AbstractController
{
    #[Route('/', name: 'app_user_rank_index', methods: ['GET'])]
    public function getAll(UserRankRepository $userRankRepository): JsonResponse
    {
        $userRanks = $userRankRepository->findAll();
        $data = [];

        foreach ($userRanks as $userRank) {
            $data[] = [
                'id' => $userRank->getId(),
                'level' => $userRank->getLevel(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }


        #[Route('/new', name: 'add_user_rank', methods: ['POST'])]
        public function add(Request $request, ManagerRegistry $doctrine): JsonResponse
        {
            $entityManager = $doctrine->getManager();
       
            $userRank = new UserRank();
            $data = json_decode($request->getContent(), true);
            $level = $data['level'];
            if (empty($level) ) {
                throw new NotFoundHttpException('Expecting mandatory parameters!');
            }
    
            $userRank->setLevel($level);

       
            $entityManager->persist($userRank);
            $entityManager->flush();
       
            return $this->json('Created new rank successfully with id ' . $userRank->getId());
        }


        #[Route('/{id}', name: 'app_user_rank_show', methods: ['GET'])]
        public function get($id, UserRankRepository $userRankRepository): JsonResponse
        {
            $userRank = $userRankRepository->findOneBy(['id' => $id]);

            $data = [
                'id' => $userRank->getId(),
                'level' => $userRank->getLevel(),
            ];

            return new JsonResponse($data, Response::HTTP_OK);
        }

    #[Route('/{id}/edit', name: 'app_user_rank_edit', methods: ['GET', 'POST'])]
    public function update($id, Request $request, UserRankRepository $userRankRepository, EntityManagerInterface $manager): JsonResponse
    {

        $userRank = $userRankRepository->find($id);
        $data = json_decode($request->getContent(), true);

        empty($data['level']) ? true : $userRank->setLevel($data['level']);

        $manager->persist($userRank);
        $manager->flush();


        $userRankArray = [
            'id' => $userRank->getId(),
            'level' => $userRank->getLevel(),
        ];

        return new JsonResponse($userRankArray, Response::HTTP_OK);
    }



        #[Route('/{id}', name: 'app_user_rank_delete', methods: ['DELETE'])]
        public function delete($id, UserRankRepository $userRankRepository, Request $request): JsonResponse
        {
            $userRank = $userRankRepository->findOneBy(['id' => $id]);

            if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
                $userRankRepository->remove($userRank, true);
                return new JsonResponse(['status' => 'userRank deleted'], Response::HTTP_NO_CONTENT);
            }
            return new JsonResponse(['status' => 'UNAUTHORIZED'], Response::HTTP_FORBIDDEN);
        }

}