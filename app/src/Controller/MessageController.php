<?php

namespace App\Controller;

use App\Entity\Message;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/message')]
class MessageController extends AbstractController
{
    #[Route('/me/{id}', name: 'app_message_index', methods: ['GET'])]
    public function getAll($id, MessageRepository $messageRepository): JsonResponse
    {
        $sender = $messageRepository->findBy(array('fkUserSender' => $id));
        $recipient = $messageRepository->findBy(array('fkUserRecipient' => $id));

        $dataRecipient = [];
        $dataSender = [];
        $dataSender[0] = 'SENDER :';
        $dataRecipient[0] = 'RECIPIENT :';

        foreach ($sender as $message) {
            $this->denyAccessUnlessGranted('MESSAGE_RIGHT', $message);

            $dataSender[] = [
                'TYPE' => 'SENDER',
                'id' => $message->getId(),
                'createdAt' => $message->getCreatedAt(),
                'content' => $message->getContent(),
                'fkConversation' => $message->getFkConversation()->getId(),
                'fkUserRecipient' => $message->getFkUserRecipient()->getId(),
                'fkUserSender' => $message->getFkUserSender()->getId(),
            ];
        }

        foreach ($recipient as $message) {
            $this->denyAccessUnlessGranted('MESSAGE_RIGHT', $message);

            $dataRecipient[] = [
                'TYPE' => 'RECIPIENT',
                'id' => $message->getId(),
                'createdAt' => $message->getCreatedAt(),
                'content' => $message->getContent(),
                'fkConversation' => $message->getFkConversation()->getId(),
                'fkUserRecipient' => $message->getFkUserRecipient()->getId(),
                'fkUserSender' => $message->getFkUserSender()->getId(),
            ];
        }

        $data[0] = $dataRecipient ;
        $data[1] = $dataSender;

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/conversation/{id}', name: 'app_messages_by_conversation', methods: ['GET'])]
    public function getByConversation($id, MessageRepository $messageRepository): JsonResponse
    {
        $messages = $messageRepository->findBy(array('fkConversation' => $id));

        $messagesList = [];

        foreach ($messages as $message) {
            $messagesList[] = [
                'id' => $message->getId(),
                'createdAt' => $message->getCreatedAt(),
                'content' => $message->getContent(),
                'fkConversation' => $message->getFkConversation()->getId(),
                'fkUserRecipient' => $message->getFkUserRecipient()->getId(),
                'fkUserSender' => $message->getFkUserSender()->getId(),
            ];
        }

        $data = $messagesList ;

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/new', name: 'add_message', methods: ['POST'])]
    public function add(Request $request, ManagerRegistry $doctrine, UserRepository $userRepository, ConversationRepository $conversationRepository): JsonResponse
    {
        $entityManager = $doctrine->getManager();
   
        $message = new Message();
        $data = json_decode($request->getContent(), true);
        $content = $data['content'];
        $sender = $data['fkUserSender'];
        $recipient = $data['fkUserRecipient'];
        $fkConversation = $data['fkConversation'];

        if (empty($content) || empty($sender) || empty($recipient) || empty($fkConversation)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $message->setContent($content);
        $message->setFkUserSender($userRepository->find($sender));
        $message->setFkUserRecipient($userRepository->find($recipient));
        $message->setFkConversation($conversationRepository->find($fkConversation));
        $message->setCreatedAt(new \DateTimeImmutable());
        $this->denyAccessUnlessGranted('MESSAGE_RIGHT', $message);

        $entityManager->persist($message);
        $entityManager->flush();
   
        return $this->json('Created new message successfully with id ' . $message->getId());
    }

    #[Route('/{id}', name: 'app_message_show', methods: ['GET'])]
    public function get($id, messageRepository $messageRepository): JsonResponse
    {
        $message = $messageRepository->find($id);
        $this->denyAccessUnlessGranted('MESSAGE_RIGHT', $message);


        $data[] = [
            'id' => $message->getId(),
            'createdAt' => $message->getCreatedAt(),
            'content' => $message->getContent(),
            'fkConversation' => $message->getFkConversation()->getId(),
            'fkUserRecipient' => $message->getFkUserRecipient()->getId(),
            'fkUserSender' => $message->getFkUserSender()->getId(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'app_message_edit', methods: ['GET', 'POST'])]
    public function update($id, Request $request, messageRepository $messageRepository , EntityManagerInterface $manager, UserRepository $userRepository, ConversationRepository $conversationRepository): JsonResponse
    {
        $message = $messageRepository->find($id);
        $this->denyAccessUnlessGranted('MESSAGE_RIGHT', $message);

        $data = json_decode($request->getContent(), true);

        empty($data['content']) ? true : $message->setContent($data['content']);
        empty($data['fkUserSender']) ? true : $message->setFkUserSender($userRepository->find($data['fkUserSender']));
        empty($data['fkUserRecipient']) ? true : $message->setFkUserRecipient($userRepository->find($data['fkUserRecipient']));
        empty($data['fkConversation']) ? true : $message->setFkConversation($conversationRepository->find($data['fkConversation']));

        $manager->persist($message);
        $manager->flush();

        $messageArray = [
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'fkUserSender' => $message->getFkUserSender()->getId(),
            'fkUserRecipient' => $message->getFkUserRecipient()->getId(),
            'conversation' => $message->getFkConversation()->getId()
        ];

        return new JsonResponse($messageArray, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_message_delete', methods: ['DELETE'])]
    public function delete($id, messageRepository $messageRepository, Request $request): JsonResponse
    {
        $message = $messageRepository->find($id);

        $messageRepository->remove($message, true);
        return new JsonResponse(['status' => 'message deleted'], Response::HTTP_NO_CONTENT);
    }
}