<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserBalance;
use App\Repository\AvatarRepository;
use App\Repository\UserRankRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use PharIo\Manifest\Email as ManifestEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class LoginController extends AbstractController
{
    #[Route('/register', name: 'app_register_home', methods: ['POST'])]
    public function register(Request $request, ManagerRegistry $doctrine, UserPasswordHasherInterface $hasher,
                             UserRankRepository $userRankRepository, AvatarRepository $avatarRepository,
                             ValidatorInterface $validator) : JsonResponse
    {
        $entityManager = $doctrine->getManager();
   
        $user = new User();
        $data = json_decode($request->getContent(), true);
        $email = $data['email'];
        $password = $data['password'];
        $pseudo = $data['pseudo'];
        $avatar = $data['avatar'];

        if ( empty($email ) || empty($password ) || empty($pseudo ) || empty($avatar ) ) {
            throw new NotFoundHttpException('Expecting mandatory parameters!');
        }

        $user->setEmail($email);
        $user->setPseudo($pseudo);
        $user->setIsVerified(true);
        $user->setFkUserRank($userRankRepository->findOneBy(['level' => "Nouveau"]));
        $user->setFkAvatar($avatarRepository->find($avatar));

        $user->setRoles( ["ROLE_USER"]);
        $user->setPassword($password);
        $errors = $validator->validate($user);

        $user->setPassword($hasher->hashPassword($user, $password));

        if (count($errors) > 0) {
            /*
             * Uses a __toString method on the $errors variable which is a
             * ConstraintViolationList object. This gives us a nice string
             * for debugging.
             */
            $errorsString = (string)$errors;

            return new JSONResponse($errorsString);
        }

        $entityManager->persist($user);
        $entityManager->flush();
   

        $userBalance = new UserBalance();

        $balance = 3;

        $userBalance->setBalance($balance);
        $userBalance->setCreatedAt(new \DateTimeImmutable());
        $userBalance->setFkUser($user);
        $userBalance->setUpdatedAt(new \DateTimeImmutable());

        $entityManager->persist($userBalance);
        $entityManager->flush();



        return $this->json('Created new user successfully with id ' . $user->getId());
    }

    #[Route('/email')]
    public function sendEmail(MailerInterface $mailer)
    {
        $email = (new Email())
            ->from('contact@recycle.com')
            ->to('alexis.carrere@hotmail.fr')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('<p>See Twig integration for better HTML integration!</p>');

        $mailer->send($email);

        return $this->json('Created new user successfully with id ');


    }

}