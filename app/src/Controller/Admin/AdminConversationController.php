<?php

namespace App\Controller\Admin;

use App\Entity\Conversation;
use App\Repository\ConversationRepository;
use App\Repository\ListingRepository;

use App\Repository\ProfileRepository;
use App\Repository\SubCategoryRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

use Doctrine\Persistence\ManagerRegistry;

#[Route('/admin/conversation')]
class AdminConversationController extends AbstractController
{
    #[Route('/', name: 'app_conversation_index', methods: ['GET'])]
    public function getAll(ConversationRepository $conversationRepository): JsonResponse
    {
        $conversations = $conversationRepository->findAll();
        $data = [];

        foreach ($conversations as $conversation) {
            $data[] = [
                'id' => $conversation->getId(),
                'createdAt' => $conversation->getCreatedAt(),
                'fkListing' => $conversation->getFkListing()->getId(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }


    #[Route('/new', name: 'add_conversation', methods: ['POST'])]
    public function add(Request $request,ManagerRegistry $doctrine,ListingRepository $listingRepository): JsonResponse
    {


        $entityManager = $doctrine->getManager();
   
        $conversation = new Conversation();
        $data = json_decode($request->getContent(), true);
        $fkListing = $data['fkListing'];
        if (empty($fkListing) ) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }


        $listing = $listingRepository->find($fkListing);
        $conversation->setCreatedAt(new \DateTimeImmutable());
        $conversation->setFkListing($listing);
        $conversation->setIsActive(true);
   
        $entityManager->persist($conversation);
        $entityManager->flush();
   
        return $this->json('Created new conversation successfully with id ' . $conversation->getId());
    }


    #[Route('/{id}', name: 'app_conversation_show', methods: ['GET'])]
    public function get($id, conversationRepository $conversationRepository): JsonResponse
    {
        $conversation = $conversationRepository->findOneBy(['id' => $id]);

        $data = [
            'id' => $conversation->getId(),
            'fkListing' => $conversation->getFkListing()->getId(),
            'createdAt' => $conversation->getCreatedAt(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }



    #[Route('/{id}', name: 'app_conversation_delete', methods: ['DELETE'])]
    public function delete($id, ConversationRepository $conversationRepository, Request $request): JsonResponse
    {
        $conversation = $conversationRepository->findOneBy(['id' => $id]);

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $conversationRepository->remove($conversation, true);
            return new JsonResponse(['status' => 'conversation deleted'], Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(['status' => 'UNAUTHORIZED'], Response::HTTP_FORBIDDEN);
    }

    #[Route('/me/{id}', name: 'app_conversation_show_mine', methods: ['GET'])]
    public function getMyConversations($id,ListingRepository $listingRepository, ConversationRepository $conversationRepository,ProfileRepository $profileRepository ): JsonResponse
    {

        $conversations = null;

        $profile = $profileRepository->find( $id);
        $listings = $listingRepository->findBy(array('fkProfile' => $id));
        foreach ($listings as $listing){
            $conversations = $conversationRepository->findBy(array('fkListing' => $listing->getId()));
        }

        $data = [];

        if($conversations != null){

            foreach ($conversations as $conversation) {

                $data[] = [
                    'id' => $conversation->getId(),
                    'listing' => $conversation->getFkListing()->getId(),
                    'profile' => $profile->getPseudo(),
                    'avatar' => $profile->getFkAvatar()->getImage()
                ];
            }
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }
}