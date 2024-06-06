<?php

namespace App\Controller;

use App\Entity\UserRank;
use App\Form\UserRankType;
use App\Repository\UserRepository;
use App\Repository\UserRankRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/userRank')]
class UserRankController extends AbstractController
{
    #[Route('/', name: 'app_user_rank_index', methods: ['GET'])]
    public function getAll(UserRankRepository $userRankRepository): JsonResponse
    {
        $userRanks = $userRankRepository->findAll();
        $data = [];

        foreach ($userRanks as $userRank) {
            $this->denyAccessUnlessGranted('USER_RANK_ADMIN', $userRank);

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
            $this->denyAccessUnlessGranted('USER_RANK_ADMIN', $userRank);

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
            $this->denyAccessUnlessGranted('USER_RANK_ADMIN', $userRank);

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
        $this->denyAccessUnlessGranted('USER_RANK_ADMIN', $userRank);

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
            $this->denyAccessUnlessGranted('USER_RANK_ADMIN', $userRank);

            $userRankRepository->remove($userRank, true);
            return new JsonResponse(['status' => 'userRank deleted'], Response::HTTP_NO_CONTENT);

        }

        #[Route('/user/{id}', name: 'app_user_rank_show', methods: ['GET'])]
        public function getByUser($id, UserRankRepository $userRankRepository, UserRepository $userRepository): JsonResponse
        {

            $user = $userRepository->findOneBy(['id' => $id]);
            $userRank = $userRankRepository->findOneBy(['id' => $user->getFkUserRank()->getId()]);
            $this->denyAccessUnlessGranted('USER_RANK_VIEW', $userRank);

            $data = [
                'id' => $userRank->getId(),
                'level' => $userRank->getLevel(),
            ];

            return new JsonResponse($data, Response::HTTP_OK);
        }

}