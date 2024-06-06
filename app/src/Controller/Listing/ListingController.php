<?php

namespace App\Controller\Listing;

use App\Entity\Listing;
use App\Entity\ListingImage;
use App\Repository\ConversationRepository;
use App\Repository\ListingCategoryRepository;
use App\Repository\ListingImageRepository;
use App\Repository\ListingRepository;
use App\Repository\ListingStatusRepository;
use App\Repository\ListingTypeRepository;
use App\Repository\MessageRepository;
use App\Repository\SubCategoryRepository;
use App\Repository\UserFavoriteListingRepository;
use App\Repository\UserRepository;
use App\Security\Voter\ListingVoter;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Security;

#[Route('/listing')]
class ListingController extends AbstractController
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

    #[Route('/all', name: 'app_listing_index', methods: ['GET'])]
    public function getAll(ListingRepository $listingRepository, ListingImageRepository $listingImageRepository): JsonResponse
    {
        $listings = $listingRepository->findAll();
        $data = [];

        foreach ($listings as $listing) {
            $data[] = [
                'id' => $listing->getId(),
                'city' => $listing->getCity(),
                'country' => $listing->getCountry(),
                'description' => $listing->getDescription(),
                'listingCategory' => $listing->getSubCategory()->getFkListingCategory()->getCategory(),
                'listingSubCategory' => $listing->getSubCategory()->getSubCategory(),
                'fkListingStatus' => $listing->getFkListingStatus()->getStatus(),
                'fkListingType' => $listing->getFkListingType()->getType(),
                'name' => $listing->getFkUser()->getPseudo(),
                'createdAt' => $listing->getFkUser()->getCreatedAt(),
                'authorAvatar' => $listing->getFkUser()->getFkAvatar()->getImage(),
                'postCode' => $listing->getPostcode(),
                'title' => $listing->getTitle(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/byStatus/{id}', name: 'app_listing_by_status', methods: ['GET'])]
    public function getByStatus($id, ListingRepository $listingRepository): JsonResponse
    {
        $listings = $listingRepository->findBy(array('fkListingStatus' => $id));
        $data = [];

        foreach ($listings as $listing) {
            $data[] = [
                'id' => $listing->getId(),
                'city' => $listing->getCity(),
                'country' => $listing->getCountry(),
                'description' => $listing->getDescription(),
                'fkListingStatus' => $listing->getFkListingStatus()->getStatus(),
                'listingCategory' => $listing->getSubCategory()->getFkListingCategory()->getCategory(),
                'listingSubCategory' => $listing->getSubCategory()->getSubCategory(),
                'fkListingType' => $listing->getFkListingType()->getType(),
                'name' => $listing->getFkUser()->getPseudo(),
                'createdAt' => $listing->getFkUser()->getCreatedAt(),
                'photo' => $listing->getFkUser()->getFkAvatar()->getImage(),
                'postCode' => $listing->getPostcode(),
                'title' => $listing->getTitle(),
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/images', name: 'app_listing_images', methods: ['GET'])]
    public function getAllListingsWithImages(ListingRepository $listingRepository, ListingImageRepository $listingImageRepository): JsonResponse
    {
        $listings = $listingRepository->findBy(array(), array('id' => 'DESC'));
        $data = [];
        $listingImages = [];
        $count = 0;

        foreach ($listings as $listing) {
            if ($count >= 20) {
                break; // To ensure that a maximum of 20 listings is returned
            }

            $listingId = $listing->getId();
            $images = $listingImageRepository->findBy(array('fkListing' => $listingId));

            for ($i = 0; $i < sizeof($images); $i++) {
                $listingImages[$i] = $images[$i]->getImage();
            }

            // Filter out closed Listings
            if ($listing->getFkListingStatus()->getId() != 2) {
                if (count($listingImages) < 1) {
                    $data[] = [
                        'id' => $listing->getId(),
                        'city' => $listing->getCity(),
                        'country' => $listing->getCountry(),
                        'description' => $listing->getDescription(),
                        'listingCategory' => $listing->getSubCategory()->getFkListingCategory()->getCategory(),
                        'listingSubCategory' => $listing->getSubCategory()->getSubCategory(),
                        'fkListingStatus' => $listing->getFkListingStatus()->getId(),
                        'fkListingType' => $listing->getFkListingType()->getType(),
                        'name' => $listing->getFkUser()->getPseudo(),
                        'createdAt' => $listing->getFkUser()->getCreatedAt(),
                        'listingAuthorAvatar' => $listing->getFkUser()->getFkAvatar()->getImage(),
                        'postCode' => $listing->getPostcode(),
                        'title' => $listing->getTitle(),
                        'listingCoverImage' => 'noPhoto'
                    ];

                } else {
                    $data[] = [
                        'id' => $listing->getId(),
                        'city' => $listing->getCity(),
                        'country' => $listing->getCountry(),
                        'description' => $listing->getDescription(),
                        'listingCategory' => $listing->getSubCategory()->getFkListingCategory()->getCategory(),
                        'listingSubCategory' => $listing->getSubCategory()->getSubCategory(),
                        'fkListingStatus' => $listing->getFkListingStatus()->getId(),
                        'fkListingType' => $listing->getFkListingType()->getType(),
                        'name' => $listing->getFkUser()->getPseudo(),
                        'createdAt' => $listing->getFkUser()->getCreatedAt(),
                        'listingAuthorAvatar' => $listing->getFkUser()->getFkAvatar()->getImage(),
                        'postCode' => $listing->getPostcode(),
                        'title' => $listing->getTitle(),
                        'listingCoverImage' => $listingImages[0]
                    ];
                }

                $count++;
            }
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }


    #[Route('/search', name: 'app_listing_search__index', methods: ['POST'])]
    public function getCustomListings(Request $request, ListingRepository $listingRepository, ListingImageRepository $listingImageRepository, ListingCategoryRepository $listingCategoryRepository, ManagerRegistry $doctrine): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $fkListingCategory = $data['fkListingCategory'];
        $latitude = $data['latitude'];
        $longitude = $data['longitude'];
        $round = $data['round'];
        $fkListingType = $data['fkListingType'];

        $listings = $listingRepository->search($fkListingCategory, $latitude, $longitude, $round, $fkListingType);
        $data = [];
        $arrayImages = [];
        if ($listings != null) {
            foreach ($listings as $listing) {
                $images = $listingImageRepository->findBy(array('fkListing' => $listing->getId()));
                for ($i = 0; $i < sizeof($images); $i++) {
                    $arrayImages[$i] = $images[$i]->getImage();
                }
                $data[] = [
                    'id' => $listing->getId(),
                    'city' => $listing->getCity(),
                    'country' => $listing->getCountry(),
                    'description' => $listing->getDescription(),
                    'listingCategory' => $listing->getSubCategory()->getFkListingCategory()->getCategory(),
                    'listingSubCategory' => $listing->getSubCategory()->getSubCategory(),
                    'fkListingStatus' => $listing->getFkListingStatus()->getStatus(),
                    'fkListingType' => $listing->getFkListingType()->getType(),
                    'fkUser' => $listing->getFkUser()->getId(),
                    'photo' => $listing->getFkUser()->getFkAvatar()->getImage(),
                    'createdAt' => $listing->getFkUser()->getCreatedAt(),
                    'name' => $listing->getFkUser()->getPseudo(),
                    'postCode' => $listing->getPostcode(),
                    'latitude' => $listing->getLatitude(),
                    'longitude' => $listing->getLongitude(),
                    'title' => $listing->getTitle(),
                    'listingCoverImage' => $arrayImages[0],
                    'fkListingId' => $listing->getId(),
                ];
            }

            return new JsonResponse($data, Response::HTTP_OK);
        }

        return new JsonResponse("empty", Response::HTTP_OK);

    }

    #[Route('/images/category/{id}', name: 'app_listing_images_category__index', methods: ['GET'])]
    public function getAllListingsWithImagesByCategory($id,ListingRepository $listingRepository,
                                                       ListingImageRepository $listingImageRepository): JsonResponse
    {
        $data = [];

        // Call to repository function
        $listings = $listingRepository->findByCategory($id);

        // For each listing, retrieve its images then populate JSON
        foreach ($listings as $listing) {
            $listingId = $listing->getId();
            $fkListingImages = $listingImageRepository->findBy(array('fkListing' => $listingId));
            $listingCoverImage = $fkListingImages[0]->getImage();

            $data[] = [
                'id' => $listing->getId(),
                'listingType' => $listing->getFkListingType()->getType(),
                'listingCategory' => $listing->getSubCategory()->getFkListingCategory()->getCategory(),
                'listingSubCategory' => $listing->getSubCategory()->getSubCategory(),
                'title' => $listing->getTitle(),
                'listingCoverImage' => $listingCoverImage,
                'country' => $listing->getCountry(),
                'postCode' => $listing->getPostcode(),
                'city' => $listing->getCity(),
            ];
        }

        // Return JSON response for front end
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/me/{id}', name: 'app_listing_show_mine', methods: ['GET'])]
    public function getMyListings($id, ListingRepository $listingRepository, ListingImageRepository $listingImageRepository, SubCategoryRepository $subCategoryRepository): JsonResponse
    {
        $listings = $listingRepository->findBy(['fkUser' => $id]);

        $data = [];

        foreach ($listings as $listing) {
            // Fetch the images related to the current listing
            $images = $listingImageRepository->findBy(['fkListing' => $listing->getId()]);

            // Prepare the array of image URLs
            $arrayImages = [];
            foreach ($images as $image) {
                $arrayImages[] = $image->getImage();
            }

            $this->denyAccessUnlessGranted('LISTING_VIEW', $listing);

            $data[] = [
                'id' => $listing->getId(),
                'city' => $listing->getCity(),
                'country' => $listing->getCountry(),
                'description' => $listing->getDescription(),
                'fkListingStatus' => $listing->getFkListingStatus()->getStatus(),
                'fkListingTypeId' => $listing->getFkListingType()->getId(),
                'fkListingType' => $listing->getFkListingType()->getType(),
                'name' => $listing->getFkUser()->getPseudo(),
                'createdAt' => $listing->getFkUser()->getCreatedAt(),
                'photo' => $listing->getFkUser()->getFkAvatar()->getImage(),
                'postCode' => $listing->getPostcode(),
                'title' => $listing->getTitle(),
                'listingCategory' => $listing->getSubCategory()->getFkListingCategory()->getCategory(),
                'listingSubCategory' => $listing->getSubCategory()->getSubCategory(),
                'listingCoverImage' => count($arrayImages) < 1 ? 'noPhoto' : $arrayImages[0],
            ];
        }

        return new JsonResponse($data, Response::HTTP_OK);
    }


    #[Route('/new', name: 'add_listing', methods: ['POST'])]
    public function add(Request                 $request, ManagerRegistry $doctrine, SubCategoryRepository $subCategoryRepository,
                        ListingStatusRepository $listingStatusRepository, ListingTypeRepository $listingTypeRepository, UserRepository $userRepository
    ): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $decodedJwtToken = $this->jwtManager->decode($this->tokenStorageInterface->getToken());
        $activeUser = $userRepository->find($decodedJwtToken["id"]);

        if ($this->isGranted(ListingVoter::CREATE, new Listing())) {
            $data = json_decode($request->getContent(), true);
            $title = $data['title'];
            $description = $data['description'];
            $fkListingStatus = $data['fkListingStatus'];
            $fkListingType = $data['fkListingType'];
            $fkSubCategory = $data['fkSubCategory'];
            $longitude = $data['longitude'];
            $latitude = $data['latitude'];
            $country = $data['country'];
            $postCode = $data['postCode'];
            $city = $data['city'];

            $images = (array) $data['images'];

            if (empty($city) || empty($country) || empty($description)
                || empty($title) || empty($fkSubCategory) || empty($fkListingStatus)
                || empty($fkListingType)
                || empty($postCode)) {

                throw new NotFoundHttpException('Expecting mandatory parameters!');
            }

            $newListing = new Listing();

            $subCategory = $subCategoryRepository->find($fkSubCategory);
            $status = $listingStatusRepository->find($fkListingStatus);
            $type = $listingTypeRepository->find($fkListingType);

            $newListing->setCity($city);
            $newListing->setCountry($country);
            $newListing->setDescription($description);
            $newListing->setSubCategory($subCategory);
            $newListing->setFkListingStatus($status);
            $newListing->setFkListingType($type);
            $newListing->setFkUser($activeUser);
            $newListing->setPostcode($postCode);
            $newListing->setTitle($title);
            $newListing->setLatitude($latitude);
            $newListing->setLongitude($longitude);

            $entityManager->persist($newListing);
            $entityManager->flush();

            foreach ($images as $image) {
                $newImage = new ListingImage();
                $newImage->setImage($image);
                $newImage->setFkListing($newListing);
                $newImage->setCreatedAt(new \DateTimeImmutable());
                $entityManager->persist($newImage);
                $entityManager->flush();
            }

            $responseText = "Successfully created a new listing with id: " . $newListing->getId();
        } else {
            $responseText = "You are not authorized to do this.";
        }

        return new JsonResponse($responseText, Response::HTTP_OK);
    }

    #[Route('/view/{id}', name: 'app_listing_show', methods: ['GET'])]
    public function get(
        $id,
        ListingRepository $listingRepository,
        ListingImageRepository $listingImageRepository
    ): JsonResponse
    {
        $listing = $listingRepository->find($id);

        // Fetch the images related to the listing
        $images = $listingImageRepository->findBy(['fkListing' => $id]);
        $arrayImages = [];

        // Prepare the array of image URLs
        for ($i = 0; $i < sizeof($images); $i++) {
            $arrayImages[$i] = $images[$i]->getImage();
        }

        $data[] = [
            'id' => $listing->getId(),
            'city' => $listing->getCity(),
            'country' => $listing->getCountry(),
            'description' => $listing->getDescription(),
            'listingCategory' => $listing->getSubCategory()->getFkListingCategory()->getCategory(),
            'listingSubCategory' => $listing->getSubCategory()->getSubCategory(),
            'fkListingCategoryImage' => $listing->getSubCategory()->getFkListingCategory()->getCategoryImage(),
            'fkListingStatus' => $listing->getFkListingStatus()->getStatus(),
            'fkListingType' => $listing->getFkListingType()->getType(),
            'authorUserId' => $listing->getFkUser()->getId(),
            'name' => $listing->getFkUser()->getPseudo(),
            'createdAt' => $listing->getFkUser()->getCreatedAt(),
            'photo' => $listing->getFkUser()->getFkAvatar()->getImage(),
            'title' => $listing->getTitle(),
            'listingImages' => count($arrayImages) < 1 ? 'noPhoto' : $arrayImages,
        ];

        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}/edit', name: 'app_listing_edit', methods: ['GET', 'POST'])]
    public function update($id, Request $request, listingRepository $listingRepository, EntityManagerInterface $manager, ListingCategoryRepository $listingCategoryRepository,
                           ListingStatusRepository $listingStatusRepository, ListingTypeRepository $listingTypeRepository, UserRepository $userRepository, ListingImageRepository $listingImageRepository
    ): JsonResponse
    {
        $listing = $listingRepository->find($id);
        $data = json_decode($request->getContent(), true);
        $arrayImages = [];

        if ($this->isGranted(ListingVoter::EDIT, new Listing())) {
            $images = $listingImageRepository->findBy(array('fkListing' => $id));

            foreach ($images as $image) {
                $arrayImages[] = [
                    'image' => $image->getImage(),
                ];
            }

            empty($data['city']) ? true : $listing->setCity($data['city']);
            empty($data['country']) ? true : $listing->setCountry($data['country']);
            empty($data['description']) ? true : $listing->setDescription($data['description']);
            empty($data['postCode']) ? true : $listing->setPostcode($data['postCode']);
            empty($data['title']) ? true : $listing->setTitle($data['title']);
            empty($data['listingSubCategory']) ? true : $listing->setSubCategory($listingCategoryRepository->find($data['listingSubCategory']));
            empty($data['fkListingStatus']) ? true : $listing->setFkListingStatus($listingStatusRepository->find($data['fkListingStatus']));
            empty($data['fkListingType']) ? true : $listing->setFkListingType($listingTypeRepository->find($data['fkListingType']));
            empty($data['fkUser']) ? true : $listing->setFkUser($userRepository->find($data['fkUser']));

            $manager->persist($listing);
            $manager->flush();

            $listingArray = [
                'id' => $listing->getId(),
                'fkListingStatus' => $listing->getFkListingStatus()->getId(),
                'title' => $listing->getTitle(),
                'country' => $listing->getCountry(),
                'postCode' => $listing->getPostcode(),
                'city' => $listing->getCity(),
                'description' => $listing->getDescription(),
                'fkListingType' => $listing->getFkListingType()->getId(),
                'listingCategory' => $listing->getSubCategory()->getFkListingCategory()->getCategory(),
                'listingSubCategory' => $listing->getSubCategory()->getSubCategory(),
                'images' => $arrayImages,
            ];

            $responseText = "Listing successfully edited.";
            $responseArray = [
                'responseText' => $responseText,
                'editedListing' => $listingArray,
            ];
        } else {
            $responseText = "You are not authorized to do this.";
            $responseArray = [
                'responseText' => $responseText,
            ];
        }

        return new JsonResponse($responseArray, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_listing_delete', methods: ['DELETE'])]
    public function delete($id, listingRepository $listingRepository, Request $request, MessageRepository $messageRepository, ConversationRepository $conversationRepository,
                           ListingImageRepository $listingImageRepository, UserFavoriteListingRepository $userFavoriteListingRepository): JsonResponse
    {
        $listing = $listingRepository->find($id);

        $this->denyAccessUnlessGranted('LISTING_DELETE', $listing);

        // find the conversations related to the listing
        $conversations = $conversationRepository->findBy(array('fkListing' => $listing->getId()));

        // find the messages related to the conversations and delete them
        foreach ($conversations as $conversation) {
            $messages = $messageRepository->findBy(array('fkConversation' => $conversation->getId()));
            foreach ($messages as $message) {
                $messageRepository->remove($message, true);
            }
        }
        // delete the conversations
        foreach ($conversations as $conversation) {
            $conversationRepository->remove($conversation, true);
        }

        // delete the images related to the listing
        $images = $listingImageRepository->findBy(array('fkListing' => $listing->getId()));
        foreach ($images as $image) {
            $listingImageRepository->remove($image, true);
        }

        // delete the user favorite listings related to the listing
        $userFavoriteListings = $userFavoriteListingRepository->findBy(array('fkListing' => $listing->getId()));
        foreach ($userFavoriteListings as $userFavoriteListing) {
            $userFavoriteListingRepository->remove($userFavoriteListing, true);
        }

        // delete the listing
        $listingRepository->remove($listing, true);

        return new JsonResponse(['status' => 'listing deleted'], Response::HTTP_NO_CONTENT);
    }
}