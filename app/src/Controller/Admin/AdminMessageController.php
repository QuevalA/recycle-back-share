<?php

namespace App\Controller\Admin;

use App\Entity\Message;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\ProfileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/admin/message')]
class AdminMessageController extends AbstractController
{
    #[Route('/me/{id}', name: 'app_message_index', methods: ['GET'])]
    public function getAll($id,MessageRepository $messageRepository): JsonResponse
    {
        $sender = $messageRepository->findBy(array('sender' => $id));
        $recipient = $messageRepository->findBy(array('recipient' => $id));

        $dataRecipient = [];
        $dataSender = [];
        $dataSender[0] = 'SENDER :';
        $dataRecipient[0] = 'RECIPIENT :';

        foreach ($sender as $message) {
            $dataSender[] = [
                'TYPE' => 'SENDER',
                'id' => $message->getId(),
                'createdAt' => $message->getCreatedAt(),
                'content' => $message->getContent(),
                'fkConversation' => $message->getFkConversation()->getId(),
                'recipient' => $message->getFkProfileRecipient()->getId(),
                'sender' => $message->getFkProfileSender()->getId(),
            ];
        }

        foreach ($recipient as $message) {
            $dataRecipient[] = [
                'TYPE' => 'RECIPIENT',
                'id' => $message->getId(),
                'createdAt' => $message->getCreatedAt(),
                'content' => $message->getContent(),
                'fkConversation' => $message->getFkConversation()->getId(),
                'recipient' => $message->getFkProfileRecipient()->getId(),
                'sender' => $message->getFkProfileSender()->getId(),
            ];
        }

        $data[0] = $dataRecipient ;
        $data[1] = $dataSender;

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/new', name: 'add_message', methods: ['POST'])]
    public function add(Request $request, ManagerRegistry $doctrine, ProfileRepository $profileRepository, ConversationRepository $conversationRepository): JsonResponse
    {
        $entityManager = $doctrine->getManager();
   
        $message = new Message();
        $data = json_decode($request->getContent(), true);
        $content = $data['content'];
        $sender = $data['sender'];
        $recipient = $data['recipient'];
        $fkConversation = $data['fkConversation'];

        if (empty($content) || empty($sender) || empty($recipient) || empty($fkConversation)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $message->setContent($content);
        $message->setFkProfileSender($profileRepository->find($sender));
        $message->setFkProfileRecipient($profileRepository->find($recipient));
        $message->setFkConversation($conversationRepository->find($fkConversation));
        $message->setCreatedAt(new \DateTimeImmutable());

        $entityManager->persist($message);
        $entityManager->flush();
   
        return $this->json('Created new message successfully with id ' . $message->getId());
    }

    #[Route('/{id}', name: 'app_message_show', methods: ['GET'])]
    public function get($id, messageRepository $messageRepository): JsonResponse
    {
        $message = $messageRepository->find($id);

        $data[] = [
            'id' => $message->getId(),
            'createdAt' => $message->getCreatedAt(),
            'content' => $message->getContent(),
            'fkConversation' => $message->getFkConversation()->getId(),
            'recipient' => $message->getFkProfileRecipient()->getId(),
            'sender' => $message->getFkProfileSender()->getId(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'app_message_edit', methods: ['GET', 'POST'])]
    public function update($id, Request $request, messageRepository $messageRepository , EntityManagerInterface $manager, ProfileRepository $profileRepository, ConversationRepository $conversationRepository): JsonResponse
    {
        $message = $messageRepository->find($id);
        $data = json_decode($request->getContent(), true);

        empty($data['content']) ? true : $message->setContent($data['content']);
        empty($data['sender']) ? true : $message->setFkProfileSender($profileRepository->find($data['sender']));
        empty($data['recipient']) ? true : $message->setFkProfileRecipient($profileRepository->find($data['recipient']));
        empty($data['fkConversation']) ? true : $message->setFkConversation($conversationRepository->find($data['fkConversation']));

        $manager->persist($message);
        $manager->flush();

        $messageArray = [
            'id' => $message->getId(),
            'content' => $message->getContent(),
            'sender' => $message->getFkProfileSender()->getId(),
            'recipient' => $message->getFkProfileRecipient()->getId(),
            'conversation' => $message->getFkConversation()->getId()
        ];

        return new JsonResponse($messageArray, Response::HTTP_OK);
    }
    
    #[Route('/{id}', name: 'app_message_delete', methods: ['DELETE'])]
    public function delete($id, messageRepository $messageRepository, Request $request): JsonResponse
    {
        $message = $messageRepository->find($id);

        if ($this->isCsrfTokenValid('delete'.$id, $request->request->get('_token'))) {
            $messageRepository->remove($message, true);
            return new JsonResponse(['status' => 'message deleted'], Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(['status' => 'UNAUTHORIZED'], Response::HTTP_FORBIDDEN);
    }
}