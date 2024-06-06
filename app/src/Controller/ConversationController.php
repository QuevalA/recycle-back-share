<?php

namespace App\Controller;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\ConversationRepository;
use App\Repository\ListingImageRepository;
use App\Repository\ListingRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use App\Security\Voter\ConversationVoter;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;

#[Route('/conversation')]
class ConversationController extends AbstractController
{
    private TokenStorageInterface $tokenStorageInterface;
    private JWTTokenManagerInterface $jwtManager;
    private $security;

    public function __construct(TokenStorageInterface $tokenStorageInterface, JWTTokenManagerInterface $jwtManager, Security $security)
    {
        $this->tokenStorageInterface = $tokenStorageInterface;
        $this->jwtManager = $jwtManager;
        $this->security = $security;
    }

    #[Route('/', name: 'app_conversation_index', methods: ['GET'])]
    public function getAll(ConversationRepository $conversationRepository): JsonResponse
    {
        $conversations = $conversationRepository->findAll();
        $data = [];

        foreach ($conversations as $conversation) {
            $this->denyAccessUnlessGranted('CONVERSATION_RIGHT', $conversation);

            $data[] = ['id' => $conversation->getId(), 'createdAt' => $conversation->getCreatedAt(), 'fkListingId' => $conversation->getFkListing()->getId(),];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/new/{id}', name: 'add_conversation', methods: ['POST'])]
    public function add($id, Request $request, ManagerRegistry $doctrine, UserRepository $userRepository, MessageRepository $messageRepository, ListingRepository $listingRepository): JsonResponse
    {
        $data = [];

        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $activeUser = $userRepository->find($decodedJwtToken["id"]);
        $hasConversation = $messageRepository->getConversationIdForUserAndListing($decodedJwtToken["id"], $id);

        if ($this->isGranted(ConversationVoter::CREATE, new Conversation())) {
            $entityManager = $doctrine->getManager();
            $newConversation = new Conversation();
            $newMessage = new Message();
            $receivedData = json_decode($request->getContent(), true);
            $listing = $listingRepository->find($id);

            $firstMessage = $receivedData['firstMessage'];
            if (empty($firstMessage)) {
                throw new NotFoundHttpException("No 1rst message provided. A conversation can't be empty.");
            }

            //Ligne de test avec n'importe quel User
            /*$activeUser = $userRepository->find(5);
            $hasConversation = $messageRepository->getConversationIdForUserAndListing(5, $id);*/

            $listingAuthor = $userRepository->find($listing->getFkUser());

            if ($hasConversation === null) {

                $currentDateTime = new DateTimeImmutable();

                $newConversation->setCreatedAt($currentDateTime);
                $newConversation->setFkListing($listing);
                $newConversation->setIsActive(true);
                $entityManager->persist($newConversation);

                $newMessage->setContent($firstMessage);
                $newMessage->setCreatedAt($currentDateTime);
                $newMessage->setFkConversation($newConversation);
                $newMessage->setFkUserSender($activeUser);
                $newMessage->setFkUserRecipient($listingAuthor);
                $entityManager->persist($newMessage);

                $entityManager->flush();

                $data[] = ["requestResult" => "New conversation created with id : " . $newConversation->getId() . ", starting with message : '" . $newMessage->getContent() . "'",];

            } else if ($hasConversation) {
                $data[] = ["requestResult" => "Active user already has a conversation opened for this listing, with id : " . $hasConversation,];
            }
        } else {
            $data[] = [
                'result' => "You are not authorized to do this.",
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_conversation_show', methods: ['GET'])]
    public function get($id, ConversationRepository $conversationRepository, ListingImageRepository $listingImageRepository, MessageRepository $messageRepository): JsonResponse
    {
        $conversation = $conversationRepository->findOneBy(['id' => $id]);
        $this->denyAccessUnlessGranted('CONVERSATION_RIGHT', $conversation);

        $fkListingId = $conversation->getFkListing()->getId();

        $messages = $messageRepository->findBy(['fkConversation' => $id]);
        $messagesList = [];

        foreach ($messages as $message) {
            $messagesList[] = ['id' => $message->getId(), 'fkConversation' => $message->getFkConversation()->getId(), 'createdAt' => $message->getCreatedAt(), 'sender' => $message->getFkUserSender()->getId(), 'recipient' => $message->getFkUserRecipient()->getId(), 'content' => $message->getContent(),];
        }

        $fkListingImages = $listingImageRepository->findBy(array('fkListing' => $fkListingId));
        $listingCoverImage = $fkListingImages[0]->getImage();

        $data = ['conversationId' => $conversation->getId(), 'createdAt' => $conversation->getCreatedAt(), 'isActive' => $conversation->getIsActive(), 'fkListingId' => $conversation->getFkListing()->getId(), 'listingCoverImage' => $listingCoverImage, 'messagesList' => $messagesList,];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/byUser/{id}', name: 'app_conversation_show_by_user', methods: ['GET'])]
    public function getByUser($id, MessageRepository $messageRepository, ListingImageRepository $listingImageRepository): JsonResponse
    {
        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());

        $sender = $messageRepository->findBy(array('fkUserSender' => $id));
        $recipient = $messageRepository->findBy(array('fkUserRecipient' => $id));
        $messages = array_merge($sender, $recipient);

        $conversations = [];

        foreach ($messages as $message) {
            $conversation = $message->getFkConversation();

            if ($conversation->getIsActive() === true) {
                $fkListingId = $conversation->getFkListing()->getId();
                $fkListingImages = $listingImageRepository->findBy(array('fkListing' => $fkListingId));
                $listingCoverImage = $fkListingImages[0]->getImage();

                $conversations[] = ['conversationId' => $conversation->getId(), 'fkListingId' => $fkListingId, 'fkListingTitle' => $conversation->getFkListing()->getTitle(), 'conversationIsActive' => $conversation->getIsActive(), 'listingCoverImage' => $listingCoverImage, 'latestMessageCreatedAt' => $messageRepository->findLatestMessageByConversationId($conversation->getId())->getCreatedAt(), 'latestMessageContent' => $messageRepository->findLatestMessageByConversationId($conversation->getId())->getContent()];
            }
        }

        $UniqueConversations = array_unique($conversations, SORT_REGULAR);

        $data = ['jwt' => $decodedJwtToken, 'conversations' => $UniqueConversations];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_conversation_delete', methods: ['DELETE'])]
    public function delete($id, ConversationRepository $conversationRepository, Request $request, MessageRepository $messageRepository): JsonResponse
    {
        $conversation = $conversationRepository->findOneBy(['id' => $id]);
        $this->denyAccessUnlessGranted('CONVERSATION_RIGHT', $conversation);

        // delete all the messages of the conversation
        $messages = $messageRepository->findBy(['fkConversation' => $id]);
        foreach ($messages as $message) {
            $messageRepository->remove($message, true);
        }

        $conversationRepository->remove($conversation, true);
        return new JsonResponse(['status' => 'conversation deleted'], Response::HTTP_NO_CONTENT);
    }

    #[Route('/me/{id}', name: 'app_conversation_show_mine', methods: ['GET'])]
    public function getMyConversations($id, ListingRepository $listingRepository, ConversationRepository $conversationRepository, UserRepository $userRepository, MessageRepository $messageRepository): JsonResponse
    {
        $conversations = null;

        $user = $userRepository->find($id);
        $listings = $listingRepository->findBy(array('fkUser' => $id));
        foreach ($listings as $listing) {
            $conversations = $conversationRepository->findBy(array('fkListing' => $listing->getId()));
        }

        $data = [];

        if ($conversations != null) {
            foreach ($conversations as $conversation) {
                $this->denyAccessUnlessGranted('CONVERSATION_RIGHT', $conversation);

                $data[] = ['id' => $conversation->getId(), 'listing' => $conversation->getFkListing()->getId(), 'user' => $user->getPseudo(), 'avatar' => $user->getFkAvatar()->getImage(), 'message' => $messageRepository->findLatestMessageByConversationId($conversation->getId())->getContent(), 'createdAt' => $messageRepository->findLatestMessageByConversationId($conversation->getId())->getCreatedAt(),

                ];
            }
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }
}