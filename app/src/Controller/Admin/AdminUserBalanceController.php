<?php

namespace App\Controller\Admin;

use App\Entity\UserBalance;
use App\Repository\UserBalanceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/userBalance')]
class AdminUserBalanceController extends AbstractController
{
    #[Route('/', name: 'app_user_balance_index', methods: ['GET'])]
    public function getAll(UserBalanceRepository $userBalanceRepository): JsonResponse
    {
        $userBalances = $userBalanceRepository->findAll();
        $data = [];

        foreach ($userBalances as $userBalance) {
            $data[] = [
                'id' => $userBalance->getId(),
                'balance' => $userBalance->getBalance(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/new', name: 'add_user_balance', methods: ['POST'])]
    public function add(Request $request, ManagerRegistry $doctrine, UserRepository $userRepository): JsonResponse
    {
        $entityManager = $doctrine->getManager();
   
        $userBalance = new UserBalance();
        $data = json_decode($request->getContent(), true);
        $balance = $data['balance'];
        $user = $userRepository->find($data['fkUser']);

        if (empty($balance) || empty($user) ) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $userBalance->setBalance($balance);
        $userBalance->setCreatedAt(new \DateTimeImmutable());
        $userBalance->setFkUser($user);
        $userBalance->setUpdatedAt(new \DateTimeImmutable());

   
        $entityManager->persist($userBalance);
        $entityManager->flush();
   
        return $this->json('Created new userBalane successfully with id ' . $userBalance->getId());
    }


    #[Route('/{id}', name: 'app_user_balance_show', methods: ['GET'])]
    public function get($id, UserBalanceRepository $userBalanceRepository): JsonResponse
    {
        $userBalance = $userBalanceRepository->find($id);

        $data[] = [
            'id' => $userBalance->getId(),
            'balance' => $userBalance->getBalance(),
            'user' => $userBalance->getFkUser()->getId(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/user/{id}', name: 'app_user_balance_show_by_user', methods: ['GET'])]
    public function getByUser($id, UserBalanceRepository $userBalanceRepository): JsonResponse
    {
        $userBalance = $userBalanceRepository->findOneBy(array('fkUser' => $id));

        $data[] = [
            'id' => $userBalance->getId(),
            'balance' => $userBalance->getBalance(),
            'user' => $userBalance->getFkUser()->getId(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'app_user_balance_edit', methods: ['GET', 'POST'])]
    public function update($id, Request $request, UserBalanceRepository $userBalanceRepository, EntityManagerInterface $manager, UserRepository $userRepository): JsonResponse
    {
        $userBalance = $userBalanceRepository->find($id);
        $data = json_decode($request->getContent(), true);

        empty($data['balance']) ? true : $userBalance->setBalance($data['balance']);
        empty($data['fkUser']) ? true : $userBalance->setFkUser($userRepository->find($data['fkUser']));

        $manager->persist($userBalance);
        $manager->flush();


        $userBalanceArray= [
            'id' => $userBalance->getId(),
            'balance' => $userBalance->getBalance(),
            'user' => $userBalance->getFkUser()->getId(),
        ];

        return new JsonResponse($userBalanceArray, Response::HTTP_OK);
    }


    #[Route('/{id}', name: 'app_user_balance_delete', methods: ['DELETE'])]
    public function delete($id, UserBalanceRepository $userBalanceRepository, Request $request): JsonResponse
    {
        $userBalance = $userBalanceRepository->find($id);

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $userBalanceRepository->remove($userBalance, true);
            return new JsonResponse(['status' => 'userBalance deleted'], Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(['status' => 'UNAUTHORIZED'], Response::HTTP_FORBIDDEN);
    }
}