<?php

namespace App\Controller;

use App\Entity\Avatar;
use App\Form\AvatarType;
use App\Repository\AvatarRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


#[Route('/avatar')]
class AvatarController extends AbstractController
{
    #[Route('/', name: 'app_avatar_index', methods: ['GET'])]
    public function getAll(AvatarRepository $avatarRepository): JsonResponse
    {
        $avatars = $avatarRepository->findAll();
        $data = [];

        foreach ($avatars as $avatar) {
            $data[] = [
                'id' => $avatar->getId(),
                'image' => $avatar->getImage(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }


    #[Route('/new', name: 'add_avatar', methods: ['POST'])]
    public function new(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse 
    {


        $avatar = $serializer->deserialize($request->getContent(), Avatar::class, 'json');
        $this->denyAccessUnlessGranted('AVATAR_ADMIN', $avatar);

        $em->persist($avatar);
        $em->flush();

        $jsonAvatar = $serializer->serialize($avatar, 'json', ['groups' => 'getAvatars']);
        
        $location = $urlGenerator->generate('app_avatar_show', ['id' => $avatar->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonAvatar, Response::HTTP_CREATED, ["Location" => $location], true);
   }

    #[Route('/{id}', name: 'app_avatar_show', methods: ['GET'])]
    public function get($id, AvatarRepository $avatarRepository): JsonResponse
    {
        $avatar = $avatarRepository->findOneBy(['id' => $id]);
        $this->denyAccessUnlessGranted('AVATAR_USER', $avatar);

        $data = [
            'id' => $avatar->getId(),
            'image' => $avatar->getImage(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'app_avatar_edit', methods: ['GET', 'POST'])]
    public function update($id, Request $request, AvatarRepository $avatarRepository, EntityManagerInterface $manager): JsonResponse
    {
        $avatar = $avatarRepository->findOneBy(['id' => $id]);
        $data = json_decode($request->getContent(), true);
        $this->denyAccessUnlessGranted('AVATAR_ADMIN', $avatar);

        empty($data['image']) ? true : $avatar->setImage($data['image']);


        $manager->persist($avatar);
        $manager->flush();


        $avatarArray = [
            'id' => $avatar->getId(),
            'image' => $avatar->getImage(),
            ];

        return new JsonResponse($avatarArray, Response::HTTP_OK);
    }


    #[Route('/{id}', name: 'app_avatar_delete', methods: ['DELETE'])]
    public function delete($id, AvatarRepository $avatarRepository, Request $request): JsonResponse
    {
        $avatar = $avatarRepository->findOneBy(['id' => $id]);
        $this->denyAccessUnlessGranted('AVATAR_ADMIN', $avatar);

            $avatarRepository->remove($avatar, true);
            return new JsonResponse(['status' => 'avatar deleted'], Response::HTTP_NO_CONTENT);

    }


}