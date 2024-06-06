<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\AvatarRepository;
use App\Repository\UserRankRepository;
use App\Repository\UserRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/user')]
class AdminUserController extends AbstractController
{
    #[Route('/user/login', name: 'login', methods: ['POST'])]
    public function login(UserRepository $userRepository): JsonResponse
    {
        // Your private key file with passphrase
        // Can be generated with "ssh-keygen -t rsa -m pem"
        $privateKeyFile = '/path/to/key-with-passphrase.pem';

        // Create a private key of type "resource"
        $privateKey = openssl_pkey_get_private(
            file_get_contents($privateKeyFile)
        );

        $payload = [
            'iss' => 'example.org',
            'aud' => 'example.com',
            'iat' => 1356999524,
            'nbf' => 1357000000
        ];

        $jwt = JWT::encode($payload, $privateKey, 'RS256');
        echo "Encode:\n" . print_r($jwt, true) . "\n";

        // Get public key from the private key, or pull from from a file.
        $publicKey = openssl_pkey_get_details($privateKey)['key'];

        $decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
        echo "Decode:\n" . print_r((array)$decoded, true) . "\n";

        $payload = [
            'iss' => 'example.org',
            'aud' => 'example.com',
            'iat' => 1356999524,
            'nbf' => 1357000000
        ];

        $jwt = JWT::encode($payload, $privateKey, 'RS256');
        echo "Encode:\n" . print_r($jwt, true) . "\n";

        // Get public key from the private key, or pull from from a file.
        $publicKey = openssl_pkey_get_details($privateKey)['key'];

        $decoded = JWT::decode($jwt, new Key($publicKey, 'RS256'));
        echo "Decode:\n" . print_r((array)$decoded, true) . "\n";

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function getAll(UserRepository $userRepository): JsonResponse
    {
        $users = $userRepository->findAll();
        $data = [];

        foreach ($users as $user) {
            $data[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'role' => $user->getRoles(),
                'createdAt' => $user->getCreatedAt(),
                'updatedAt' => $user->getUpdatedAt(),
                'city' => $user->getCity(),
                'country' => $user->getCountry(),
                'pseudo' => $user->getPseudo(),
                'fkAvatar' => $user->getFkAvatar()->getId(),
                'fkUserRank' => $user->getFkUserRank()->getId(),
                'gpsCoordinates' => $user->getGpsCoordinates(),
                'postCode' => $user->getPostcode(),
                'streetName' => $user->getStreetName(),
                'streetNumber' => $user->getStreetNumber(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/new', name: 'add_user', methods: ['POST'])]
    public function add(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $hasher, UserRankRepository $userRankRepository, AvatarRepository $avatarRepository): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $user = new User();
        $data = json_decode($request->getContent(), true);

        $role = $data['role'];
        $email = $data['email'];
        $password = $data['password'];
        $city = $data['city'];
        $country = $data['country'];
        $pseudo = $data['pseudo'];
        $fkAvatarId = $data['fkAvatar'];
        $gpsCoordinates = $data['gpsCoordinates'];
        $postCode = $data['postCode'];
        $streetName = $data['streetName'];
        $streetNumber = $data['streetNumber'];
        $fkUserRankId = $data['fkUserRank'];

        if (empty($email) || empty($password) || empty($pseudo) || empty($fkAvatarId) || empty($fkUserRankId)) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $fkUserRank = $userRankRepository->find($fkUserRankId);
        $avatar = $avatarRepository->find($fkAvatarId);

        $user->setCreatedAt(new DateTimeImmutable());
        $user->setPassword($hasher->hashPassword($user, $password));
        $user->setEmail($email);
        $user->setRoles(["ROLE_USER"]);
        $user->setCity($city);
        $user->setCountry($country);
        $user->setGpsCoordinates($gpsCoordinates);
        $user->setPostcode($postCode);
        $user->setStreetName($streetName);
        $user->setStreetNumber($streetNumber);
        $user->setPseudo($pseudo);
        $user->setIsActive(true);
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setFkUserRank($fkUserRank);
        $user->setFkAvatar($avatar);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json('Created new user successfully with id ' . $user->getId());
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function update($id, Request $request, UserRepository $userRepository, EntityManagerInterface $manager, UserRankRepository $userRankRepository, AvatarRepository $avatarRepository): JsonResponse
    {
        $user = $userRepository->find($id);
        $data = json_decode($request->getContent(), true);

        empty($data['email']) ? true : $user->setEmail($data['email']);
        empty($data['password']) ? true : $user->setPassword($data['password']);
        empty($data['role']) ? true : $user->setRoles($data['role']);
        empty($data['city']) ? true : $user->setCity($data['city']);
        empty($data['country']) ? true : $user->setCountry($data['country']);
        empty($data['pseudo']) ? true : $user->setPseudo($data['pseudo']);
        empty($data['fkAvatar']) ? true : $user->setFkAvatar($avatarRepository->find($data['fkAvatar']));
        empty($data['fkUserRank']) ? true : $user->setFkUserRank($userRankRepository->find($data['fkUserRank']));
        empty($data['gpsCoordinates']) ? true : $user->setGpsCoordinates($data['gpsCoordinates']);
        empty($data['postCode']) ? true : $user->setPostcode($data['postCode']);
        empty($data['streetName']) ? true : $user->setStreetName($data['streetName']);
        empty($data['streetNumber']) ? true : $user->setStreetNumber($data['streetNumber']);

        $user->setUpdatedAt(new DateTimeImmutable());

        $manager->persist($user);
        $manager->flush();

        $userArray = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'createdAt' => $user->getCreatedAt(),
            'updatedAt' => $user->getUpdatedAt(),
            'city' => $user->getCity(),
            'country' => $user->getCountry(),
            'pseudo' => $user->getPseudo(),
            'fkAvatar' => $user->getFkAvatar()->getId(),
            'fkUserRankId' => $user->getFkUserRank()->getId(),
            'gpsCoordinates' => $user->getGpsCoordinates(),
            'postCode' => $user->getPostcode(),
            'streetName' => $user->getStreetName(),
            'streetNumber' => $user->getStreetNumber(),
        ];

        return new JsonResponse($userArray, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['DELETE'])]
    public function delete($id, userRepository $userRepository, Request $request): JsonResponse
    {
        $user = $userRepository->find($id);

        if ($this->isCsrfTokenValid('delete' . $id, $request->request->get('_token'))) {
            $userRepository->remove($user, true);
            return new JsonResponse(['status' => 'user deleted'], Response::HTTP_NO_CONTENT);
        }
        return new JsonResponse(['status' => 'UNAUTHORIZED'], Response::HTTP_FORBIDDEN);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function get($id, UserRepository $userRepository): JsonResponse
    {
        $user = $userRepository->find($id);

        $data[] = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRoles(),
            'createdAt' => $user->getCreatedAt(),
            'updatedAt' => $user->getUpdatedAt(),
            'city' => $user->getCity(),
            'country' => $user->getCountry(),
            'pseudo' => $user->getPseudo(),
            'fkAvatar' => $user->getFkAvatar()->getId(),
            'fkUserRank' => $user->getFkUserRank()->getId(),
            'gpsCoordinates' => $user->getGpsCoordinates(),
            'postCode' => $user->getPostcode(),
            'streetName' => $user->getStreetName(),
            'streetNumber' => $user->getStreetNumber(),
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }
}